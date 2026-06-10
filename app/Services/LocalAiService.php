<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;

class LocalAiService
{
    protected string $endpoint;
    protected string $model;

    public function __construct()
    {
        $this->endpoint = rtrim(config('ai.local.endpoint', 'http://localhost:11434'), '/');
        $this->model = config('ai.local.model', 'synapse-nlp:latest');
    }

    /**
     * Parse a natural language prompt via a local OpenAI-compatible endpoint (Ollama / llama.cpp).
     */
    public function parse(string $prompt, int $userId): ?array
    {
        $nowStr = Carbon::now()->toDateTimeString();

        try {
            $response = Http::timeout(30)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$this->endpoint}/v1/chat/completions", [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $this->systemPrompt($nowStr),
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'temperature' => 0.1,
                    'max_tokens' => 512,
                    'response_format' => ['type' => 'json_object'],
                ]);

            if (!$response->successful()) {
                logger()->warning('Local AI endpoint returned non-200', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $body = $response->json();
            $content = $body['choices'][0]['message']['content'] ?? null;

            if (!$content) {
                return null;
            }

            $parsed = json_decode(trim($content), true);

            if (!is_array($parsed) || !isset($parsed['intent'])) {
                logger()->warning('Local AI returned malformed JSON', ['raw' => $content]);
                return null;
            }

            return $parsed;

        } catch (\Exception $e) {
            logger()->error('Local AI parse failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if the local endpoint is reachable and the model is loaded.
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->endpoint}/api/tags");
            if (!$response->successful()) {
                return false;
            }
            $models = $response->json('models', []);
            foreach ($models as $m) {
                if (($m['name'] ?? '') === $this->model) {
                    return true;
                }
            }
            // Model not found but server is up — we can pull it
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function systemPrompt(string $nowStr): string
    {
        return "You are the Natural Language Processing engine for Synapse, a personal assistant, accountant, and project distributor app.
Current local time is: {$nowStr}.

Parse the user's input and return ONLY a valid JSON object. No markdown, no extra text.

The JSON format must be:
{
  \"intent\": \"schedule_task\" | \"record_transaction\" | \"publish_media\" | \"query_tasks\" | \"query_finances\" | \"query_queue\" | \"unknown\",
  \"parameters\": { ... }
}

Guidelines for parameters based on intent:

1. schedule_task:
- \"title\": string (What the user wants to do, clear summary)
- \"description\": string or null
- \"due_at\": string (format: \"YYYY-MM-DD HH:MM:SS\") or null. Interpret relative times like \"tomorrow at 2pm\" or \"friday morning\" relative to {$nowStr}.
- \"priority\": \"low\" | \"medium\" | \"high\"

2. record_transaction:
- \"type\": \"income\" | \"expense\"
- \"amount\": float (> 0)
- \"currency\": string (3 letters, default \"GHS\")
- \"category\": string (e.g. \"Rent Expense\", \"Marketing\", \"Consulting Revenue\")
- \"description\": string or null
- \"occurred_at\": string (format: \"YYYY-MM-DD\", default today)

3. publish_media:
- \"media_title\": string
- \"channel\": \"youtube\" | \"spotify\" | \"audiomack\" | \"instagram\" | \"linkedin\" | \"facebook\" | \"website\"
- \"caption\": string or null
- \"scheduled_at\": string (format: \"YYYY-MM-DD HH:MM:SS\") or null

4. query_tasks:
- \"status\": \"pending\" | \"in_progress\" | \"completed\" | \"cancelled\" | \"all\"

5. query_finances:
- \"query_type\": \"balance\" | \"total_income\" | \"total_expense\" | \"budget_status\" | \"list\"

6. query_queue:
- \"status\": \"pending\" | \"scheduled\" | \"published\" | \"failed\" | \"all\"

Return 'unknown' intent if you cannot map the input.";
    }
}
