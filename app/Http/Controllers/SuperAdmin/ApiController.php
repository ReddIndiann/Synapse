<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\LocalAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class ApiController extends Controller
{
    public function index(): View
    {
        $providers = [
            'gemini' => [
                'configured' => !empty(config('ai.gemini.key')),
                'model' => config('ai.gemini.model', 'gemini-2.5-flash'),
                'key_preview' => $this->previewKey(config('ai.gemini.key')),
            ],
            'local' => [
                'configured' => !empty(config('ai.local.endpoint')),
                'endpoint' => config('ai.local.endpoint'),
                'model' => config('ai.local.model'),
            ],
            'regex' => [
                'configured' => true,
                'description' => 'Built-in PHP pattern matching (no external dependencies)',
            ],
        ];

        return view('superadmin.apis.index', compact('providers'));
    }

    public function test(string $provider, LocalAiService $localAi): JsonResponse
    {
        if ($provider === 'gemini') {
            $key = config('ai.gemini.key');
            if (!$key) {
                return response()->json(['status' => 'error', 'message' => 'Gemini API key not configured.']);
            }

            try {
                $response = Http::timeout(10)
                    ->get("https://generativelanguage.googleapis.com/v1beta/models?key={$key}");

                if ($response->successful()) {
                    return response()->json(['status' => 'ok', 'message' => 'Gemini API is reachable.']);
                }

                return response()->json([
                    'status' => 'error',
                    'message' => 'Gemini API returned status ' . $response->status(),
                ]);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }

        if ($provider === 'local') {
            if ($localAi->isAvailable()) {
                return response()->json(['status' => 'ok', 'message' => 'Local AI endpoint is reachable.']);
            }

            return response()->json(['status' => 'error', 'message' => 'Local AI endpoint is unreachable.']);
        }

        return response()->json(['status' => 'error', 'message' => "Unknown provider: {$provider}"]);
    }

    protected function previewKey(?string $key): string
    {
        if (!$key) return '';
        if (strlen($key) <= 8) return str_repeat('*', strlen($key));
        return substr($key, 0, 4) . str_repeat('*', strlen($key) - 8) . substr($key, -4);
    }
}
