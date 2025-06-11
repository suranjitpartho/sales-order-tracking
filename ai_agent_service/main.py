from fastapi import FastAPI
from pydantic import BaseModel

from langchain_ollama import OllamaLLM
from langchain_community.utilities import SQLDatabase
from langchain_experimental.sql import SQLDatabaseChain

from sqlalchemy import create_engine, text
from sqlalchemy.engine import URL

app = FastAPI()
llm = OllamaLLM(
    model="deepseek-llm",
    base_url="http://localhost:11434",
    temperature=0
)

db_url = URL.create(
    drivername="mysql+mysqlconnector",
    username="user",
    password="123",
    host="localhost",
    port=3306,
    database="salesorder_db"
)

engine = create_engine(db_url)
db = SQLDatabase(engine, include_tables=["tasks"])

db_chain = SQLDatabaseChain.from_llm(
    llm, 
    db, 
    verbose=True, 
    return_intermediate_steps=False,
    return_direct=False,
)

class Query(BaseModel):
    question: str

# FastAPI endpoint
@app.post("/ask")
def ask_agent(query: Query):
    try:
        answer = db_chain.invoke({"query": query.question})
        print(f">> Result: {answer}")

        return {"answer": answer}

    except Exception as e:
        print(">> Error:", str(e))
        return {"error": str(e)}

