<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Assistant\AssistantChatRequest;
use App\Jobs\ProcessPublishJob;
use App\Models\AssistantMessage;
use App\Models\Budget;
use App\Models\DistributionChannel;
use App\Models\MediaAsset;
use App\Models\PublishJob;
use App\Models\Task;
use App\Models\Transaction;
use App\Services\AccountingLedgerService;
use App\Services\AiAssistantService;
use App\Services\EntityResolverService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AssistantController extends Controller
{
    protected $aiService;
    protected $ledgerService;
    protected EntityResolverService $resolver;

    public function __construct(
        AiAssistantService $aiService,
        AccountingLedgerService $ledgerService,
        EntityResolverService $resolver
    ) {
        $this->aiService = $aiService;
        $this->ledgerService = $ledgerService;
        $this->resolver = $resolver;
    }

    public function index(): View
    {
        $userId = auth()->id();

        $messages = AssistantMessage::query()
            ->where('user_id', $userId)
            ->oldest()
            ->get();

        if ($messages->isEmpty()) {
            $welcome = AssistantMessage::create([
                'user_id' => $userId,
                'role' => 'assistant',
                'content' => 'Hi! I am Synapse AI, your business management assistant. Ask me to schedule tasks, manage budgets, record transactions, or queue content. Try saying:
• "Schedule client call tomorrow at 10 AM, high priority"
• "Set marketing budget to 5000 GHS monthly"
• "Spent 150 GHS on Internet Utilities"
• "Publish my podcast video on YouTube"',
            ]);
            $messages = collect([$welcome]);
        }

        return view('assistant.chat', [
            'messages' => $messages,
            'recentTasks' => auth()->user()->tasks()->latest()->limit(5)->get(),
        ]);
    }

    public function store(AssistantChatRequest $request): RedirectResponse
    {
        $userId = auth()->id();
        $prompt = $request->validated('prompt');

        AssistantMessage::create([
            'user_id' => $userId,
            'role' => 'user',
            'content' => $prompt,
        ]);

        $parsed = $this->aiService->parse($prompt, $userId);

        $intent = $parsed['intent'] ?? 'unknown';
        $params = $parsed['parameters'] ?? [];

        switch ($intent) {
            case 'manage_task':
                $this->handleManageTask($userId, $params, $parsed);
                break;

            case 'manage_budget':
                $this->handleManageBudget($userId, $params);
                break;

            // Legacy aliases (normalized in AiAssistantService, kept as safety net)
            case 'schedule_task':
                $this->handleManageTask($userId, array_merge($params, ['action' => 'create']), $parsed);
                break;

            case 'query_tasks':
                $this->handleManageTask($userId, array_merge($params, ['action' => 'read']), $parsed);
                break;

            case 'record_transaction':
                $this->handleRecordTransaction($userId, $params);
                break;

            case 'publish_media':
                $this->handlePublishMedia($userId, $params);
                break;

            case 'query_finances':
                $this->handleQueryFinances($userId, $params);
                break;

            case 'query_queue':
                $this->handleQueryQueue($userId, $params);
                break;

            default:
                AssistantMessage::create([
                    'user_id' => $userId,
                    'role' => 'assistant',
                    'content' => "I couldn't map that to a specific action. Try:\n" .
                                 "• Tasks: 'Schedule project review tomorrow at 2 PM' · 'Delete client call task' · 'Show my tasks'\n" .
                                 "• Budgets: 'Set marketing budget to 5000 GHS' · 'Delete rent budget' · 'Show my budgets'\n" .
                                 "• Finance: 'Log expense of 50 GHS for server hosting'\n" .
                                 "• Publish: 'Queue YouTube publish job for new podcast episode'",
                ]);
                break;
        }

        return redirect()->route('assistant.chat');
    }

    protected function handleManageTask(int $userId, array $params, array $parsed = []): void
    {
        $action = $params['action'] ?? 'create';

        switch ($action) {
            case 'read':
                $this->listTasks($userId, $params['status'] ?? 'all');
                break;

            case 'complete':
            case 'update':
            case 'delete':
                $hint = $params['title'] ?? null;
                $resolved = $this->resolver->resolveTask($userId, $hint, $params['task_id'] ?? null);

                if ($resolved['status'] === 'none') {
                    AssistantMessage::create([
                        'user_id' => $userId,
                        'role' => 'assistant',
                        'content' => "I couldn't find a task matching \"" . ($hint ?: 'that') . "\". Try listing tasks with \"Show my tasks\".",
                    ]);
                    return;
                }

                if ($resolved['status'] === 'multiple') {
                    $msg = "I found multiple matching tasks. Which one did you mean?\n";
                    foreach ($resolved['candidates'] as $idx => $t) {
                        $msg .= ($idx + 1) . ". **{$t->title}** (ID #{$t->id})\n";
                    }
                    $msg .= "\nReply with something like \"Delete task #" . $resolved['candidates']->first()->id . "\" or use the full title.";
                    AssistantMessage::create([
                        'user_id' => $userId,
                        'role' => 'assistant',
                        'content' => $msg,
                        'metadata' => [
                            'type' => 'clarify_task',
                            'action' => $action,
                            'candidates' => $resolved['candidates']->map(fn ($t) => ['id' => $t->id, 'title' => $t->title])->values()->all(),
                        ],
                    ]);
                    return;
                }

                /** @var Task $task */
                $task = $resolved['match'];

                if ($action === 'delete') {
                    AssistantMessage::create([
                        'user_id' => $userId,
                        'role' => 'assistant',
                        'content' => "Are you sure you want to delete task **{$task->title}**? This cannot be undone.",
                        'metadata' => [
                            'type' => 'confirm_delete_task',
                            'task_id' => $task->id,
                            'task_title' => $task->title,
                        ],
                    ]);
                    return;
                }

                if ($action === 'complete') {
                    $task->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]);
                    AssistantMessage::create([
                        'user_id' => $userId,
                        'role' => 'assistant',
                        'content' => "Marked complete: '{$task->title}'.",
                    ]);
                    return;
                }

                // update
                $updates = [];
                if (!empty($params['priority'])) $updates['priority'] = $params['priority'];
                if (!empty($params['status'])) $updates['status'] = $params['status'];
                if (!empty($params['due_at'])) $updates['due_at'] = Carbon::parse($params['due_at']);
                if (!empty($params['description'])) $updates['description'] = $params['description'];
                if (!empty($params['new_title'])) $updates['title'] = $params['new_title'];

                if (empty($updates)) {
                    AssistantMessage::create([
                        'user_id' => $userId,
                        'role' => 'assistant',
                        'content' => "Found task '{$task->title}', but I need more details on what to change (priority, due date, or status).",
                    ]);
                    return;
                }

                $task->update($updates);
                AssistantMessage::create([
                    'user_id' => $userId,
                    'role' => 'assistant',
                    'content' => "Updated task '{$task->title}'.",
                ]);
                break;

            case 'create':
            default:
                $this->createTask($userId, $params, $parsed);
                break;
        }
    }

    protected function createTask(int $userId, array $params, array $parsed): void
    {
        $dueAt = !empty($params['due_at']) ? Carbon::parse($params['due_at']) : null;

        if (!empty($parsed['conflict'])) {
            $conflictingTaskNames = collect($parsed['conflicting_tasks'])->map(function ($t) {
                return "'{$t['title']}' at " . Carbon::parse($t['due_at'])->format('h:i A');
            })->join(', ');

            $aiContent = "Conflict detected! You already have task(s): {$conflictingTaskNames}. Would you like to reschedule this new task to {$parsed['alternative_due_at']}, force schedule it anyway, or cancel?";

            AssistantMessage::create([
                'user_id' => $userId,
                'role' => 'assistant',
                'content' => $aiContent,
                'metadata' => [
                    'type' => 'task_conflict',
                    'task_params' => $params,
                    'alternative_due_at' => $parsed['alternative_due_at'],
                ],
            ]);
            return;
        }

        $task = Task::create([
            'user_id' => $userId,
            'title' => $params['title'] ?? 'Untitled task',
            'description' => $params['description'] ?? null,
            'priority' => $params['priority'] ?? 'medium',
            'status' => 'pending',
            'due_at' => $dueAt,
        ]);

        $timeStr = $dueAt ? " for " . $dueAt->format('M j, Y \a\t h:i A') : "";
        AssistantMessage::create([
            'user_id' => $userId,
            'role' => 'assistant',
            'content' => "Task successfully scheduled: '{$task->title}'{$timeStr} (Priority: " . ucfirst($task->priority) . ").",
        ]);
    }

    protected function listTasks(int $userId, string $status): void
    {
        $query = Task::where('user_id', $userId);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $tasksList = $query->latest()->limit(8)->get();

        if ($tasksList->isEmpty()) {
            $msg = "You have no " . ($status !== 'all' ? str_replace('_', ' ', $status) . " " : "") . "tasks registered in your backlog.";
        } else {
            $msg = "Here are your " . ($status !== 'all' ? str_replace('_', ' ', $status) . " " : "") . "tasks:\n";
            foreach ($tasksList as $idx => $t) {
                $priorityBadge = strtoupper($t->priority);
                $dueStr = $t->due_at ? " (due " . $t->due_at->format('M j, g:i A') . ")" : "";
                $statusStr = str_replace('_', ' ', $t->status);
                $msg .= ($idx + 1) . ". **{$t->title}** — Priority: {$priorityBadge} · Status: {$statusStr}{$dueStr}\n";
            }
        }

        AssistantMessage::create([
            'user_id' => $userId,
            'role' => 'assistant',
            'content' => $msg,
        ]);
    }

    protected function handleManageBudget(int $userId, array $params): void
    {
        $action = $params['action'] ?? 'read';

        switch ($action) {
            case 'read':
                $this->listBudgets($userId, $params);
                break;

            case 'create':
                $amount = (float) ($params['amount'] ?? 0);
                if ($amount <= 0) {
                    AssistantMessage::create([
                        'user_id' => $userId,
                        'role' => 'assistant',
                        'content' => "I need an amount to create a budget. Try: \"Set marketing budget to 5000 GHS\".",
                    ]);
                    return;
                }

                $name = $params['name'] ?? $params['category'] ?? 'Budget';
                $category = $params['category'] ?? $params['name'] ?? 'General';
                $period = $params['period'] ?? 'monthly';

                // Upsert: if matching budget exists, update amount instead
                $resolved = $this->resolver->resolveBudget($userId, $category);
                if ($resolved['status'] === 'single') {
                    $budget = $resolved['match'];
                    $budget->update([
                        'amount' => $amount,
                        'period' => $period,
                        'name' => $params['name'] ?? $budget->name,
                    ]);
                    AssistantMessage::create([
                        'user_id' => $userId,
                        'role' => 'assistant',
                        'content' => "Updated budget **{$budget->name}** ({$budget->category}): limit set to " . number_format($amount, 2) . " GHS ({$period}).",
                    ]);
                    return;
                }

                $budget = Budget::create([
                    'user_id' => $userId,
                    'name' => $name,
                    'category' => $category,
                    'amount' => $amount,
                    'period' => $period,
                ]);

                AssistantMessage::create([
                    'user_id' => $userId,
                    'role' => 'assistant',
                    'content' => "Budget created: **{$budget->name}** for category '{$budget->category}' — " . number_format($amount, 2) . " GHS ({$period}).",
                ]);
                break;

            case 'update':
                $hint = $params['name'] ?? $params['category'] ?? null;
                $resolved = $this->resolver->resolveBudget($userId, $hint, $params['budget_id'] ?? null);

                if ($resolved['status'] === 'none') {
                    // Fall through to create if amount provided
                    if (!empty($params['amount']) && (float) $params['amount'] > 0) {
                        $this->handleManageBudget($userId, array_merge($params, ['action' => 'create']));
                        return;
                    }
                    AssistantMessage::create([
                        'user_id' => $userId,
                        'role' => 'assistant',
                        'content' => "I couldn't find a budget matching \"" . ($hint ?: 'that') . "\". Create one with \"Set {$hint} budget to 1000\".",
                    ]);
                    return;
                }

                if ($resolved['status'] === 'multiple') {
                    $msg = "I found multiple matching budgets. Which one?\n";
                    foreach ($resolved['candidates'] as $idx => $b) {
                        $msg .= ($idx + 1) . ". **{$b->name}** ({$b->category}) — " . number_format($b->amount, 2) . " GHS\n";
                    }
                    AssistantMessage::create([
                        'user_id' => $userId,
                        'role' => 'assistant',
                        'content' => $msg,
                    ]);
                    return;
                }

                $budget = $resolved['match'];
                $updates = [];
                if (isset($params['amount']) && (float) $params['amount'] > 0) {
                    $updates['amount'] = (float) $params['amount'];
                }
                if (!empty($params['period'])) $updates['period'] = $params['period'];
                if (!empty($params['name'])) $updates['name'] = $params['name'];
                if (!empty($params['category'])) $updates['category'] = $params['category'];

                if (empty($updates)) {
                    AssistantMessage::create([
                        'user_id' => $userId,
                        'role' => 'assistant',
                        'content' => "Found budget '{$budget->name}', but I need a new amount or period to update.",
                    ]);
                    return;
                }

                $budget->update($updates);
                AssistantMessage::create([
                    'user_id' => $userId,
                    'role' => 'assistant',
                    'content' => "Updated budget **{$budget->name}**: " . number_format($budget->amount, 2) . " GHS ({$budget->period}).",
                ]);
                break;

            case 'delete':
                $hint = $params['name'] ?? $params['category'] ?? null;
                $resolved = $this->resolver->resolveBudget($userId, $hint, $params['budget_id'] ?? null);

                if ($resolved['status'] === 'none') {
                    AssistantMessage::create([
                        'user_id' => $userId,
                        'role' => 'assistant',
                        'content' => "I couldn't find a budget matching \"" . ($hint ?: 'that') . "\". Try \"Show my budgets\".",
                    ]);
                    return;
                }

                if ($resolved['status'] === 'multiple') {
                    $msg = "I found multiple matching budgets. Which should I delete?\n";
                    foreach ($resolved['candidates'] as $idx => $b) {
                        $msg .= ($idx + 1) . ". **{$b->name}** ({$b->category})\n";
                    }
                    AssistantMessage::create([
                        'user_id' => $userId,
                        'role' => 'assistant',
                        'content' => $msg,
                    ]);
                    return;
                }

                $budget = $resolved['match'];
                AssistantMessage::create([
                    'user_id' => $userId,
                    'role' => 'assistant',
                    'content' => "Are you sure you want to delete budget **{$budget->name}** ({$budget->category})?",
                    'metadata' => [
                        'type' => 'confirm_delete_budget',
                        'budget_id' => $budget->id,
                        'budget_name' => $budget->name,
                    ],
                ]);
                break;

            default:
                $this->listBudgets($userId, $params);
                break;
        }
    }

    protected function listBudgets(int $userId, array $params = []): void
    {
        $query = Budget::where('user_id', $userId);
        $hint = $params['name'] ?? $params['category'] ?? null;
        if ($hint) {
            $query->where(function ($q) use ($hint) {
                $q->where('name', 'like', "%{$hint}%")
                  ->orWhere('category', 'like', "%{$hint}%");
            });
        }

        $budgets = $query->latest()->get();

        if ($budgets->isEmpty()) {
            $msg = "You don't have any budgets set up currently. Try: \"Set marketing budget to 5000 GHS monthly\".";
        } else {
            $msg = "Here is your budget status:\n";
            foreach ($budgets as $b) {
                $spent = Transaction::where('user_id', $userId)
                    ->where('category', $b->category)
                    ->where('type', 'expense')
                    ->whereMonth('occurred_at', now()->month)
                    ->whereYear('occurred_at', now()->year)
                    ->sum('amount');

                $percent = $b->amount > 0 ? round(($spent / $b->amount) * 100) : 0;
                $status = $spent > $b->amount ? "EXCEEDED" : "Under Limit";

                $msg .= "• **{$b->name}** ({$b->category}): Spent " . number_format($spent, 2) . " GHS / Budget " . number_format($b->amount, 2) . " GHS ({$percent}% used) · *{$status}*\n";
            }
        }

        AssistantMessage::create([
            'user_id' => $userId,
            'role' => 'assistant',
            'content' => $msg,
        ]);
    }

    protected function handleRecordTransaction(int $userId, array $params): void
    {
        $transaction = Transaction::create([
            'user_id' => $userId,
            'type' => $params['type'],
            'amount' => $params['amount'],
            'currency' => $params['currency'] ?? 'GHS',
            'category' => $params['category'],
            'description' => $params['description'] ?? 'Logged via AI assistant',
            'occurred_at' => !empty($params['occurred_at']) ? Carbon::parse($params['occurred_at']) : Carbon::today(),
            'payment_method' => 'Cash',
            'exchange_rate' => 1.0,
        ]);

        $this->ledgerService->recordTransaction($transaction);

        $formattedAmount = number_format($params['amount'], 2) . ' ' . ($params['currency'] ?? 'GHS');
        AssistantMessage::create([
            'user_id' => $userId,
            'role' => 'assistant',
            'content' => "Financial record captured: Recorded " . ucfirst($params['type']) . " of {$formattedAmount} under '{$params['category']}'. The IFRS General Ledger has been updated.",
        ]);
    }

    protected function handlePublishMedia(int $userId, array $params): void
    {
        $media = MediaAsset::query()
            ->where('user_id', $userId)
            ->where('title', 'like', "%{$params['media_title']}%")
            ->first();

        if (!$media) {
            $media = MediaAsset::query()->where('user_id', $userId)->latest()->first();

            if (!$media) {
                $media = MediaAsset::create([
                    'user_id' => $userId,
                    'title' => $params['media_title'],
                    'filename' => 'placeholder.mp4',
                    'path' => 'media/placeholder.mp4',
                    'mime_type' => 'video/mp4',
                    'size' => 1024 * 1024 * 5,
                    'status' => 'ready',
                    'notes' => 'Placeholder media created for publishing queue',
                ]);
            }
        }

        $channel = DistributionChannel::where('slug', $params['channel'])->first();
        if (!$channel) {
            $channel = DistributionChannel::first();
        }

        $publishJob = PublishJob::create([
            'user_id' => $userId,
            'media_asset_id' => $media->id,
            'distribution_channel_id' => $channel->id,
            'status' => !empty($params['scheduled_at']) ? 'scheduled' : 'pending',
            'caption' => $params['caption'] ?? 'Published via AI assistant',
            'scheduled_at' => !empty($params['scheduled_at']) ? Carbon::parse($params['scheduled_at']) : null,
        ]);

        if ($publishJob->status === 'pending') {
            ProcessPublishJob::dispatch($publishJob);
        }

        $schedStr = $publishJob->scheduled_at ? " scheduled for " . $publishJob->scheduled_at->format('M j, Y \a\t h:i A') : " immediate dispatch";
        AssistantMessage::create([
            'user_id' => $userId,
            'role' => 'assistant',
            'content' => "Media publishing queued: Uploading '{$media->title}' to {$channel->name} ({$schedStr}). You can monitor the progress on the Publish Queue page.",
        ]);
    }

    protected function handleQueryFinances(int $userId, array $params): void
    {
        $queryType = $params['query_type'] ?? 'balance';

        if ($queryType === 'budget_status') {
            $this->listBudgets($userId, $params);
            return;
        }

        if ($queryType === 'list') {
            $txs = Transaction::where('user_id', $userId)->latest()->limit(5)->get();
            if ($txs->isEmpty()) {
                $msg = "No transaction records found in your ledger.";
            } else {
                $msg = "Here are your 5 most recent transactions:\n";
                foreach ($txs as $tx) {
                    $sign = $tx->type === 'income' ? '+' : '-';
                    $msg .= "• {$tx->occurred_at->format('M d')} — **{$tx->category}**: {$sign}" . number_format($tx->amount, 2) . " {$tx->currency} ({$tx->payment_method})\n";
                }
            }
        } else {
            $totalIncome = Transaction::where('user_id', $userId)->where('type', 'income')->sum('amount');
            $totalExpense = Transaction::where('user_id', $userId)->where('type', 'expense')->sum('amount');
            $balance = $totalIncome - $totalExpense;

            if ($queryType === 'total_income') {
                $msg = "Your total income recorded is **" . number_format($totalIncome, 2) . " GHS**.";
            } elseif ($queryType === 'total_expense') {
                $msg = "Your total expenses recorded sum up to **" . number_format($totalExpense, 2) . " GHS**.";
            } else {
                $msg = "Financial Ledger Summary:\n" .
                       "• Total Income: **" . number_format($totalIncome, 2) . " GHS**\n" .
                       "• Total Expenses: **" . number_format($totalExpense, 2) . " GHS**\n" .
                       "• Net Balance: **" . number_format($balance, 2) . " GHS**";
            }
        }

        AssistantMessage::create([
            'user_id' => $userId,
            'role' => 'assistant',
            'content' => $msg,
        ]);
    }

    protected function handleQueryQueue(int $userId, array $params): void
    {
        $status = $params['status'] ?? 'all';
        $query = PublishJob::where('user_id', $userId);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $jobs = $query->latest()->limit(5)->get();

        if ($jobs->isEmpty()) {
            $msg = "There are no " . ($status !== 'all' ? $status . " " : "") . "publishing jobs currently in your queue.";
        } else {
            $msg = "Here are your recent publication jobs:\n";
            foreach ($jobs as $job) {
                $mediaTitle = $job->mediaAsset?->title ?? 'Untitled Media';
                $channelName = $job->distributionChannel?->name ?? 'Unknown Channel';
                $statusBadge = strtoupper($job->status);

                $dateStr = '';
                if ($job->status === 'published' && $job->published_at) {
                    $dateStr = " (live " . $job->published_at->diffForHumans() . ")";
                } elseif ($job->status === 'scheduled' && $job->scheduled_at) {
                    $dateStr = " (scheduled for " . $job->scheduled_at->format('M j, g:i A') . ")";
                }

                $msg .= "• **{$mediaTitle}** to **{$channelName}** — Status: {$statusBadge}{$dateStr}\n";
                if ($job->published_url) {
                    $msg .= "  *Link: [View Media]({$job->published_url})*\n";
                }
            }
        }

        AssistantMessage::create([
            'user_id' => $userId,
            'role' => 'assistant',
            'content' => $msg,
        ]);
    }

    /**
     * Resolve task conflicts from chat buttons.
     */
    public function resolveConflict(Request $request, AssistantMessage $message): RedirectResponse
    {
        $userId = auth()->id();
        abort_unless($message->user_id === $userId, 403);
        abort_unless(isset($message->metadata['type']) && $message->metadata['type'] === 'task_conflict', 400);

        $action = $request->input('action');
        $params = $message->metadata['task_params'];
        $alternativeDueAt = $message->metadata['alternative_due_at'] ?? null;

        $message->update(['metadata' => null]);

        if ($action === 'reschedule') {
            $newDue = Carbon::parse($alternativeDueAt);
            Task::create([
                'user_id' => $userId,
                'title' => $params['title'],
                'description' => $params['description'] ?? null,
                'priority' => $params['priority'] ?? 'medium',
                'status' => 'pending',
                'due_at' => $newDue,
            ]);

            AssistantMessage::create([
                'user_id' => $userId,
                'role' => 'user',
                'content' => 'Reschedule to proposed slot.',
            ]);

            AssistantMessage::create([
                'user_id' => $userId,
                'role' => 'assistant',
                'content' => "Rescheduled: '{$params['title']}' has been scheduled for " . $newDue->format('M j, Y \a\t h:i A') . ".",
            ]);
        } elseif ($action === 'confirm') {
            $dueAt = !empty($params['due_at']) ? Carbon::parse($params['due_at']) : null;
            Task::create([
                'user_id' => $userId,
                'title' => $params['title'],
                'description' => $params['description'] ?? null,
                'priority' => $params['priority'] ?? 'medium',
                'status' => 'pending',
                'due_at' => $dueAt,
            ]);

            AssistantMessage::create([
                'user_id' => $userId,
                'role' => 'user',
                'content' => 'Force schedule anyway.',
            ]);

            $timeStr = $dueAt ? " for " . $dueAt->format('M j, Y \a\t h:i A') : "";
            AssistantMessage::create([
                'user_id' => $userId,
                'role' => 'assistant',
                'content' => "Force scheduled: '{$params['title']}'{$timeStr} despite the conflict.",
            ]);
        } else {
            AssistantMessage::create([
                'user_id' => $userId,
                'role' => 'user',
                'content' => 'Cancel task.',
            ]);

            AssistantMessage::create([
                'user_id' => $userId,
                'role' => 'assistant',
                'content' => "Cancelled: Task '{$params['title']}' was not scheduled.",
            ]);
        }

        return redirect()->route('assistant.chat');
    }

    /**
     * Confirm or cancel pending destructive actions (task/budget delete).
     */
    public function resolveAction(Request $request, AssistantMessage $message): RedirectResponse
    {
        $userId = auth()->id();
        abort_unless($message->user_id === $userId, 403);

        $type = $message->metadata['type'] ?? null;
        abort_unless(in_array($type, ['confirm_delete_task', 'confirm_delete_budget'], true), 400);

        $action = $request->input('action');
        $meta = $message->metadata;
        $message->update(['metadata' => null]);

        if ($action !== 'confirm') {
            AssistantMessage::create([
                'user_id' => $userId,
                'role' => 'user',
                'content' => 'Cancel deletion.',
            ]);
            AssistantMessage::create([
                'user_id' => $userId,
                'role' => 'assistant',
                'content' => 'Deletion cancelled. Nothing was removed.',
            ]);
            return redirect()->route('assistant.chat');
        }

        if ($type === 'confirm_delete_task') {
            $task = Task::where('user_id', $userId)->where('id', $meta['task_id'])->first();
            $title = $meta['task_title'] ?? 'task';

            AssistantMessage::create([
                'user_id' => $userId,
                'role' => 'user',
                'content' => 'Confirm delete task.',
            ]);

            if ($task) {
                $task->delete();
                AssistantMessage::create([
                    'user_id' => $userId,
                    'role' => 'assistant',
                    'content' => "Deleted task '{$title}'.",
                ]);
            } else {
                AssistantMessage::create([
                    'user_id' => $userId,
                    'role' => 'assistant',
                    'content' => "That task was already removed.",
                ]);
            }
        }

        if ($type === 'confirm_delete_budget') {
            $budget = Budget::where('user_id', $userId)->where('id', $meta['budget_id'])->first();
            $name = $meta['budget_name'] ?? 'budget';

            AssistantMessage::create([
                'user_id' => $userId,
                'role' => 'user',
                'content' => 'Confirm delete budget.',
            ]);

            if ($budget) {
                $budget->delete();
                AssistantMessage::create([
                    'user_id' => $userId,
                    'role' => 'assistant',
                    'content' => "Deleted budget '{$name}'.",
                ]);
            } else {
                AssistantMessage::create([
                    'user_id' => $userId,
                    'role' => 'assistant',
                    'content' => "That budget was already removed.",
                ]);
            }
        }

        return redirect()->route('assistant.chat');
    }

    public function clearChat(): RedirectResponse
    {
        AssistantMessage::query()->where('user_id', auth()->id())->delete();
        return redirect()->route('assistant.chat')->with('status', 'Chat history cleared.');
    }
}
