<?php

namespace App\Services\Ai\Providers;

use App\Contracts\AiNlpProvider;
use App\Services\Ai\AiSystemPrompt;
use App\Services\Ai\Concerns\ParsesJsonNlpResponse;
use Illuminate\Support\Facades\Http;

class AnthropicNlpProvider implements AiNlpProvider
{
    use ParsesJsonNlpResponse;

    public function name(): string
    {
        return 'anthropic';
    }

    public function label(): string
    {
        return 'Anthropic Claude';
    }

    public function isConfigured(): bool
    {
        return !empty(config('ai.anthropic.key'));
    }

    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return ['status' => 'error', 'message' => 'Anthropic API key not configured.'];
        }

        try {
            $probe = $this->parse('show my tasks', 0);
            if ($probe) {
                return ['status' => 'ok', 'message' => 'Anthropic API responded successfully.'];
            }

            return ['status' => 'error', 'message' => 'Anthropic API did not return a valid NLP response.'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function parse(string $prompt, int $userId): ?array
    {
        $apiKey = config('ai.anthropic.key');
        if (!$apiKey) {
            return null;
        }

        $model = config('ai.anthropic.model', 'claude-3-5-haiku-latest');
        $nowStr = AiSystemPrompt::now();

        try {
            $response = Http::timeout((int) config('ai.anthropic.timeout', 30))
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'x-api-key' => $apiKey,
                    'anthropic-version' => config('ai.anthropic.version', '2023-06-01'),
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => $model,
                    'max_tokens' => (int) config('ai.anthropic.max_tokens', 512),
                    'temperature' => (float) config('ai.anthropic.temperature', 0.1),
                    'system' => AiSystemPrompt::system($nowStr),
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);

            if (!$response->successful()) {
                logger()->warning('Anthropic API returned non-200', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $blocks = $response->json('content', []);
            $text = collect($blocks)
                ->where('type', 'text')
                ->pluck('text')
                ->implode("\n");

            return $this->decodeIntentJson($text, $this->label());
        } catch (\Exception $e) {
            logger()->error('Anthropic API parse failed: ' . $e->getMessage());
            return null;
        }
    }

    public function status(): array
    {
        $key = config('ai.anthropic.key');

        return [
            'name' => $this->name(),
            'label' => $this->label(),
            'configured' => $this->isConfigured(),
            'model' => config('ai.anthropic.model', 'claude-3-5-haiku-latest'),
            'key_preview' => $this->previewKey($key),
            'type' => 'anthropic',
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
