<?php

namespace App\Services\Ai\Providers;

use App\Contracts\AiNlpProvider;
use App\Services\Ai\AiSystemPrompt;
use App\Services\Ai\Concerns\ParsesJsonNlpResponse;
use Illuminate\Support\Facades\Http;

class OpenAiCompatibleNlpProvider implements AiNlpProvider
{
    use ParsesJsonNlpResponse;

    public function __construct(
        protected string $providerName
    ) {}

    public function name(): string
    {
        return $this->providerName;
    }

    public function label(): string
    {
        return config("ai.{$this->providerName}.label")
            ?? ucfirst(str_replace('_', ' ', $this->providerName));
    }

    public function isConfigured(): bool
    {
        $cfg = $this->config();

        if (empty($cfg['endpoint']) || empty($cfg['model'])) {
            return false;
        }

        // Local Ollama does not require an API key
        if ($this->providerName === 'local') {
            return true;
        }

        return !empty($cfg['key']);
    }

    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return ['status' => 'error', 'message' => "{$this->label()} is not configured."];
        }

        $endpoint = rtrim($this->config()['endpoint'], '/');

        try {
            if ($this->providerName === 'local') {
                $response = Http::timeout(5)->get("{$endpoint}/api/tags");
                if ($response->successful()) {
                    return ['status' => 'ok', 'message' => 'Local AI endpoint is reachable.'];
                }

                return ['status' => 'error', 'message' => 'Local AI endpoint returned status ' . $response->status()];
            }

            $response = Http::timeout(10)
                ->withToken($this->config()['key'])
                ->get($this->modelsUrl($endpoint));

            if ($response->successful()) {
                return ['status' => 'ok', 'message' => "{$this->label()} API is reachable."];
            }

            // Some compatible APIs lack /models — try a minimal completion
            $probe = $this->parse('show my tasks', 0);
            if ($probe) {
                return ['status' => 'ok', 'message' => "{$this->label()} API responded successfully."];
            }

            return ['status' => 'error', 'message' => "{$this->label()} returned status {$response->status()}."];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function parse(string $prompt, int $userId): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $cfg = $this->config();
        $endpoint = rtrim($cfg['endpoint'], '/');
        $nowStr = AiSystemPrompt::now();

        try {
            $request = Http::timeout((int) ($cfg['timeout'] ?? 30))
                ->withHeaders(['Content-Type' => 'application/json']);

            if (!empty($cfg['key'])) {
                $request = $request->withToken($cfg['key']);
            }

            $payload = [
                'model' => $cfg['model'],
                'messages' => [
                    ['role' => 'system', 'content' => AiSystemPrompt::system($nowStr)],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => (float) ($cfg['temperature'] ?? 0.1),
                'max_tokens' => (int) ($cfg['max_tokens'] ?? 512),
            ];

            if ($cfg['json_mode'] ?? true) {
                $payload['response_format'] = ['type' => 'json_object'];
            }

            $response = $request->post($this->chatCompletionsUrl($endpoint), $payload);

            if (!$response->successful()) {
                logger()->warning("{$this->label()} returned non-200", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $content = $response->json('choices.0.message.content');

            return $this->decodeIntentJson($content, $this->label());
        } catch (\Exception $e) {
            logger()->error("{$this->label()} parse failed: " . $e->getMessage());
            return null;
        }
    }

    public function status(): array
    {
        $cfg = $this->config();

        return [
            'name' => $this->name(),
            'label' => $this->label(),
            'configured' => $this->isConfigured(),
            'endpoint' => $cfg['endpoint'] ?? null,
            'model' => $cfg['model'] ?? null,
            'key_preview' => $this->previewKey($cfg['key'] ?? null),
            'type' => 'openai_compatible',
        ];
    }

    public function isAvailable(): bool
    {
        return $this->testConnection()['status'] === 'ok';
    }

    /**
     * @return array<string, mixed>
     */
    protected function config(): array
    {
        return config("ai.{$this->providerName}", []);
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

    protected function chatCompletionsUrl(string $endpoint): string
    {
        $endpoint = rtrim($endpoint, '/');

        if (str_ends_with($endpoint, '/v1')) {
            return "{$endpoint}/chat/completions";
        }

        return "{$endpoint}/v1/chat/completions";
    }

    protected function modelsUrl(string $endpoint): string
    {
        $endpoint = rtrim($endpoint, '/');

        if (str_ends_with($endpoint, '/v1')) {
            return "{$endpoint}/models";
        }

        return "{$endpoint}/v1/models";
    }
}
