<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\MediaAsset;
use App\Models\PublishJob;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\User;
use App\Services\LocalAiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(LocalAiService $localAi): View
    {
        $stats = [
            'users' => User::count(),
            'tasks' => Task::count(),
            'transactions' => Transaction::count(),
            'budgets' => Budget::count(),
            'media' => MediaAsset::count(),
            'publish_jobs' => PublishJob::count(),
        ];

        $system = [
            'queue_size' => DB::table('jobs')->count(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'db_connection' => config('database.default'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
        ];

        $ai = [
            'provider' => config('ai.provider', 'regex'),
            'gemini_configured' => !empty(config('ai.gemini.key')),
            'local_available' => $localAi->isAvailable(),
            'local_model' => config('ai.local.model'),
        ];

        $recentLogs = $this->recentLogs();

        return view('superadmin.dashboard', compact('stats', 'system', 'ai', 'recentLogs'));
    }

    protected function recentLogs(): array
    {
        $logFile = storage_path('logs/laravel.log');
        if (!file_exists($logFile)) {
            return [];
        }

        $lines = [];
        $handle = fopen($logFile, 'r');
        if ($handle) {
            $position = max(0, filesize($logFile) - 32768);
            fseek($handle, $position);
            fgets($handle);

            while (($line = fgets($handle)) !== false) {
                $lines[] = $line;
                if (count($lines) > 50) {
                    array_shift($lines);
                }
            }
            fclose($handle);
        }

        return array_reverse($lines);
    }
}
