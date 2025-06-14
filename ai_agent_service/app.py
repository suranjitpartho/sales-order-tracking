import os, json, sqlparse
import pandas as pd
from fastapi import FastAPI
from pydantic import BaseModel
from openai import AsyncOpenAI
from dotenv import load_dotenv
from app_tools import execute_query, generate_chart, get_schema_summary, engine
from pandas import DataFrame
from sqlalchemy import inspect

# Load environment and initialize
load_dotenv()
app = FastAPI()
client = AsyncOpenAI(api_key=os.getenv("OPENAI_API_KEY"))

# Describe functions to model
functions = [
    {
        "name": "execute_query",
        "description": "Run a SQL query against the salesorder_db and return rows as JSON.",
        "parameters": {
            "type": "object",
            "properties": {
                "sql": {"type": "string"}
            },
            "required": ["sql"]
        }
    },
    {
        "name": "generate_chart",
        "description": "Generate a chart (bar/line/pie/scatter/area) from JSON rows, returning a base64 PNG.",
        "parameters": {
            "type": "object",
            "properties": {
                "rows": {"type": "array", "items": {"type": "object"}},
                "x": {"type": "string"},
                "y": {"type": "string"},
                "kind": {"type": "string", "enum": ["bar","line","pie","scatter", "area", "hist", "box"]}
            },
            "required": ["rows","x","y"]
        }
    }
]

# Request model
class Query(BaseModel):
    question: str

@app.post("/ask")
async def ask(q: Query):

    # Seed the conversation with schema as a system message
    messages = [
        {"role": "system", "content": get_schema_summary()},
        {"role": "user", "content": q.question}
    ]

    try:
        resp = await client.chat.completions.create(
            model="gpt-3.5-turbo",
            messages=messages,
            timeout=30,
            functions=functions,
            function_call="auto"
        )
        message = resp.choices[0].message

        if message.function_call and message.function_call.name == "execute_query":
            args   = json.loads(message.function_call.arguments)
            sql    = args["sql"]
            formatted_sql = sqlparse.format(sql, reindent=True, keyword_case='upper')
            rows   = execute_query(sql)["rows"]
            lc = q.question.lower()
            wants_chart = any(k in lc for k in ["chart", "graph", "plot", "bar", "line", "pie", "scatter", "area", "histogram", "boxplot"])
            keys = list(rows[0].keys())
            x_col, y_col = keys[0], keys[1] if len(keys) > 1 else keys[0]


            # Generate summary 
            # insight_mode = "chart" if wants_chart else "table"
            # insight_prompt = {
            #     "role": "user", 
            #     "content": (
            #         f"The following {insight_mode} was generated using this data:\n\n"
            #         f"X-axis: {x_col if wants_chart else 'N/A'}\n"
            #         f"Y-axis: {y_col if wants_chart else 'N/A'}\n\n"
            #         f"Data:\n{rows}\n\n"
            #         f"Please summarize the insights from this {insight_mode}. "
            #         "Use 1 to 3 lines max. If it's a chart, comment on patterns, anomalies, or comparisons. "
            #         "If it's a table, summarize totals, groupings, or noticeable metrics."
            #     )
            # }
            # insight = await client.chat.completions.create(
            #     model="gpt-3.5-turbo",
            #     messages=[{"role": "system", "content": "You are a smart data analyst. Interpret charts or tables briefly — no fluff, just meaningful observations in 1–3 lines."}, insight_prompt]
            # )
            # summary = insight.choices[0].message.content.strip()


            # Handle chart generation
            if wants_chart and rows:
                kind = "bar"
                if "line chart" in lc: kind = "line"
                elif "area chart" in lc: kind = "area"
                elif "pie chart" in lc: kind = "pie"
                elif "histogram" in lc or "distribution" in lc: kind = "hist"
                elif "boxplot" in lc or "box plot" in lc: kind = "box"
                elif "scatter" in lc or "relation" in lc or "vs" in lc: kind = "scatter"

                chart_b64 = generate_chart(rows, x_col, y_col, kind)["image_base64"]

                return {
                    "summary": None,
                    "sql": formatted_sql,
                    "chart": chart_b64
                }

            # Handle table generation
            df = DataFrame(rows)
            table_html = df.to_html(index=False, classes="order-table")
            return {
                "summary": None,
                "sql":     formatted_sql,
                "table":   table_html,
                "table_rows": rows
            }

        return {"summary": message.content}

    except Exception as e:
        return {"error": str(e)}
    



@app.get("/schema")
def get_schema():
    inspector = inspect(engine)
    db_schema = {}

    for table_name in inspector.get_table_names():
        columns = [col["name"] for col in inspector.get_columns(table_name)]
        db_schema[table_name] = columns

    return {"database": "salesorder_db", "tables": db_schema}
