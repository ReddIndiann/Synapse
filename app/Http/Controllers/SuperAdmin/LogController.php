<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogController extends Controller
{
    public function index(Request $request): View
    {
        $logFile = storage_path('logs/laravel.log');
        $logContent = '';
        $levels = ['ERROR', 'WARNING', 'INFO', 'DEBUG', 'CRITICAL', 'ALERT'];

        if (file_exists($logFile)) {
            $content = file_get_contents($logFile);

            $filterLevel = $request->get('level');
            $search = $request->get('search');

            $entries = $this->parseLogEntries($content);

            if ($filterLevel) {
                $entries = array_filter($entries, fn($e) => $e['level'] === strtoupper($filterLevel));
            }

            if ($search) {
                $entries = array_filter($entries, fn($e) => str_contains(strtolower($e['text']), strtolower($search)));
            }

            $logContent = array_slice(array_reverse($entries), 0, 200);
        }

        return view('superadmin.logs.index', compact('logContent', 'levels'));
    }

    public function clear(): \Illuminate\Http\RedirectResponse
    {
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            file_put_contents($logFile, '');
        }

        return redirect()->route('superadmin.logs.index')->with('status', 'Log file cleared.');
    }

    protected function parseLogEntries(string $content): array
    {
        $entries = [];
        $pattern = '/\[(\d{4}-\d{2}-\d{2}[^\]]+)\]\s+(\w+)\.\w+:\s+(.*?)(?=\[\d{4}|\z)/s';

        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $entries[] = [
                    'timestamp' => $match[1],
                    'level' => $match[2],
                    'text' => trim($match[3]),
                ];
            }
        }

        return $entries;
    }
}
