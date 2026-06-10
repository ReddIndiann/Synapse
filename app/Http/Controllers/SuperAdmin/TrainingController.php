<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AssistantMessage;
use App\Services\LocalAiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class TrainingController extends Controller
{
    public function index(LocalAiService $localAi): View
    {
        $stats = [
            'total_messages' => AssistantMessage::count(),
            'user_messages' => AssistantMessage::where('role', 'user')->count(),
            'assistant_messages' => AssistantMessage::where('role', 'assistant')->count(),
            'unique_users' => AssistantMessage::distinct('user_id')->count('user_id'),
        ];

        $trainingFile = storage_path('app/training/synapse-training.jsonl');
        $trainingFileExists = file_exists($trainingFile);
        $trainingFileSize = $trainingFileExists ? filesize($trainingFile) : 0;

        $localModelAvailable = $localAi->isAvailable();
        $localEndpoint = config('ai.local.endpoint');
        $localModel = config('ai.local.model');

        return view('superadmin.training.index', compact(
            'stats', 'trainingFileExists', 'trainingFileSize',
            'localModelAvailable', 'localEndpoint', 'localModel'
        ));
    }

    public function export(): RedirectResponse
    {
        $exitCode = Artisan::call('ai:export-training-data', ['--include-examples' => true]);

        $message = $exitCode === 0
            ? 'Training data exported successfully.'
            : 'Export failed. Check logs for details.';

        return redirect()->route('superadmin.training.index')->with('status', $message);
    }
}
