<?php

namespace App\Http\Controllers\Distribution;

use App\Http\Controllers\Controller;
use App\Models\DistributionChannel;
use App\Models\MediaAsset;
use App\Models\PublishJob;
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

        PublishJob::create([
            ...$validated,
            'user_id' => auth()->id(),
            'status' => $status,
        ]);

        return redirect()->route('distribution.publish.index')->with('status', 'Publish job queued. Platform integration comes in Phase 4.');
    }

    public function destroy(PublishJob $publish): RedirectResponse
    {
        abort_unless($publish->user_id === auth()->id(), 403);
        $publish->delete();

        return redirect()->route('distribution.publish.index')->with('status', 'Publish job removed.');
    }
}
