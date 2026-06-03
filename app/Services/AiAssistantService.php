<?php

namespace App\Services;

use App\Models\Task;
use App\Models\MediaAsset;
use App\Models\DistributionChannel;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class AiAssistantService
{
    /**
     * Parse natural language command.
     */
    public function parse(string $prompt, int $userId): array
    {
        $apiKey = config('services.gemini.key') ?: env('GEMINI_API_KEY');
        $nowStr = Carbon::now()->toDateTimeString();

        $result = null;

        if ($apiKey) {
            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $this->getPromptContext($prompt, $nowStr)]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                    ]
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $textResponse = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if ($textResponse) {
                        $result = json_decode(trim($textResponse), true);
                    }
                }
            } catch (\Exception $e) {
                // Fallback to local parser on exception
                logger()->error('Gemini API parse failed: ' . $e->getMessage());
            }
        }

        if (!$result) {
            $result = $this->localParse($prompt);
        }

        // Apply context post-processing (e.g. checking conflict)
        return $this->postProcess($result, $userId);
    }

    /**
     * System prompt context for Gemini.
     */
    private function getPromptContext(string $prompt, string $nowStr): string
    {
        return "You are the Natural Language Processing engine for Synapse, a personal assistant, accountant, and project distributor app.
Current local time is: {$nowStr}.

Parse the user's input: \"{$prompt}\"

You must output ONLY a valid JSON object. Do not wrap in markdown or any other tags.
The JSON format must be:
{
  \"intent\": \"schedule_task\" | \"record_transaction\" | \"publish_media\" | \"unknown\",
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
- \"category\": string (suitable ledger category e.g., \"Rent Expense\", \"Utilities\", \"Marketing\", \"Consulting Revenue\", \"Product Sales\")
- \"description\": string or null
- \"occurred_at\": string (format: \"YYYY-MM-DD\", default to today)

3. publish_media:
- \"media_title\": string (title of the media the user wants to publish)
- \"channel\": \"youtube\" | \"spotify\" | \"audiomack\" | \"instagram\" | \"linkedin\" | \"facebook\" | \"website\"
- \"caption\": string or null (accompanying description or notes)
- \"scheduled_at\": string (format: \"YYYY-MM-DD HH:MM:SS\") or null for immediate publishing.

Return 'unknown' intent if you cannot map the input to any of the above.";
    }

    /**
     * Fallback local parser using regex and simple rules.
     */
    private function localParse(string $prompt): array
    {
        $promptLower = strtolower($prompt);

        // 1. Check for record_transaction
        if (Str::contains($promptLower, ['spend', 'spent', 'spent ', 'log expense', 'record expense', 'paid', 'buy', 'bought', 'expense', 'income', 'receive', 'received', 'earned', 'got paid', 'log income'])) {
            $type = 'expense';
            if (Str::contains($promptLower, ['income', 'receive', 'received', 'earned', 'got paid', 'log income'])) {
                $type = 'income';
            }

            // Extract amount
            $amount = 0.0;
            if (preg_match('/(\d+(?:\.\d+)?)\s*(ghs|usd|eur|gbp|dollars|cedis|euro|pounds)?/i', $prompt, $matches)) {
                $amount = floatval($matches[1]);
            }

            // Extract currency
            $currency = 'GHS';
            if (preg_match('/(usd|eur|gbp|dollars|cedis|euro|pounds)/i', $promptLower, $cMatches)) {
                $c = $cMatches[1];
                if ($c === 'usd' || $c === 'dollars') $currency = 'USD';
                elseif ($c === 'eur' || $c === 'euro') $currency = 'EUR';
                elseif ($c === 'gbp' || $c === 'pounds') $currency = 'GBP';
                else $currency = 'GHS';
            }

            // Extract Category
            $category = $type === 'income' ? 'Consulting Revenue' : 'Utilities';
            if (Str::contains($promptLower, ['rent'])) $category = 'Rent Expense';
            elseif (Str::contains($promptLower, ['marketing', 'ad', 'ads', 'promo'])) $category = 'Marketing';
            elseif (Str::contains($promptLower, ['software', 'subscription', 'saas', 'server', 'hosting'])) $category = 'Software Subscriptions';
            elseif (Str::contains($promptLower, ['travel', 'flight', 'uber', 'taxi'])) $category = 'Travel';
            elseif (Str::contains($promptLower, ['sale', 'product', 'shop'])) $category = 'Product Sales';

            // Extract description
            $description = $prompt;

            return [
                'intent' => 'record_transaction',
                'parameters' => [
                    'type' => $type,
                    'amount' => $amount,
                    'currency' => $currency,
                    'category' => $category,
                    'description' => $description,
                    'occurred_at' => Carbon::today()->toDateString(),
                ]
            ];
        }

        // 2. Check for publish_media
        if (Str::contains($promptLower, ['publish', 'upload', 'queue', 'post', 'distribute'])) {
            $channel = 'youtube';
            if (Str::contains($promptLower, 'spotify')) $channel = 'spotify';
            elseif (Str::contains($promptLower, 'audiomack')) $channel = 'audiomack';
            elseif (Str::contains($promptLower, 'instagram')) $channel = 'instagram';
            elseif (Str::contains($promptLower, 'linkedin')) $channel = 'linkedin';
            elseif (Str::contains($promptLower, 'facebook')) $channel = 'facebook';
            elseif (Str::contains($promptLower, 'website')) $channel = 'website';

            // Extract media title: look for words after publish/upload
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
                    'scheduled_at' => null, // default to immediate
                ]
            ];
        }

        // 3. Fallback to schedule_task
        $priority = 'medium';
        if (Str::contains($promptLower, 'high')) $priority = 'high';
        elseif (Str::contains($promptLower, 'low')) $priority = 'low';

        $dueAt = null;
        if (Str::contains($promptLower, 'tomorrow')) {
            $dueAt = Carbon::tomorrow()->setHour(9)->setMinute(0)->toDateTimeString();
        } elseif (Str::contains($promptLower, 'friday')) {
            $dueAt = Carbon::parse('next friday')->setHour(10)->setMinute(0)->toDateTimeString();
        } elseif (preg_match('/(\d{4}-\d{2}-\d{2})/', $prompt, $dMatches)) {
            $dueAt = Carbon::parse($dMatches[1])->setHour(9)->setMinute(0)->toDateTimeString();
        }

        return [
            'intent' => 'schedule_task',
            'parameters' => [
                'title' => Str::limit($prompt, 80),
                'description' => $prompt,
                'due_at' => $dueAt,
                'priority' => $priority,
            ]
        ];
    }

    /**
     * Post-process parsed results (e.g. check for conflicts, link channels).
     */
    private function postProcess(array $parsed, int $userId): array
    {
        if ($parsed['intent'] === 'schedule_task') {
            $dueAt = $parsed['parameters']['due_at'] ?? null;
            if ($dueAt) {
                $dueCarbon = Carbon::parse($dueAt);
                $start = $dueCarbon->copy()->subHour();
                $end = $dueCarbon->copy()->addHour();

                // Find conflicting tasks
                $conflicts = Task::query()
                    ->where('user_id', $userId)
                    ->whereBetween('due_at', [$start, $end])
                    ->whereNotIn('status', ['completed', 'cancelled'])
                    ->get();

                if ($conflicts->isNotEmpty()) {
                    $parsed['conflict'] = true;
                    $parsed['conflicting_tasks'] = $conflicts->map(function ($t) {
                        return [
                            'id' => $t->id,
                            'title' => $t->title,
                            'due_at' => $t->due_at->toDateTimeString(),
                        ];
                    })->toArray();

                    // Propose alternative slot: next available hour on the same day, or next day
                    $altSlot = $dueCarbon->copy()->addHour();
                    while (Task::query()->where('user_id', $userId)->whereBetween('due_at', [$altSlot->copy()->subHour(), $altSlot->copy()->addHour()])->whereNotIn('status', ['completed', 'cancelled'])->exists()) {
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
