import os, json
import pandas as pd
from fastapi import FastAPI
from pydantic import BaseModel
from openai import AsyncOpenAI
from dotenv import load_dotenv
from app_tools import execute_query, generate_chart
from pandas import DataFrame


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
        "description": "Generate a chart (bar/line/pie/scatter) from JSON rows, returning a base64 PNG.",
        "parameters": {
            "type": "object",
            "properties": {
                "rows": {"type": "array", "items": {"type": "object"}},
                "x": {"type": "string"},
                "y": {"type": "string"},
                "kind": {"type": "string", "enum": ["bar","line","pie","scatter"]}
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

    # Seed the conversation with your schema as a system message
    messages = [
        {
            "role": "system",
            "content": (
                "You are an AI assistant with access to a MySQL database called salesorder_db. "
                "It contains exactly one table, `tasks`, with columns:\n"
                "- id (bigint unsigned)\n"
                "- order_date (date)\n"
                "- product_id (varchar(6))\n"
                "- product_category (enum('clothing','ornaments','other'))\n"
                "- buyer_gender (enum('male','female'))\n"
                "- buyer_age (int)\n"
                "- order_location (text)\n"
                "- international_shipping (tinyint(1))\n"
                "- sales_price (decimal(10,2))\n"
                "- shipping_charges (decimal(10,2))\n"
                "- sales_per_unit (decimal(10,2))\n"
                "- quantity (int)\n"
                "- total_sales (decimal(12,2))\n"
            )
        },
        {"role": "user", "content": q.question}
    ]

    try:

        resp = await client.chat.completions.create(
            model="gpt-3.5-turbo",
            messages=messages,
            functions=functions,
            function_call="auto"
        )
        message = resp.choices[0].message
        print("Function called by GPT:", message.function_call.name if message.function_call else "No function")

        if message.function_call and message.function_call.name == "execute_query":
            args   = json.loads(message.function_call.arguments)
            sql    = args["sql"]
            rows   = execute_query(sql)["rows"]

            insight_prompt = {"role": "user", "content": ("Based on this data:\n" f"{rows}\n\n" "Give me a single, punchy sentence interpreting what it means.")}
            insight = await client.chat.completions.create(
                model="gpt-3.5-turbo",
                messages=[{"role":"system","content":"You are a data analyst who summarizes tables and charts in one sentence."}, insight_prompt]
            )
            summary = insight.choices[0].message.content.strip()

            lc = q.question.lower()
            wants_chart = any(k in lc for k in ["chart", "graph", "plot", "bar", "line", "pie", "scatter"])

            if wants_chart and rows:
                keys = list(rows[0].keys())
                x_col, y_col = keys[0], keys[1]

                kind = "bar"
                if "line chart" in lc: kind = "line"
                elif "pie chart" in lc: kind = "pie"
                elif "scatter" in lc or "relation" in lc or "vs" in lc: kind = "scatter"

                chart_b64 = generate_chart(rows, x_col, y_col, kind)["image_base64"]

                return {
                    "summary": summary or f"{kind.title()} of {y_col} vs {x_col}.",
                    "sql": sql,
                    "chart": chart_b64
                }

            df = DataFrame(rows)
            table_html = df.to_html(index=False, classes="order-table")
            return {
                "summary": summary,
                "sql":     sql,
                "table":   table_html
            }

        return {"summary": message.content}

    except Exception as e:
        return {"error": str(e)}