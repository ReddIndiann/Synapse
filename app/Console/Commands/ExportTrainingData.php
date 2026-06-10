<?php

namespace App\Console\Commands;

use App\Models\AssistantMessage;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\Budget;
use App\Models\MediaAsset;
use App\Models\PublishJob;
use App\Models\DistributionChannel;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExportTrainingData extends Command
{
    protected $signature = 'ai:export-training-data
                            {--output= : Output file path (default: storage/app/training/synapse-training.jsonl)}
                            {--include-examples : Include synthetic examples alongside real chat history}';

    protected $description = 'Export chat history and patterns as JSONL for fine-tuning a local LLM';

    public function handle(): int
    {
        $path = $this->option('output') ?: storage_path('app/training/synapse-training.jsonl');
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $examples = [];

        // 1. Real chat history from assistant_messages (pair user→assistant turns)
        $this->line('Exporting real chat history...');
        $messages = AssistantMessage::query()
            ->where('role', 'user')
            ->orderBy('user_id')
            ->orderBy('created_at')
            ->get();

        foreach ($messages as $msg) {
            $nextMsg = AssistantMessage::query()
                ->where('user_id', $msg->user_id)
                ->where('created_at', '>', $msg->created_at)
                ->where('role', 'assistant')
                ->orderBy('created_at')
                ->first();

            if ($nextMsg) {
                $examples[] = $this->buildExample($msg->content, $nextMsg->content);
            }
        }

        // 2. If requested, generate synthetic examples from known task / tx patterns
        if ($this->option('include-examples')) {
            $this->line('Generating synthetic examples from database records...');
            $examples = array_merge($examples, $this->syntheticExamples());
        }

        if (empty($examples)) {
            $this->warn('No training data found. Run some conversations first or use --include-examples.');
            return Command::SUCCESS;
        }

        $written = 0;
        $handle = fopen($path, 'w');
        foreach ($examples as $ex) {
            fwrite($handle, json_encode($ex) . "\n");
            $written++;
        }
        fclose($handle);

        $this->info("Exported {$written} training examples to {$path}");

        return Command::SUCCESS;
    }

    protected function buildExample(string $prompt, string $response): array
    {
        return [
            'messages' => [
                ['role' => 'system', 'content' => $this->systemPrompt()],
                ['role' => 'user', 'content' => $prompt],
                ['role' => 'assistant', 'content' => $response],
            ],
        ];
    }

    protected function syntheticExamples(): array
    {
        $examples = [];

        // From existing tasks
        $tasks = Task::query()->limit(50)->get();
        foreach ($tasks as $task) {
            $timeStr = $task->due_at ? ' at ' . $task->due_at->format('g:i A') : '';
            $dayStr = $task->due_at ? ' on ' . $task->due_at->format('M j') : '';
            $priority = $task->priority;

            $examples[] = $this->buildExample(
                "Schedule {$task->title}{$dayStr}{$timeStr}",
                "Task '{$task->title}' has been scheduled{$dayStr}{$timeStr} (Priority: {$priority})."
            );

            $examples[] = $this->buildExample(
                "Create a {$priority} priority task for {$task->title}",
                "Task created: '{$task->title}' with {$priority} priority."
            );
        }

        // From existing transactions
        $transactions = Transaction::query()->limit(50)->get();
        foreach ($transactions as $tx) {
            $sign = $tx->type === 'expense' ? 'Spent' : 'Received';
            $examples[] = $this->buildExample(
                "{$sign} {$tx->amount} {$tx->currency} on {$tx->category}",
                "{$tx->type} of {$tx->amount} {$tx->currency} recorded under '{$tx->category}'."
            );

            $examples[] = $this->buildExample(
                "Log {$tx->type} of {$tx->amount} {$tx->currency} for {$tx->category}",
                "Transaction recorded: {$tx->type} {$tx->amount} {$tx->currency} in {$tx->category}."
            );
        }

        // Common query patterns
        $queryPatterns = [
            'Show my pending tasks' => 'query_tasks',
            'What tasks are due soon' => 'query_tasks',
            'List all my tasks' => 'query_tasks',
            'Show completed tasks' => 'query_tasks',
            'What is my account balance' => 'query_finances',
            'How much have I spent this month' => 'query_finances',
            'Show my budget status' => 'query_finances',
            'List my recent transactions' => 'query_finances',
            'What is my total income' => 'query_finances',
            'Show my publish queue' => 'query_queue',
            'Any pending publications' => 'query_queue',
            'Which task is due soonest' => 'query_tasks',
        ];

        foreach ($queryPatterns as $query => $intent) {
            $params = match ($intent) {
                'query_tasks' => '{"status": "all"}',
                'query_finances' => '{"query_type": "balance"}',
                'query_queue' => '{"status": "all"}',
                default => '{}',
            };
            $examples[] = [
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt()],
                    ['role' => 'user', 'content' => $query],
                    ['role' => 'assistant', 'content' => "{\"intent\": \"{$intent}\", \"parameters\": {$params}}"],
                ],
            ];
        }

        // Comparative / natural questions
        $comparative = [
            'Which one is sooner' => '{"intent": "query_tasks", "parameters": {"status": "all"}}',
            'Which task is due first' => '{"intent": "query_tasks", "parameters": {"status": "all"}}',
            'What do I have coming up' => '{"intent": "query_tasks", "parameters": {"status": "all"}}',
            'What is on my schedule today' => '{"intent": "query_tasks", "parameters": {"status": "all"}}',
            'How many tasks do I have' => '{"intent": "query_tasks", "parameters": {"status": "all"}}',
        ];
        foreach ($comparative as $q => $json) {
            $examples[] = [
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt()],
                    ['role' => 'user', 'content' => $q],
                    ['role' => 'assistant', 'content' => $json],
                ],
            ];
        }

        return $examples;
    }

    protected function systemPrompt(): string
    {
        return "You are the NLP engine for Synapse, a business management assistant. "
             . "Parse user input into a JSON object with intent and parameters. "
             . "Intents: schedule_task, record_transaction, publish_media, query_tasks, query_finances, query_queue, unknown.";
    }
}
