<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssistantController extends Controller
{
    public function index(): View
    {
        return view('assistant.chat', [
            'recentTasks' => auth()->user()->tasks()->latest()->limit(5)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'prompt' => ['required', 'string', 'max:2000'],
        ]);

        Task::create([
            'user_id' => auth()->id(),
            'title' => \Illuminate\Support\Str::limit($validated['prompt'], 120),
            'description' => $validated['prompt'],
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        return redirect()->route('assistant.chat')->with('status', 'Request captured. AI processing will be connected in a later phase.');
    }
}
