import re, io, base64
import pandas as pd
from sqlalchemy import create_engine, text, inspect
import matplotlib.pyplot as plt
from matplotlib import cycler

engine = create_engine("mysql+mysqlconnector://user:123@localhost:3306/salesorder_db")

# schema summary to define database to model
def get_schema_summary() -> str:
    inspector = inspect(engine)
    include_tables = {"orders", "order_status_log"}
    lines = ["You have access to a MySQL database called salesorder_db."]
    
    for table_name in inspector.get_table_names():
        if table_name in include_tables:
            columns = [col["name"] for col in inspector.get_columns(table_name)]
            lines.append(f"Table `{table_name}` has columns: {', '.join(columns)}.")

    return "\n".join(lines)

# helper to sanitize and flatten raw SQL strings
def flatten_sql(raw_sql: str) -> str:
    raw = re.sub(r"^\s*sqlquery\s*:\s*", "", raw_sql, flags=re.IGNORECASE)
    raw = raw.replace("```sql", "").replace("```", "").strip()
    raw = re.sub(r"--.*?(\r?\n|$)", " ", raw)
    raw = re.sub(r"/\*[\s\S]*?\*/", " ", raw)
    return " ".join(raw.split())

# execute a SQL query and return rows as JSON
def execute_query(sql: str) -> dict:
    clean_sql = flatten_sql(sql)
    df = pd.read_sql(text(clean_sql), engine)
    return {"rows": df.to_dict(orient="records")}  

# 3) generate a chart from JSON rows and return base64-encoded PNG
def generate_chart(rows: list, x: str, y: str, kind: str = "bar") -> dict:

    df = pd.DataFrame(rows).copy()

    plt.style.use('https://github.com/dhaitz/matplotlib-stylesheets/raw/master/pitayasmoothie-light.mplstyle')
    # plt.style.use('ggplot')
    fig, ax = plt.subplots(figsize=(7, 4))

    # identify features
    numeric_cols = df.select_dtypes(include=["number"]).columns.tolist()
    categorical_cols = [c for c in df.columns if c not in numeric_cols]

    # grouped bar
    if kind == "bar" and len(numeric_cols) > 1:
        df.set_index(x)[numeric_cols].plot(kind='bar', ax=ax)
        title = f"{', '.join(numeric_cols)} by {x}"

    # stacked bar
    elif kind == "bar" and len(numeric_cols) == 1 and len(categorical_cols) >= 2:
        x_col, stack_col, y_col = categorical_cols[0], categorical_cols[1], numeric_cols[0]
        df.pivot_table(index=x_col, columns=stack_col, values=y_col, aggfunc='sum', fill_value=0)\
        .plot(kind='bar', stacked=True, ax=ax)
        title = f"Stacked {y_col} by {x_col} and {stack_col}"

    else:
        # Single metric chart types
        title = f"{y} by {x}"
        
        if kind == "bar":
            ax.bar(df[x], df[y])

        elif kind == "hist":
            ax.hist(df[y], bins=10)

        elif kind == "line":
            y_cols = [col for col in numeric_cols if col != x]
            if len(y_cols) > 1:
                df[[x] + y_cols].set_index(x).plot(marker="o", ax=ax)
                title = f"{', '.join(y_cols)} by {x}"
            else:
                ax.plot(df[x], df[y], marker="o")
        
        elif kind == "area":
            y_cols = [col for col in numeric_cols if col != x]
            df_area = df[[x] + y_cols].copy()
            df_area.set_index(x).plot(kind="area", stacked=True, alpha=0.6, ax=ax)
            title = f"Area chart of {', '.join(y_cols)} by {x}"

        elif kind == "pie":
            ax.axis('equal')
            ax.pie(df[y], labels=df[x], autopct='%1.1f%%', startangle=90, wedgeprops={'width': 0.4})

        elif kind == "box":
            if y in numeric_cols:
                if x in categorical_cols:
                    grouped = [group[y].values for name, group in df.groupby(x)]
                    ax.boxplot(grouped, labels=df[x].unique())
                    title = f"Boxplot of {y} grouped by {x}"
                    ax.set_xlabel(x)
                else:
                    ax.boxplot(df[y].dropna())
                    title = f"Boxplot of {y}"
                    ax.set_xticks([])
                ax.set_ylabel(y)
                ax.set_title(title)

        elif kind == "scatter":
            color_by = None
            if len(categorical_cols) >= 1:
                color_by = categorical_cols[0]

            if color_by:
                for category, group in df.groupby(color_by):
                    ax.scatter(group[x], group[y], label=str(category), alpha=0.7)
                ax.legend(title=color_by)
            else:
                ax.scatter(df[x], df[y], alpha=0.7)
            ax.set_xlabel(x)
            ax.set_ylabel(y)
            
        else:
            raise ValueError(f"Unsupported chart kind: {kind}")



    ax.set_title(title)
    plt.tight_layout()

    # Save to buffer with transparent background
    buffer = io.BytesIO()
    plt.savefig(buffer, format="png", dpi=100)
    buffer.seek(0)
    img_b64 = base64.b64encode(buffer.read()).decode("utf-8")
    plt.close(fig)
    return {"image_base64": img_b64}