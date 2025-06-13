<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\RequestException;
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

        try {
            $resp = Http::timeout(600)
                ->post('http://localhost:8001/ask', ['question' => $query])
                ->throw();
        } catch (RequestException $e) {
            \Log::error('AI Agent request failed', [
                'query'     => $query,
                'exception' => $e->getMessage(),
            ]);
            return back()->withErrors('AI service is temporarily unavailable. Please try again later.');
        }

        $data          = $resp->json();
        $summary      = $data['summary'] ?? 'No summary returned.';
        $sql           = $data['sql'] ?? null;
        $tableHtml     = $data['table'] ?? null;
        $chartBase64   = $data['chart_base64'] ?? $data['chart'] ?? null;

        return view('ai-agent.index', compact(
            'query', 'summary', 'sql', 'tableHtml', 'chartBase64'
        ));
    }
}
