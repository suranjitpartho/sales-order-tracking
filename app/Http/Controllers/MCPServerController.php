<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class MCPServerController extends Controller
{
    public function index()
    {
        return view('mcp-server.index');
    }

    public function ask(Request $request)
    {
        $query = $request->input('question');

        $response = Http::timeout(200)
            ->post('http://127.0.0.1:8001/mcp', [
                'tool'       => 'natural_language_sql_tool',
                'parameters' => ['question' => $query]
            ])
            ->throw();

        $data = json_decode($response[0]['text'] ?? '{}', true);

        return view('mcp-server.index', [
            'question'    => $query,
            'sql'         => $data['sql'] ?? null,
            'tableHtml'   => $data['table'] ?? null,
            'tableRows'   => $data['table_rows'] ?? [],
        ]);
    }

    // public function summary(Request $request)
    // {
    //     $response = Http::timeout(100)->post('http://127.0.0.1:8001/summary', [
    //         'question'    => $request->input('question'),
    //         'table_rows'  => $request->input('table_rows'),
    //     ]);

    //     return $response->json();
    // }

}
