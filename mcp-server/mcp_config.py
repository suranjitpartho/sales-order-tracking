from mcp.server import Server
from mcp.types import Tool, TextContent
from typing import Any, Dict, List
from decimal import Decimal
import json, sqlparse
from pandas import DataFrame

from llm.deepseek_wrapper import DeepSeekWrapper
from tools.sql_tool import SQLTool
from tools.schema_tool import SchemaInfoTool


llm = DeepSeekWrapper()

sql = SQLTool("mysql+pymysql://user:123@localhost:3306/salesorder_db")
schema_tool = SchemaInfoTool("mysql+pymysql://user:123@localhost:3306/salesorder_db")
server = Server("deepseek-sql-server")


def make_json_safe(obj):
    if isinstance(obj, Decimal):
        return float(obj)
    elif isinstance(obj, dict):
        return {k: make_json_safe(v) for k, v in obj.items()}
    elif isinstance(obj, list):
        return [make_json_safe(v) for v in obj]
    return obj


@server.list_tools()
async def list_tools() -> List[Tool]:
    return [
        Tool(
            name="natural_language_sql_tool",
            description="Convert natural language to SQL and return query results",
            inputSchema={
                "type": "object",
                "properties": {
                    "question": {
                        "type": "string",
                        "description": "A plain English question"
                    }
                },
                "required": ["question"]
            }
        )
    ]


@server.call_tool()
async def call_tool(name: str, arguments: Dict[str, Any]) -> List[TextContent]:
    if name != "natural_language_sql_tool":
        return [TextContent(type="text", text=json.dumps({"error": "Unknown tool"}))]

    question = arguments.get("question")
    schema_summary = schema_tool.get_schema_summary()
    prompt = (
        "You are a SQL assistant. Convert the following question into a MySQL SQL query.\n\n"
        f"{schema_summary}\n\n"
        f"Question: {question}\n\n"
        "Return only the SQL query."
    )
    sql_query = llm.generate(prompt)
    results = sql.run_query(sql_query)

    if results and "error" not in results[0]:
        rows = make_json_safe(results)

        df = DataFrame(rows)
        table_html = df.to_html(index=False, classes="order-table", border=0)
        formatted_sql = sqlparse.format(sql_query, reindent=True, keyword_case='upper')

        return [TextContent(
            type="text",
            text=json.dumps({
                "sql": formatted_sql,
                "table": table_html,
                "table_rows": rows
            })
        )]
    else:
        return [TextContent(type="text", text=json.dumps({
            "error": results[0].get("error", "Unknown error"),
            "sql": sql_query
        }))]

