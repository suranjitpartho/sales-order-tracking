<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MCPServerController extends Controller
{
    public function index()
    {
        return view('mcp-server.index');
    }

    public function ask(Request $request)
    {
        $query = $request->input('question');

        $response = Http::timeout(150)
            ->post('http://127.0.0.1:8001/mcp', [
                'tool'       => 'natural_language_sql_tool',
                'parameters' => ['question' => $query]
            ])
            ->throw();

        $data = json_decode($response[0]['text'] ?? '{}', true);

        return view('mcp-server.index', [
            'question'    => $query,
            #'summary'     => $data['summary'] ?? null,
            'sql'         => $data['sql'] ?? null,
            'tableHtml'   => $data['table'] ?? null,
            'tableRows'   => $data['table_rows'] ?? [],
            'chartBase64' => $data['chart_base64'] ?? null,
        ]);
    }

}
