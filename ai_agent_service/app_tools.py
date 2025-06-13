import re, io, base64
import pandas as pd
from sqlalchemy import create_engine, text
import matplotlib.pyplot as plt
from matplotlib import cycler
from matplotlib.patches import FancyBboxPatch

# 1) Helper to sanitize and flatten raw SQL strings
def flatten_sql(raw_sql: str) -> str:
    raw = re.sub(r"^\s*sqlquery\s*:\s*", "", raw_sql, flags=re.IGNORECASE)
    raw = raw.replace("```sql", "").replace("```", "").strip()
    raw = re.sub(r"--.*?(\r?\n|$)", " ", raw)
    raw = re.sub(r"/\*[\s\S]*?\*/", " ", raw)
    return " ".join(raw.split())

# 2) Execute a SQL query and return rows as JSON
def execute_query(sql: str) -> dict:
    engine = create_engine(
        "mysql+mysqlconnector://user:123@localhost:3306/salesorder_db"
    )
    clean_sql = flatten_sql(sql)
    df = pd.read_sql(text(clean_sql), engine)
    return {"rows": df.to_dict(orient="records")}  

# 3) Generate a chart from JSON rows and return base64-encoded PNG
def generate_chart(rows: list, x: str, y: str, kind: str = "bar") -> dict:

    custom_colors = ["#2c6dc8", "#ed946b", "#91bb73", "#e7355c", "#9160b8"]
    plt.rcParams['axes.prop_cycle'] = cycler('color', custom_colors)

    df = pd.DataFrame(rows).copy()

    # Create figure with transparent background
    fig, ax = plt.subplots(figsize=(7, 4), facecolor='none')
    ax.set_facecolor('none')

    # identify features
    numeric_cols = df.select_dtypes(include=["number"]).columns.tolist()
    categorical_cols = [c for c in df.columns if c not in numeric_cols]

    # grouped bar
    if kind == "bar" and len(numeric_cols) > 1:
        df_group = df.set_index(x)[numeric_cols]
        df_group.plot(kind='bar', ax=ax)
        ax.legend(frameon=False, facecolor='none', labelcolor='white', fontsize=8, loc='upper right', bbox_to_anchor=(0.98, 0.98))
        title = f"{', '.join(numeric_cols)} by {x}"

    # stacked bar
    elif kind == "bar" and len(numeric_cols) == 1 and len(categorical_cols) >= 2:
        x_col, stack_col, y_col = categorical_cols[0], categorical_cols[1], numeric_cols[0]
        df.pivot_table(index=x_col, columns=stack_col, values=y_col, aggfunc='sum', fill_value=0)\
        .plot(kind='bar', stacked=True, ax=ax)
        title = f"Stacked {y_col} by {x_col} and {stack_col}"
        ax.legend(frameon=False, facecolor='none', labelcolor='#bebebe', fontsize=8, loc='upper right')

    else:
        # Single metric chart types
        title = f"{y} by {x}"
        
        if kind == "bar":
            ax.bar(df[x], df[y])
        elif kind == "line":
            ax.plot(df[x], df[y], marker="o")
        elif kind == "pie":
            ax.axis('equal')
            ax.pie(df[y], labels=df[x], autopct='%1.1f%%', startangle=90, wedgeprops={'width': 0.4})
        elif kind == "scatter":
            ax.scatter(df[x], df[y])
        else:
            raise ValueError(f"Unsupported chart kind: {kind}")

    # styling for title and labels
    ax.set_title(title, fontsize=10, color='#bebebe')
    ax.set_xlabel(x, fontsize=8, color='white')
    ax.set_ylabel(y, fontsize=8, color='white')
    # Rotate and style x-tick labels
    plt.setp(ax.get_xticklabels(), rotation=30, ha='right', fontsize=8, color='white')
    plt.setp(ax.get_yticklabels(), fontsize=8, color='white')

    # Style spines and ticks as white
    for spine in ax.spines.values():
        spine.set_color('#535353')
    ax.tick_params(axis='x', colors='#535353')
    ax.tick_params(axis='y', colors='#535353')

    # Add subtle white gridlines
    ax.grid(True, linestyle='--', linewidth=0.5, alpha=0.3, color='white')

    # Tight layout
    plt.tight_layout()

    # Save to buffer with transparent background
    buffer = io.BytesIO()
    plt.savefig(buffer, format="png", dpi=100, transparent=True)
    buffer.seek(0)
    img_b64 = base64.b64encode(buffer.read()).decode("utf-8")
    plt.close(fig)
    return {"image_base64": img_b64}