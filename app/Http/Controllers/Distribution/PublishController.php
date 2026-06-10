<?php

namespace App\Http\Controllers\Distribution;

use App\Http\Controllers\Controller;
use App\Http\Requests\Distribution\PublishJobRequest;
use App\Models\DistributionChannel;
use App\Models\MediaAsset;
use App\Models\PublishJob;
use App\Jobs\ProcessPublishJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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

    public function store(PublishJobRequest $request): RedirectResponse
    {
        $asset = MediaAsset::query()->findOrFail($request->validated('media_asset_id'));
        $this->authorize('view', $asset);

        $status = !empty($request->validated('scheduled_at')) ? 'scheduled' : 'pending';

        $job = PublishJob::create([
            ...$request->validated(),
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
        $this->authorize('view', $publish);
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
        $this->authorize('view', $publish);
        
        return response()->json([
            'status' => $publish->status,
            'logs' => $publish->logs ?: [],
            'published_url' => $publish->published_url,
            'progress' => $this->calculateProgress($publish->status, $publish->logs),
        ]);
    }

    public function destroy(PublishJob $publish): RedirectResponse
    {
        $this->authorize('delete', $publish);
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
