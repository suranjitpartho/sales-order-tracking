from fastapi import FastAPI
from pydantic import BaseModel
from langchain_openai import ChatOpenAI
from langchain_community.utilities import SQLDatabase
from langchain_experimental.sql import SQLDatabaseChain
from sqlalchemy import create_engine
from sqlalchemy.engine import URL
from dotenv import load_dotenv
import os


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

# --- Request Format ---
class Query(BaseModel):
    question: str

# --- API Endpoint ---
@app.post("/ask")
def ask_agent(query: Query):
    try:
        response = db_chain.invoke({"query": query.question})
        # print(f">> Full Result: {response}")

        final_answer = response.get("result", "")
        steps = response.get("intermediate_steps", [])
        sql = next((step for step in steps if isinstance(step, str) and step.strip().lower().startswith("select")), "N/A")

        return {
            "question": query.question,
            "answer": final_answer,
            "sql": sql
        }
    
    except Exception as e:
        print(">> Error:", str(e))
        return {"error": str(e)}
