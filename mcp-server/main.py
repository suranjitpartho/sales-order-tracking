import json
from fastapi import FastAPI, Request
from fastapi.responses import JSONResponse
from mcp_config import call_tool, llm

app = FastAPI()

@app.post("/mcp")
async def mcp_handler(request: Request):
        data = await request.json()
        tool_name = data["tool"]
        parameters = data.get("parameters", {})

        if tool_name != "natural_language_sql_tool":
            return JSONResponse(content={"error": "Unknown tool"}, status_code=400)

        results = await call_tool(tool_name, parameters)

        return JSONResponse(content=[res.model_dump() for res in results])

# @app.post("/summary")
# async def summary_handler(request: Request):
#     body = await request.json()
#     question = body.get("question")
#     rows     = body.get("table_rows", [])

#     if not question or not rows:
#         return JSONResponse(content={"error": "Missing 'question' or 'table_rows'"}, status_code=400)

#     summary = llm.generate_summary(question, rows)
#     return JSONResponse(content={"summary": summary})

