<?php

namespace App\Http\Controllers\Distribution;

use App\Http\Controllers\Controller;
use App\Models\DistributionChannel;
use App\Models\MediaAsset;
use App\Models\PublishJob;
use App\Jobs\ProcessPublishJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublishController extends Controller
{
    public function index(): View
    {
        $jobs = PublishJob::query()
            ->where('user_id', auth()->id())
            ->with(['mediaAsset', 'distributionChannel'])
            ->latest()
            ->paginate(10);

        return view('distribution.publish.index', compact('jobs'));
    }

    public function create(): View
    {
        return view('distribution.publish.create', [
            'assets' => MediaAsset::query()->where('user_id', auth()->id())->orderBy('title')->get(),
            'channels' => DistributionChannel::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'media_asset_id' => ['required', 'exists:media_assets,id'],
            'distribution_channel_id' => ['required', 'exists:distribution_channels,id'],
            'caption' => ['nullable', 'string', 'max:2000'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        $asset = MediaAsset::query()->findOrFail($validated['media_asset_id']);
        abort_unless($asset->user_id === auth()->id(), 403);

        $status = ! empty($validated['scheduled_at']) ? 'scheduled' : 'pending';

        $job = PublishJob::create([
            ...$validated,
            'user_id' => auth()->id(),
            'status' => $status,
        ]);

        // Dispatch background queue worker if immediate
        if ($status === 'pending') {
            ProcessPublishJob::dispatch($job);
            return redirect()->route('distribution.publish.monitor', $job)->with('status', 'Publish job started.');
        }

        return redirect()->route('distribution.publish.index')->with('status', 'Publish job scheduled.');
    }

    /**
     * Real-time monitoring UI.
     */
    public function monitor(PublishJob $publish): View
    {
        abort_unless($publish->user_id === auth()->id(), 403);
        $publish->load(['mediaAsset', 'distributionChannel']);
        
        return view('distribution.publish.monitor', [
            'job' => $publish,
        ]);
    }

    /**
     * JSON Endpoint for AJAX Polling.
     */
    public function statusJson(PublishJob $publish): JsonResponse
    {
        abort_unless($publish->user_id === auth()->id(), 403);
        
        return response()->json([
            'status' => $publish->status,
            'logs' => $publish->logs ?: [],
            'published_url' => $publish->published_url,
            'progress' => $this->calculateProgress($publish->status, $publish->logs),
        ]);
    }

    public function destroy(PublishJob $publish): RedirectResponse
    {
        abort_unless($publish->user_id === auth()->id(), 403);
        $publish->delete();

        return redirect()->route('distribution.publish.index')->with('status', 'Publish job removed.');
    }

    /**
     * Helper to compute progress bar percentage.
     */
    private function calculateProgress(string $status, ?array $logs): int
    {
        if ($status === 'published') return 100;
        if ($status === 'failed') return 100;
        if ($status === 'pending') return 10;
        
        if ($status === 'processing') {
            if (!$logs) return 20;
            $count = count($logs);
            if ($count <= 2) return 25;
            if ($count <= 4) return 50;
            if ($count <= 6) return 75;
            return 90;
        }

        return 0;
    }
}
