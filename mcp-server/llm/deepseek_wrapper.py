import requests, re, json
from typing import List

class DeepSeekWrapper:
    def __init__(self, model_name="deepseek-coder:6.7b", host="http://localhost:11434"):
        self.model_name = model_name
        self.host = host

    # Generate SQL query from natural language prompt
    def generate(self, prompt: str) -> str:
        try:
            response = requests.post(
                f"{self.host}/api/generate",
                json={
                    "model": self.model_name,
                    "prompt": prompt,
                    "stream": False
                }
            )
            response.raise_for_status()
            raw = response.json().get("response", "").strip()
            return self._extract_sql(raw)
        except Exception as e:
            return f"DeepSeek error: {str(e)}"

    # Generate insight summary
    # def generate_summary(self, question: str, rows: List[dict]) -> str:
    #     message = (
    #         f"You are a smart data analyst. Generate insight on the following results. No fluff, just meaningful observation in just one line.\n\n"
    #         f"Question: {question}\n\n"
    #         f"Result:\n{json.dumps(rows, indent=2)}\n\n"
    #         "Summary:"
    #     )
    #     response = requests.post(
    #         f"{self.host}/api/generate",
    #         json={
    #             "model": self.model_name,
    #             "prompt": message,
    #             "temperature": 0.7,
    #             "stream": False
    #         }
    #     )
    #     response.raise_for_status()
    #     resp_json = response.json()
    #     insight = resp_json.get("response", "").strip()
    #     return insight if insight else "No summary could be generated."

    # Extract SQL query from DeepSeek response
    def _extract_sql(self, response: str) -> str:
        response = re.sub(r"```(sql)?", "", response).strip()
        match = re.search(r"(SELECT|INSERT|UPDATE|DELETE).*?;", response, re.IGNORECASE | re.DOTALL)
        if match:
            return match.group(0).strip()
        return response.splitlines()[0].strip()
