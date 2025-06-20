from sqlalchemy import create_engine, text
from typing import List, Dict, Union

class SQLTool:
    def __init__(self, db_uri: str):
        self.engine = create_engine(db_uri)

    def run_query(self, sql: str) -> List[Dict[str, Union[str, float, int]]]:
        
        if not sql.strip().lower().startswith("select"):
            return [{"error": "Only SELECT queries are allowed."}]
        
        try:
            with self.engine.connect() as conn:
                result = conn.execute(text(sql))
                rows = result.mappings().all()
                return [dict(row) for row in rows] if rows else [{"info": "No rows returned."}]

        except Exception as e:
            return [{"error": str(e)}]
