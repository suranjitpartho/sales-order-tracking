from sqlalchemy import create_engine, inspect

class SchemaInfoTool:
    def __init__(self, db_uri: str):
        self.engine = create_engine(db_uri)

    def get_schema_summary(self) -> str:
        inspector = inspect(self.engine)
        summary_lines = []

        for table in inspector.get_table_names():
            columns = inspector.get_columns(table)
            col_defs = [f"{col['name']} ({col['type']})" for col in columns]
            summary_lines.append(f"Table `{table}` with columns: {', '.join(col_defs)}")

        return "\n".join(summary_lines)
