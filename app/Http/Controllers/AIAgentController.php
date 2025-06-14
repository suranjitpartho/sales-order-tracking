<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AIAgentController extends Controller
{
    private function getSchema()
    {
        $response = Http::get('http://localhost:8001/schema');
        return $response->json();
    }

    public function index()
    {
        $schema = $this->getSchema();
        return view('ai-agent.index', ['schema' => $schema,]);
    }

    public function ask(Request $request)
    {
        $query = $request->input('question');

        $resp = Http::timeout(100)
            ->post('http://localhost:8001/ask', ['question' => $query])
            ->throw();

        $data = $resp->json();
        $summary = $data['summary'] ?? 'No summary returned.';
        $sql = $data['sql'] ?? null;
        $tableHtml = $data['table'] ?? null;
        $chartBase64 = $data['chart_base64'] ?? $data['chart'] ?? null;
        $tableRows   = $data['table_rows'] ?? [];

        $schema = $this->getSchema();

        return view('ai-agent.index', compact('query', 'summary', 'sql', 'tableHtml', 'chartBase64', 'schema', 'tableRows'));
    }

    public function downloadCsv(Request $request)
    {
        $rows = json_decode($request->input('table_rows'), true);

        if (!$rows || !is_array($rows)) {
            return redirect()->back()->withErrors(['No data to export.']);
        }

        $csvName = 'table_export_' . Str::random(8) . '.csv';
        $csvPath = storage_path("app/public/{$csvName}");

        $file = fopen($csvPath, 'w');
        fputcsv($file, array_keys($rows[0]));

        foreach ($rows as $row) {
            fputcsv($file, $row);
        }

        fclose($file);

        return response()->download($csvPath)->deleteFileAfterSend(true);
    }
}
