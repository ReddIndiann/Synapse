<?php

namespace App\Services;

use App\Services\Ai\AiProviderManager;
use App\Models\Task;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class AiAssistantService
{
    protected AiProviderManager $providers;

    public function __construct(AiProviderManager $providers)
    {
        $this->providers = $providers;
    }

    /**
     * Parse natural language command using configured provider.
     */
    public function parse(string $prompt, int $userId): array
    {
        $result = $this->providers->parse($prompt, $userId);

        if (!$result) {
            $result = $this->localParse($prompt);
        }

        $result = $this->normalizeIntents($result);

        return $this->postProcess($result, $userId);
    }

    /**
     * Map legacy intents to manage_task / manage_budget.
     */
    protected function normalizeIntents(array $parsed): array
    {
        $intent = $parsed['intent'] ?? 'unknown';
        $params = $parsed['parameters'] ?? [];

        if ($intent === 'schedule_task') {
            return [
                'intent' => 'manage_task',
                'parameters' => array_merge($params, ['action' => 'create']),
            ];
        }

        if ($intent === 'query_tasks') {
            return [
                'intent' => 'manage_task',
                'parameters' => array_merge($params, ['action' => 'read']),
            ];
        }

        if ($intent === 'query_finances' && ($params['query_type'] ?? '') === 'budget_status') {
            return [
                'intent' => 'manage_budget',
                'parameters' => array_merge($params, ['action' => 'read']),
            ];
        }

        return $parsed;
    }

    /**
     * Fallback local parser using regex and simple rules.
     * Domain-first: budget vs task before catch-all create.
     */
    private function localParse(string $prompt): array
    {
        $promptLower = strtolower($prompt);

        $budgetSignals = ['budget', 'budgets', 'spending limit', 'allowance', 'spending cap'];
        $taskSignals = ['task', 'tasks', 'todo', 'todos', 'checklist', 'meeting', 'appointment', 'remind', 'reminder'];
        $hasBudget = Str::contains($promptLower, $budgetSignals);
        $hasTask = Str::contains($promptLower, $taskSignals);

        $isDelete = (bool) preg_match('/\b(delete|remove|drop|erase)\b/i', $promptLower);
        $isUpdate = (bool) preg_match('/\b(update|change|edit|set|increase|decrease|raise|lower)\b/i', $promptLower);
        $isComplete = (bool) preg_match('/\b(mark\s+(as\s+)?complete|complete|finish|done)\b/i', $promptLower);
        $isQuery = Str::contains($promptLower, ['what', 'how', 'show', 'list', 'view', 'summary', 'status', 'report', 'am i', 'find', 'get', 'check', 'current', 'active', 'recent', 'total', 'have i', 'which', 'many', 'upcoming', 'next', 'sooner', 'earlier', 'later']);

        // --- Budget CRUD (domain gate first) ---
        if ($hasBudget) {
            if ($isDelete) {
                return [
                    'intent' => 'manage_budget',
                    'parameters' => [
                        'action' => 'delete',
                        'name' => $this->extractBudgetHint($prompt),
                        'category' => $this->extractBudgetHint($prompt),
                    ],
                ];
            }

            if ($isUpdate || preg_match('/\b(set|create|add|make|new)\b/i', $promptLower)) {
                $amount = $this->extractAmount($prompt);
                $hint = $this->extractBudgetHint($prompt);
                $period = 'monthly';
                if (Str::contains($promptLower, 'quarterly')) $period = 'quarterly';
                elseif (Str::contains($promptLower, 'yearly') || Str::contains($promptLower, 'annual')) $period = 'yearly';

                // "set/create/add" establishes or upserts a budget; explicit update verbs → update
                $action = 'create';
                if (preg_match('/\b(update|change|edit|increase|decrease|raise|lower)\b/i', $promptLower)) {
                    $action = 'update';
                }

                return [
                    'intent' => 'manage_budget',
                    'parameters' => [
                        'action' => $action,
                        'name' => $hint ?: 'Budget',
                        'category' => $hint ?: 'General',
                        'amount' => $amount,
                        'period' => $period,
                    ],
                ];
            }

            if ($isQuery || true) {
                // Default budget mentions without verbs → read
                return [
                    'intent' => 'manage_budget',
                    'parameters' => [
                        'action' => 'read',
                        'name' => $this->extractBudgetHint($prompt),
                        'category' => $this->extractBudgetHint($prompt),
                    ],
                ];
            }
        }

        // --- Task delete / complete / update ---
        if ($isDelete && ($hasTask || !$hasBudget)) {
            // Prefer task delete when "task" mentioned, or delete without budget
            if ($hasTask || preg_match('/\b(delete|remove)\b.+\b(task|todo|meeting)\b/i', $promptLower)
                || preg_match('/\b(delete|remove)\b\s+["\']?(.+?)["\']?\s*(task|todo)?$/i', $promptLower)) {
                return [
                    'intent' => 'manage_task',
                    'parameters' => [
                        'action' => 'delete',
                        'title' => $this->extractTaskHint($prompt),
                    ],
                ];
            }
        }

        if ($isComplete && !$hasBudget) {
            return [
                'intent' => 'manage_task',
                'parameters' => [
                    'action' => 'complete',
                    'title' => $this->extractTaskHint($prompt),
                ],
            ];
        }

        if ($isUpdate && $hasTask && !$hasBudget) {
            $params = [
                'action' => 'update',
                'title' => $this->extractTaskHint($prompt),
            ];
            if (Str::contains($promptLower, 'high')) $params['priority'] = 'high';
            elseif (Str::contains($promptLower, 'low')) $params['priority'] = 'low';
            $due = $this->extractDueAt($prompt, $promptLower);
            if ($due) $params['due_at'] = $due;
            return ['intent' => 'manage_task', 'parameters' => $params];
        }

        // --- Queries ---
        if ($isQuery) {
            if ($hasTask || Str::contains($promptLower, ['upcoming', 'next', 'sooner', 'earlier', 'later', 'due', 'overdue', 'schedule', 'scheduled'])) {
                $status = 'all';
                if (Str::contains($promptLower, 'completed')) $status = 'completed';
                elseif (Str::contains($promptLower, 'cancelled')) $status = 'cancelled';
                elseif (Str::contains($promptLower, 'pending')) $status = 'pending';
                elseif (Str::contains($promptLower, ['in progress', 'active'])) $status = 'in_progress';

                return [
                    'intent' => 'manage_task',
                    'parameters' => ['action' => 'read', 'status' => $status],
                ];
            }

            if (Str::contains($promptLower, ['finance', 'finances', 'spent', 'spending', 'income', 'expense', 'expenses', 'balance', 'p&l', 'profit', 'loss', 'ledger', 'cost'])) {
                $queryType = 'balance';
                if (Str::contains($promptLower, ['income'])) $queryType = 'total_income';
                elseif (Str::contains($promptLower, ['spent', 'spending', 'expense', 'expenses', 'cost'])) $queryType = 'total_expense';
                elseif (Str::contains($promptLower, ['list', 'recent', 'transactions'])) $queryType = 'list';

                return [
                    'intent' => 'query_finances',
                    'parameters' => ['query_type' => $queryType],
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
                    'parameters' => ['status' => $status],
                ];
            }
        }

        // --- record_transaction ---
        if (Str::contains($promptLower, ['spend', 'spent', 'log expense', 'record expense', 'paid', 'buy', 'bought', 'expense', 'income', 'receive', 'received', 'earned', 'got paid', 'log income'])) {
            $type = Str::contains($promptLower, ['income', 'receive', 'received', 'earned', 'got paid', 'log income']) ? 'income' : 'expense';

            $amount = $this->extractAmount($prompt);

            $currency = 'GHS';
            if (preg_match('/(usd|eur|gbp|dollars|cedis|euro|pounds)/i', $promptLower, $cMatches)) {
                $c = strtolower($cMatches[1]);
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
                ],
            ];
        }

        // --- publish_media ---
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
                ],
            ];
        }

        // --- schedule_task only with clear task signals or schedule verbs ---
        $scheduleVerb = (bool) preg_match('/\b(schedule|add|create|set|make|remind me|i want to|i need to)\b/i', $promptLower);
        $hasTime = (bool) preg_match('/\b(tomorrow|today|monday|tuesday|wednesday|thursday|friday|saturday|sunday|\d{1,2}\s*(am|pm)|at\s+\d)/i', $promptLower);

        if ($hasTask || $scheduleVerb || $hasTime) {
            $priority = 'medium';
            if (Str::contains($promptLower, 'high')) $priority = 'high';
            elseif (Str::contains($promptLower, 'low')) $priority = 'low';

            $dueAt = $this->extractDueAt($prompt, $promptLower);
            $titleClean = preg_replace('/^(?:schedule|add|create|set|make|remind me to|remind me|i want to|i need to)\s+(?:(?:a|an|the)\s+)?(?:task|event|meeting|appointment)?\s*(?:for|to|about)?\s*/i', '', $prompt);
            $titleClean = trim($titleClean);
            $titleClean = preg_replace('/\b(?:at\s+)?\d{1,2}(?::\d{2})?\s*(?:am|pm|a\.m\.|p\.m\.)\b/i', '', $titleClean);
            $titleClean = preg_replace('/\b(today|tomorrow|next|at|by|before|after|this|coming|high|low|priority|medium)\b/i', '', $titleClean);
            $titleClean = preg_replace('/\s{2,}/', ' ', $titleClean);
            $titleClean = trim($titleClean);
            $title = $titleClean ?: $prompt;

            return [
                'intent' => 'manage_task',
                'parameters' => [
                    'action' => 'create',
                    'title' => Str::limit($title, 80),
                    'description' => $prompt,
                    'due_at' => $dueAt,
                    'priority' => $priority,
                ],
            ];
        }

        return [
            'intent' => 'unknown',
            'parameters' => [],
        ];
    }

    private function extractAmount(string $prompt): float
    {
        if (preg_match('/(\d+(?:\.\d+)?)\s*(ghs|usd|eur|gbp|dollars|cedis|euro|pounds)?/i', $prompt, $matches)) {
            return floatval($matches[1]);
        }
        return 0.0;
    }

    private function extractBudgetHint(string $prompt): ?string
    {
        // "marketing budget", "rent budget", "budget for marketing"
        if (preg_match('/\b(?:for\s+)?([a-z0-9][\w\s-]{0,40}?)\s+budget\b/i', $prompt, $m)) {
            $hint = trim($m[1]);
            $hint = preg_replace('/\b(set|create|add|make|new|update|change|edit|delete|remove|my|the|a|an)\b/i', '', $hint);
            $hint = trim(preg_replace('/\s{2,}/', ' ', $hint));
            if ($hint !== '') return Str::title($hint);
        }
        if (preg_match('/\bbudget\s+(?:for|on|of)\s+([a-z0-9][\w\s-]{0,40}?)(?:\s+to|\s+of|\s+at|$)/i', $prompt, $m)) {
            return Str::title(trim($m[1]));
        }
        return null;
    }

    private function extractTaskHint(string $prompt): string
    {
        $clean = preg_replace('/\b(delete|remove|drop|erase|mark|as|complete|finish|done|update|change|edit|reschedule|the|a|an|my|task|todo|meeting)\b/i', ' ', $prompt);
        $clean = preg_replace('/\s{2,}/', ' ', $clean);
        return trim($clean) ?: $prompt;
    }

    private function extractDueAt(string $prompt, string $promptLower): ?string
    {
        $dueAt = null;

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
        } elseif (Str::contains($promptLower, 'tomorrow')) {
            $dueAt = Carbon::tomorrow()->setHour(9)->setMinute(0)->toDateTimeString();
        } elseif (preg_match('/\b(?:next\s+)?(monday|tuesday|wednesday|thursday|friday|saturday|sunday)\b/i', $promptLower, $dMatches)) {
            $dueAt = Carbon::parse($dMatches[1])->setHour(10)->setMinute(0)->toDateTimeString();
        } elseif (preg_match('/(\d{4}-\d{2}-\d{2})/', $prompt, $dMatches)) {
            $dueAt = Carbon::parse($dMatches[1])->setHour(9)->setMinute(0)->toDateTimeString();
        }

        return $dueAt;
    }

    private function postProcess(array $parsed, int $userId): array
    {
        $intent = $parsed['intent'] ?? 'unknown';
        $action = $parsed['parameters']['action'] ?? null;

        if ($intent === 'manage_task' && $action === 'create') {
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
                    $parsed['conflicting_tasks'] = $conflicts->map(fn ($t) => [
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
