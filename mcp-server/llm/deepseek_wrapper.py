import requests, re
from typing import List
import json

class DeepSeekWrapper:
    def __init__(self, model_name="deepseek-coder:6.7b", host="http://localhost:11434"):
        self.model_name = model_name
        self.host = host

    # generate SQL query from natural language prompt
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

    # extract SQL query from DeepSeek response
    def _extract_sql(self, response: str) -> str:
        response = re.sub(r"```(sql)?", "", response).strip()
        match = re.search(r"(SELECT|INSERT|UPDATE|DELETE).*?;", response, re.IGNORECASE | re.DOTALL)
        if match:
            return match.group(0).strip()
        return response.splitlines()[0].strip()
    

    # def trim_insight(insight: str, max_sentences: int = 2) -> str:
    #     sentences = re.split(r'(?<=[.!?])\s+', insight.strip())
    #     return " ".join(sentences[:max_sentences]).strip()
    

    # def generate_summary(self, question: str, rows: List[dict]) -> str:
    #     prompt = (
    #         f"Analyze the following SQL query result and write a short summary of the insight:\n\n"
    #         f"Question: {question}\n\n"
    #         f"Result:\n{json.dumps(rows, indent=2)}\n\n"
    #         "Summary:"
    #     )

    #     try:
    #         response = requests.post(
    #             f"{self.host}/api/generate",
    #             json={
    #                 "model": self.model_name,
    #                 "prompt": prompt,
    #                 "temperature": 0.7,
    #                 "stream": False
    #             }
    #         )
    #         response.raise_for_status()
    #         resp_json = response.json()
    #         raw_summary = resp_json.get("response", "").strip()

    #         if raw_summary:
    #             return raw_summary
    #         return "No summary could be generated."
        
    #     except Exception as e:
    #         return f"Insight generation failed: {str(e)}"



