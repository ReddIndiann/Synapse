<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use App\Models\AssistantMessage;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\MediaAsset;
use App\Models\PublishJob;
use App\Models\DistributionChannel;
use App\Services\AiAssistantService;
use App\Services\AccountingLedgerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class AssistantController extends Controller
{
    protected $aiService;
    protected $ledgerService;

    public function __construct(AiAssistantService $aiService, AccountingLedgerService $ledgerService)
    {
        $this->aiService = $aiService;
        $this->ledgerService = $ledgerService;
    }

    public function index(): View
    {
        $userId = auth()->id();

        // Get chat history
        $messages = AssistantMessage::query()
            ->where('user_id', $userId)
            ->oldest()
            ->get();

        // If no messages, seed initial welcome message
        if ($messages->isEmpty()) {
            $welcome = AssistantMessage::create([
                'user_id' => $userId,
                'role' => 'assistant',
                'content' => 'Hi! I am Synapse AI, your business management assistant. Ask me to schedule tasks, record transactions, or queue content. Try saying:
• "Schedule client call tomorrow at 10 AM, high priority"
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

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'prompt' => ['required', 'string', 'max:2000'],
        ]);

        $userId = auth()->id();
        $prompt = $validated['prompt'];

        // 1. Save user message
        AssistantMessage::create([
            'user_id' => $userId,
            'role' => 'user',
            'content' => $prompt,
        ]);

        // 2. Parse query using AI
        $parsed = $this->aiService->parse($prompt, $userId);

        $intent = $parsed['intent'] ?? 'unknown';
        $params = $parsed['parameters'] ?? [];

        // 3. Process Intent
        switch ($intent) {
            case 'schedule_task':
                $dueAt = $params['due_at'] ? Carbon::parse($params['due_at']) : null;

                if ($parsed['conflict']) {
                    // Conflict detected: save details in metadata and warn the user
                    $conflictingTaskNames = collect($parsed['conflicting_tasks'])->map(function($t) {
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
                } else {
                    // Schedule directly
                    $task = Task::create([
                        'user_id' => $userId,
                        'title' => $params['title'],
                        'description' => $params['description'],
                        'priority' => $params['priority'] ?? 'medium',
                        'status' => 'pending',
                        'due_at' => $dueAt,
                    ]);

                    $timeStr = $dueAt ? " for " . $dueAt->format('M j, Y \a\t h:i A') : "";
                    AssistantMessage::create([
                        'user_id' => $userId,
                        'role' => 'assistant',
                        'content' => "Task successfully scheduled: '{$params['title']}'{$timeStr} (Priority: " . ucfirst($params['priority']) . ").",
                    ]);
                }
                break;

            case 'record_transaction':
                // For double entry ledger, map Payment Method to Cash and record
                $transaction = Transaction::create([
                    'user_id' => $userId,
                    'type' => $params['type'],
                    'amount' => $params['amount'],
                    'currency' => $params['currency'] ?? 'GHS',
                    'category' => $params['category'],
                    'description' => $params['description'] ?? 'Logged via AI assistant',
                    'occurred_at' => $params['occurred_at'] ? Carbon::parse($params['occurred_at']) : Carbon::today(),
                    'payment_method' => 'Cash', // Default Asset Account
                    'exchange_rate' => 1.0,
                ]);

                // Sync with ledger
                $this->ledgerService->recordTransaction($transaction);

                $formattedAmount = number_format($params['amount'], 2) . ' ' . ($params['currency'] ?? 'GHS');
                AssistantMessage::create([
                    'user_id' => $userId,
                    'role' => 'assistant',
                    'content' => "Financial record captured: Recorded " . ucfirst($params['type']) . " of {$formattedAmount} under '{$params['category']}'. The IFRS General Ledger has been updated.",
                ]);
                break;

            case 'publish_media':
                // Find or create media asset match
                $media = MediaAsset::query()
                    ->where('user_id', $userId)
                    ->where('title', 'like', "%{$params['media_title']}%")
                    ->first();

                if (!$media) {
                    // If not found, look for most recent or create a placeholder asset
                    $media = MediaAsset::query()->where('user_id', $userId)->latest()->first();

                    if (!$media) {
                        $media = MediaAsset::create([
                            'user_id' => $userId,
                            'title' => $params['media_title'],
                            'filename' => 'placeholder.mp4',
                            'path' => 'media/placeholder.mp4',
                            'mime_type' => 'video/mp4',
                            'size' => 1024 * 1024 * 5, // 5MB
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
                    'status' => $params['scheduled_at'] ? 'scheduled' : 'pending',
                    'caption' => $params['caption'] ?? 'Published via AI assistant',
                    'scheduled_at' => $params['scheduled_at'] ? Carbon::parse($params['scheduled_at']) : null,
                ]);

                // Dispatch simulation queue job
                if ($publishJob->status === 'pending') {
                    \App\Jobs\ProcessPublishJob::dispatch($publishJob);
                }

                $schedStr = $publishJob->scheduled_at ? " scheduled for " . $publishJob->scheduled_at->format('M j, Y \a\t h:i A') : " immediate dispatch";
                AssistantMessage::create([
                    'user_id' => $userId,
                    'role' => 'assistant',
                    'content' => "Media publishing queued: Uploading '{$media->title}' to {$channel->name} ({$schedStr}). You can monitor the progress on the Publish Queue page.",
                ]);
                break;

            default:
                AssistantMessage::create([
                    'user_id' => $userId,
                    'role' => 'assistant',
                    'content' => "I parsed your query but couldn't resolve a specific action. You can try:\n" .
                                 "• 'Schedule project review tomorrow at 2 PM'\n" .
                                 "• 'Log expense of 50 GHS for server hosting'\n" .
                                 "• 'Queue YouTube publish job for new podcast episode'",
                ]);
                break;
        }

        return redirect()->route('assistant.chat');
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

        // Clean up action metadata
        $message->update(['metadata' => null]);

        if ($action === 'reschedule') {
            $newDue = Carbon::parse($params['alternative_due_at'] ?? $message->metadata['alternative_due_at']);
            Task::create([
                'user_id' => $userId,
                'title' => $params['title'],
                'description' => $params['description'],
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
            $dueAt = $params['due_at'] ? Carbon::parse($params['due_at']) : null;
            Task::create([
                'user_id' => $userId,
                'title' => $params['title'],
                'description' => $params['description'],
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
     * Clear Chat History.
     */
    public function clearChat(): RedirectResponse
    {
        AssistantMessage::query()->where('user_id', auth()->id())->delete();
        return redirect()->route('assistant.chat')->with('status', 'Chat history cleared.');
    }
}
