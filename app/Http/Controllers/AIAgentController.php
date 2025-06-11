<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AIAgentController extends Controller
{
    public function index()
    {
        return view('ai-agent.index');
    }

    public function ask(Request $request)
    {
        $query = $request->input('question');

        $response = Http::timeout(600)->post('http://localhost:8001/ask', ['question' => $query]);
        $data = $response->json();
        $answer = $data['answer'] ?? 'No result found';
        $sql = $data['sql'] ?? null;

        return view('ai-agent.index', compact('query', 'answer', 'sql'));
    }
}
