<?php

namespace App\Services\Ai\Providers;

use App\Contracts\AiNlpProvider;
use App\Services\Ai\AiSystemPrompt;
use App\Services\Ai\Concerns\ParsesJsonNlpResponse;
use Illuminate\Support\Facades\Http;

class GeminiNlpProvider implements AiNlpProvider
{
    use ParsesJsonNlpResponse;

    public function name(): string
    {
        return 'gemini';
    }

    public function label(): string
    {
        return 'Google Gemini';
    }

    public function isConfigured(): bool
    {
        return !empty(config('ai.gemini.key'));
    }

    public function testConnection(): array
    {
        $key = config('ai.gemini.key');
        if (!$key) {
            return ['status' => 'error', 'message' => 'Gemini API key not configured.'];
        }

        try {
            $response = Http::timeout(10)
                ->get("https://generativelanguage.googleapis.com/v1beta/models?key={$key}");

            if ($response->successful()) {
                return ['status' => 'ok', 'message' => 'Gemini API is reachable.'];
            }

            return ['status' => 'error', 'message' => 'Gemini API returned status ' . $response->status()];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function parse(string $prompt, int $userId): ?array
    {
        $apiKey = config('ai.gemini.key');
        if (!$apiKey) {
            return null;
        }

        $model = config('ai.gemini.model', 'gemini-2.5-flash');
        $nowStr = AiSystemPrompt::now();

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout((int) config('ai.gemini.timeout', 30))
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [
                        ['parts' => [['text' => AiSystemPrompt::geminiUserPrompt($prompt, $nowStr)]]],
                    ],
                    'generationConfig' => ['responseMimeType' => 'application/json'],
                ]);

            if (!$response->successful()) {
                logger()->warning('Gemini API returned non-200', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $textResponse = $response->json('candidates.0.content.parts.0.text');

            return $this->decodeIntentJson($textResponse, $this->label());
        } catch (\Exception $e) {
            logger()->error('Gemini API parse failed: ' . $e->getMessage());
            return null;
        }
    }

    public function status(): array
    {
        $key = config('ai.gemini.key');

        return [
            'name' => $this->name(),
            'label' => $this->label(),
            'configured' => $this->isConfigured(),
            'model' => config('ai.gemini.model', 'gemini-2.5-flash'),
            'key_preview' => $this->previewKey($key),
            'type' => 'gemini',
        ];
    }

    protected function previewKey(?string $key): string
    {
        if (!$key) {
            return '';
        }
        if (strlen($key) <= 8) {
            return str_repeat('*', strlen($key));
        }

        return substr($key, 0, 4) . str_repeat('*', strlen($key) - 8) . substr($key, -4);
    }
}
