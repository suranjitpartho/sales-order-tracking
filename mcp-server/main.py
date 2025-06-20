from fastapi import FastAPI, Request
from mcp_config import call_tool, list_tools
from fastapi.responses import JSONResponse

app = FastAPI()

@app.post("/mcp")
async def mcp_handler(request: Request):
    try:
        data = await request.json()
        tool_name = data["tool"]
        parameters = data.get("parameters", {})

        if tool_name != "natural_language_sql_tool":
            return JSONResponse(content={"error": "Unknown tool"}, status_code=400)

        results = await call_tool(tool_name, parameters)

        return JSONResponse(content=[res.model_dump() for res in results])

    except Exception as e:
        return JSONResponse(content={"error": str(e)}, status_code=500)