<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AiAssistantService
{
    protected LocalAiService $localAi;

    public function __construct(LocalAiService $localAi)
    {
        $this->localAi = $localAi;
    }

    /**
     * Parse natural language command using configured provider.
     */
    public function parse(string $prompt, int $userId): array
    {
        $provider = config('ai.provider', 'regex');

        $result = match ($provider) {
            'gemini' => $this->parseWithGemini($prompt),
            'local'  => $this->localAi->parse($prompt, $userId),
            default  => null,
        };

        if (!$result) {
            $result = $this->localParse($prompt);
        }

        return $this->postProcess($result, $userId);
    }

    /**
     * Parse via Google Gemini API.
     */
    protected function parseWithGemini(string $prompt): ?array
    {
        $apiKey = config('ai.gemini.key');
        if (!$apiKey) {
            return null;
        }

        $model = config('ai.gemini.model', 'gemini-2.5-flash');
        $nowStr = Carbon::now()->toDateTimeString();

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [
                        ['parts' => [['text' => $this->getPromptContext($prompt, $nowStr)]]],
                    ],
                    'generationConfig' => ['responseMimeType' => 'application/json'],
                ]);

            if ($response->successful()) {
                $textResponse = $response->json('candidates.0.content.parts.0.text');
                if ($textResponse) {
                    $parsed = json_decode(trim($textResponse), true);
                    if (is_array($parsed) && isset($parsed['intent'])) {
                        return $parsed;
                    }
                }
            }
        } catch (\Exception $e) {
            logger()->error('Gemini API parse failed: ' . $e->getMessage());
        }

        return null;
    }

    protected function getPromptContext(string $prompt, string $nowStr): string
    {
        return "You are the Natural Language Processing engine for Synapse, a personal assistant, accountant, and project distributor app.
Current local time is: {$nowStr}.

Parse the user's input: \"{$prompt}\"

You must output ONLY a valid JSON object. Do not wrap in markdown or any other tags.
The JSON format must be:
{
  \"intent\": \"schedule_task\" | \"record_transaction\" | \"publish_media\" | \"query_tasks\" | \"query_finances\" | \"query_queue\" | \"unknown\",
  \"parameters\": { ... }
}

Guidelines for parameters based on intent:

1. schedule_task:
- \"title\": string (What the user wants to do, clear summary)
- \"description\": string or null (additional context or details)
- \"due_at\": string (format: \"YYYY-MM-DD HH:MM:SS\") or null if no time specified. Interpret relative times like \"tomorrow at 2pm\" or \"friday morning\" relative to current time {$nowStr}.
- \"priority\": \"low\" | \"medium\" | \"high\" (default \"medium\")

2. record_transaction:
- \"type\": \"income\" | \"expense\"
- \"amount\": float (must be greater than 0)
- \"currency\": string (3 letters e.g., \"GHS\", \"USD\", \"EUR\", \"GBP\". Default \"GHS\")
- \"category\": string (suitable ledger category e.g., \"Rent Expense\", \"Saas Subscriptions\", \"Marketing\", \"Consulting Revenue\", \"Product Sales\")
- \"description\": string or null
- \"occurred_at\": string (format: \"YYYY-MM-DD\", default to today)

3. publish_media:
- \"media_title\": string (title of the media the user wants to publish)
- \"channel\": \"youtube\" | \"spotify\" | \"audiomack\" | \"instagram\" | \"linkedin\" | \"facebook\" | \"website\"
- \"caption\": string or null (accompanying description or notes)
- \"scheduled_at\": string (format: \"YYYY-MM-DD HH:MM:SS\") or null for immediate publishing.

4. query_tasks:
- \"status\": \"pending\" | \"in_progress\" | \"completed\" | \"cancelled\" | \"all\" (default \"all\")

5. query_finances:
- \"query_type\": \"balance\" | \"total_income\" | \"total_expense\" | \"budget_status\" | \"list\" (default \"balance\")

6. query_queue:
- \"status\": \"pending\" | \"scheduled\" | \"published\" | \"failed\" | \"all\" (default \"all\")

Return 'unknown' intent if you cannot map the input to any of the above.";
    }

    /**
     * Fallback local parser using regex and simple rules.
     */
    private function localParse(string $prompt): array
    {
        $promptLower = strtolower($prompt);

        $isQuery = Str::contains($promptLower, ['what', 'how', 'show', 'list', 'view', 'summary', 'status', 'report', 'am i', 'find', 'get', 'check', 'current', 'active', 'recent', 'total', 'have i', 'which', 'many', 'upcoming', 'next', 'sooner', 'earlier', 'later']);

        if ($isQuery) {
            if (Str::contains($promptLower, ['task', 'tasks', 'todo', 'todos', 'checklist', 'schedule', 'scheduled', 'upcoming', 'next', 'sooner', 'earlier', 'later', 'due', 'overdue'])) {
                $status = 'all';
                if (Str::contains($promptLower, 'completed')) $status = 'completed';
                elseif (Str::contains($promptLower, 'cancelled')) $status = 'cancelled';
                elseif (Str::contains($promptLower, 'pending')) $status = 'pending';
                elseif (Str::contains($promptLower, ['in progress', 'active'])) $status = 'in_progress';

                return [
                    'intent' => 'query_tasks',
                    'parameters' => ['status' => $status]
                ];
            }

            if (Str::contains($promptLower, ['finance', 'finances', 'spent', 'spending', 'income', 'expense', 'expenses', 'balance', 'budget', 'budgets', 'p&l', 'profit', 'loss', 'ledger', 'cost'])) {
                $queryType = 'balance';
                if (Str::contains($promptLower, ['budget', 'budgets'])) $queryType = 'budget_status';
                elseif (Str::contains($promptLower, ['income'])) $queryType = 'total_income';
                elseif (Str::contains($promptLower, ['spent', 'spending', 'expense', 'expenses', 'cost'])) $queryType = 'total_expense';
                elseif (Str::contains($promptLower, ['list', 'recent', 'transactions'])) $queryType = 'list';

                return [
                    'intent' => 'query_finances',
                    'parameters' => ['query_type' => $queryType]
                ];
            }

            if (Str::contains($promptLower, ['publish', 'queue', 'queued', 'distribute', 'distribution', 'channel', 'channels', 'upload', 'uploads', 'youtube', 'spotify', 'audiomack', 'facebook'])) {
                $status = 'all';
                if (Str::contains($promptLower, 'published')) $status = 'published';
                elseif (Str::contains($promptLower, 'failed')) $status = 'failed';
                elseif (Str::contains($promptLower, 'pending')) $status = 'pending';
                elseif (Str::contains($promptLower, 'scheduled')) $status = 'scheduled';

                return [
                    'intent' => 'query_queue',
                    'parameters' => ['status' => $status]
                ];
            }
        }

        // record_transaction
        if (Str::contains($promptLower, ['spend', 'spent', 'log expense', 'record expense', 'paid', 'buy', 'bought', 'expense', 'income', 'receive', 'received', 'earned', 'got paid', 'log income'])) {
            $type = Str::contains($promptLower, ['income', 'receive', 'received', 'earned', 'got paid', 'log income']) ? 'income' : 'expense';

            $amount = 0.0;
            if (preg_match('/(\d+(?:\.\d+)?)\s*(ghs|usd|eur|gbp|dollars|cedis|euro|pounds)?/i', $prompt, $matches)) {
                $amount = floatval($matches[1]);
            }

            $currency = 'GHS';
            if (preg_match('/(usd|eur|gbp|dollars|cedis|euro|pounds)/i', $promptLower, $cMatches)) {
                $c = $cMatches[1];
                $currency = match ($c) {
                    'usd', 'dollars' => 'USD',
                    'eur', 'euro' => 'EUR',
                    'gbp', 'pounds' => 'GBP',
                    default => 'GHS',
                };
            }

            $category = $type === 'income' ? 'Consulting Revenue' : 'Utilities';
            if (Str::contains($promptLower, ['rent'])) $category = 'Rent Expense';
            elseif (Str::contains($promptLower, ['marketing', 'ad', 'ads', 'promo'])) $category = 'Marketing';
            elseif (Str::contains($promptLower, ['software', 'subscription', 'saas', 'server', 'hosting'])) $category = 'Software Subscriptions';
            elseif (Str::contains($promptLower, ['travel', 'flight', 'uber', 'taxi'])) $category = 'Travel';
            elseif (Str::contains($promptLower, ['sale', 'product', 'shop'])) $category = 'Product Sales';

            return [
                'intent' => 'record_transaction',
                'parameters' => [
                    'type' => $type,
                    'amount' => $amount,
                    'currency' => $currency,
                    'category' => $category,
                    'description' => $prompt,
                    'occurred_at' => Carbon::today()->toDateString(),
                ]
            ];
        }

        // publish_media
        if (Str::contains($promptLower, ['publish', 'upload', 'queue', 'post', 'distribute'])) {
            $channel = 'youtube';
            if (Str::contains($promptLower, 'spotify')) $channel = 'spotify';
            elseif (Str::contains($promptLower, 'audiomack')) $channel = 'audiomack';
            elseif (Str::contains($promptLower, 'instagram')) $channel = 'instagram';
            elseif (Str::contains($promptLower, 'linkedin')) $channel = 'linkedin';
            elseif (Str::contains($promptLower, 'facebook')) $channel = 'facebook';
            elseif (Str::contains($promptLower, 'website')) $channel = 'website';

            $mediaTitle = 'Media Asset';
            if (preg_match('/(?:publish|upload|post)\s+["\']?([^"\']+)["\']?/i', $prompt, $mMatches)) {
                $mediaTitle = trim($mMatches[1]);
            }

            return [
                'intent' => 'publish_media',
                'parameters' => [
                    'media_title' => $mediaTitle,
                    'channel' => $channel,
                    'caption' => $prompt,
                    'scheduled_at' => null,
                ]
            ];
        }

        // schedule_task fallback
        $priority = 'medium';
        if (Str::contains($promptLower, 'high')) $priority = 'high';
        elseif (Str::contains($promptLower, 'low')) $priority = 'low';

        $dueAt = null;
        $titleClean = preg_replace('/^(?:schedule|add|create|set|make|remind me to|remind me|i want to|i need to)\s+(?:(?:a|an|the)\s+)?(?:task|event|meeting|appointment)?\s*(?:for|to|about)?\s*/i', '', $prompt);
        $titleClean = trim($titleClean);

        if (preg_match('/(?:at\s+)?(\d{1,2})(?::(\d{2}))?\s*(am|pm|a\.m\.|p\.m\.)/i', $promptLower, $tMatches)) {
            $hour = (int) $tMatches[1];
            $minute = !empty($tMatches[2]) ? (int) $tMatches[2] : 0;
            $ampm = strtolower($tMatches[3]);

            if ($ampm === 'pm' || $ampm === 'p.m.') {
                if ($hour < 12) $hour += 12;
            } elseif ($ampm === 'am' || $ampm === 'a.m.') {
                if ($hour === 12) $hour = 0;
            }

            $date = Carbon::today();
            if (Str::contains($promptLower, 'tomorrow')) {
                $date = Carbon::tomorrow();
            } elseif (preg_match('/\b(?:next\s+)?(monday|tuesday|wednesday|thursday|friday|saturday|sunday)\b/i', $promptLower, $dMatches)) {
                $date = Carbon::parse($dMatches[1]);
            }

            $dueAt = $date->copy()->setHour($hour)->setMinute($minute)->setSecond(0)->toDateTimeString();
            $titleClean = preg_replace('/\b(?:at\s+)?\d{1,2}(?::\d{2})?\s*(?:am|pm|a\.m\.|p\.m\.)\b/i', '', $titleClean);
        } elseif (Str::contains($promptLower, 'tomorrow')) {
            $dueAt = Carbon::tomorrow()->setHour(9)->setMinute(0)->toDateTimeString();
        } elseif (preg_match('/\b(?:next\s+)?(monday|tuesday|wednesday|thursday|friday|saturday|sunday)\b/i', $promptLower, $dMatches)) {
            $dueAt = Carbon::parse($dMatches[1])->setHour(10)->setMinute(0)->toDateTimeString();
        } elseif (preg_match('/(\d{4}-\d{2}-\d{2})/', $prompt, $dMatches)) {
            $dueAt = Carbon::parse($dMatches[1])->setHour(9)->setMinute(0)->toDateTimeString();
        }

        $titleClean = preg_replace('/\b(today|tomorrow|next|at|by|before|after|this|coming)\b/i', '', $titleClean);
        $titleClean = preg_replace('/\s{2,}/', ' ', $titleClean);
        $titleClean = trim($titleClean);
        $title = $titleClean ?: $prompt;

        return [
            'intent' => 'schedule_task',
            'parameters' => [
                'title' => Str::limit($title, 80),
                'description' => $prompt,
                'due_at' => $dueAt,
                'priority' => $priority,
            ]
        ];
    }

    private function postProcess(array $parsed, int $userId): array
    {
        if ($parsed['intent'] === 'schedule_task') {
            $dueAt = $parsed['parameters']['due_at'] ?? null;
            if ($dueAt) {
                $dueCarbon = Carbon::parse($dueAt);
                $start = $dueCarbon->copy()->subHour();
                $end = $dueCarbon->copy()->addHour();

                $conflicts = Task::query()
                    ->where('user_id', $userId)
                    ->whereBetween('due_at', [$start, $end])
                    ->whereNotIn('status', ['completed', 'cancelled'])
                    ->get();

                if ($conflicts->isNotEmpty()) {
                    $parsed['conflict'] = true;
                    $parsed['conflicting_tasks'] = $conflicts->map(fn($t) => [
                        'id' => $t->id,
                        'title' => $t->title,
                        'due_at' => $t->due_at->toDateTimeString(),
                    ])->toArray();

                    $altSlot = $dueCarbon->copy()->addHour();
                    while (Task::query()
                        ->where('user_id', $userId)
                        ->whereBetween('due_at', [$altSlot->copy()->subHour(), $altSlot->copy()->addHour()])
                        ->whereNotIn('status', ['completed', 'cancelled'])
                        ->exists()
                    ) {
                        $altSlot->addHour();
                    }
                    $parsed['alternative_due_at'] = $altSlot->toDateTimeString();
                } else {
                    $parsed['conflict'] = false;
                }
            } else {
                $parsed['conflict'] = false;
            }
        }

        return $parsed;
    }
}
