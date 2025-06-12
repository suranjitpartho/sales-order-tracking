import io
import os
import re
import base64
import logging

import pandas as pd
import matplotlib.pyplot as plt
from fastapi import FastAPI
from fastapi.responses import JSONResponse
from pydantic import BaseModel
from re import sub
from langchain_openai import ChatOpenAI
from langchain_community.utilities import SQLDatabase
from langchain_experimental.sql import SQLDatabaseChain
from sqlalchemy import create_engine, text
from sqlalchemy.engine import URL
from dotenv import load_dotenv
import sqlparse


load_dotenv()
app = FastAPI()

# --- LLM Model ---
llm = ChatOpenAI(
    model="gpt-3.5-turbo",
    api_key=os.getenv("OPENAI_API_KEY"),
    temperature=0,
)

# --- Database Configuration ---
db_url = URL.create(
    drivername="mysql+mysqlconnector",
    username="user",
    password="123",
    host="localhost",
    port=3306,
    database="salesorder_db"
)

engine = create_engine(db_url)
db = SQLDatabase(engine)

# --- Build the Chain ---
db_chain = SQLDatabaseChain.from_llm(
    llm, 
    db, 
    verbose=True, 
    return_intermediate_steps=True,
    return_direct=False,
)

# --- Helper to Generate Charts ---
def generate_chart_image(df, x_col, y_col, chart_type="bar"):
    fig, ax = plt.subplots()

    if chart_type == "bar":
        ax.bar(df[x_col], df[y_col], color='skyblue')
    elif chart_type == "line":
        ax.plot(df[x_col], df[y_col], marker='o')
    elif chart_type == "pie":
        ax.pie(df[y_col], labels=df[x_col], autopct='%1.1f%%')
    elif chart_type == "scatter":
        ax.scatter(df[x_col], df[y_col])
    else:
        raise ValueError("Unsupported chart type.")

    if chart_type != "pie":
        ax.set_xlabel(x_col)
        ax.set_ylabel(y_col)
        ax.set_title(f'{y_col} by {x_col}')
        plt.xticks(rotation=45)
    else:
        plt.title(f'{y_col} Distribution by {x_col}')

    buffer = io.BytesIO()
    plt.tight_layout()
    plt.savefig(buffer, format='png')
    buffer.seek(0)
    chart_data = base64.b64encode(buffer.read()).decode('utf-8')
    plt.close()
    return chart_data

# --- Robust SQL Extraction & Flattening Helpers ---
def extract_sql(steps: list) -> str:
    raw_sql = ""
    for step in steps:
        if isinstance(step, str):
            m = re.search(r"```sql\s*(.*?)```", step, flags=re.IGNORECASE | re.DOTALL)
            if m:
                return m.group(1).strip()
            m = re.search(r"```\s*(.*?)```", step, flags=re.DOTALL)
            if m:
                return m.group(1).strip()
            m = re.search(r"sqlquery\s*:\s*(.*)", step, flags=re.IGNORECASE | re.DOTALL)
            if m:
                return m.group(1).strip()
    text_all = " ".join(s for s in steps if isinstance(s, str))
    m = re.search(r"(SELECT[\s\S]*?;)", text_all, flags=re.IGNORECASE)
    return m.group(1).strip() if m else ""


def flatten_sql(raw_sql: str) -> str:
    sql = raw_sql.replace("```sql", "").replace("```", "")
    sql = re.sub(r"--.*?(\r?\n|$)", " ", sql)
    sql = re.sub(r"/\*[\s\S]*?\*/", " ", sql)
    return " ".join(sql.split())


# --- Request Format ---
class Query(BaseModel):
    question: str

# --- API Endpoint ---
@app.post("/ask")
def ask_agent(query: Query):
    try:
        response = db_chain.invoke({"query": query.question})

        final_answer = response.get("result", "")
        steps = response.get("intermediate_steps", [])
        
        raw_sql = extract_sql(steps)
        print("Raw SQL:", repr(raw_sql))
        sql = flatten_sql(raw_sql)
        print("Clean SQL:", repr(sql))

        # check if user wants in table format
        if "table format" in query.question.lower() and sql:
            df = pd.read_sql(text(sql), engine)
            table_html = df.to_html(index=False, classes="order-table")
            return {
                "question": query.question,
                "answer": final_answer,
                "sql": sql,
                "table": table_html,
            }
        
        # check if user wants charts, plots or graphs
        if any(word in query.question.lower() for word in ["chart", "graph", "plot", "bar"]) and sql:
            df = pd.read_sql(text(sql), engine)
            chart_type = "bar"
            if "line chart" in query.question.lower(): chart_type = "line"
            elif "pie chart" in query.question.lower(): chart_type = "pie"
            elif "scatter chart" in query.question.lower(): chart_type = "scatter"

            if df.shape[1] >= 2:
                chart = generate_chart_image(df, df.columns[0], df.columns[1], chart_type)
                return {
                    "question": query.question,
                    "answer": final_answer,
                    "sql": sql,
                    "chart": chart,
                }

        return {"question": query.question, "answer": final_answer, "sql": sql or "N/A"}
    
    except Exception as e:
        return {"error": str(e)}
