<?php

namespace App\Http\Controllers\Distribution;

use App\Http\Controllers\Controller;
use App\Http\Requests\Distribution\PublishCampaignRequest;
use App\Models\DistributionChannel;
use App\Models\MediaAsset;
use App\Models\PublishCampaign;
use App\Models\PublishJob;
use App\Models\UserPlatformAccount;
use App\Jobs\ProcessPublishJob;
use App\Services\Distribution\PublishCampaignService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PublishController extends Controller
{
    public function __construct(private PublishCampaignService $campaignService) {}

    public function index(): View
    {
        $campaigns = PublishCampaign::query()
            ->where('user_id', auth()->id())
            ->with(['mediaAsset', 'publishJobs.distributionChannel'])
            ->latest()
            ->paginate(10);

        return view('distribution.publish.index', compact('campaigns'));
    }

    public function create(): View
    {
        $userId = auth()->id();
        $channels = DistributionChannel::query()->where('is_active', true)->orderBy('name')->get();
        $connectedAccounts = UserPlatformAccount::query()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->get()
            ->keyBy('distribution_channel_id');

        $defaultCosts = [];
        foreach ($channels as $channel) {
            $defaultCosts[$channel->id] = $this->campaignService->defaultCostForChannel($userId, $channel);
        }

        return view('distribution.publish.create', [
            'assets' => MediaAsset::query()->where('user_id', $userId)->orderBy('title')->get(),
            'channels' => $channels,
            'connectedAccounts' => $connectedAccounts,
            'defaultCosts' => $defaultCosts,
            'prefillMediaId' => request('media_asset_id'),
        ]);
    }

    public function store(PublishCampaignRequest $request): RedirectResponse
    {
        $asset = MediaAsset::query()->findOrFail($request->validated('media_asset_id'));
        $this->authorize('view', $asset);

        $campaign = $this->campaignService->createCampaign(auth()->id(), $request->validated());

        $isImmediate = empty($request->validated('scheduled_at'));

        if ($isImmediate) {
            return redirect()
                ->route('distribution.publish.campaign', $campaign)
                ->with('status', 'Multi-platform publish campaign started.');
        }

        return redirect()
            ->route('distribution.publish.index')
            ->with('status', 'Publish campaign scheduled.');
    }

    public function campaignMonitor(PublishCampaign $campaign): View
    {
        $this->authorize('view', $campaign);
        $campaign->load(['mediaAsset', 'publishJobs.distributionChannel']);

        $marketingBudget = app(\App\Services\BudgetService::class)->marketingBudgetSummary(auth()->id());

        return view('distribution.publish.campaign-monitor', [
            'campaign' => $campaign,
            'marketingBudget' => $marketingBudget,
        ]);
    }

    public function campaignStatusJson(PublishCampaign $campaign): JsonResponse
    {
        $this->authorize('view', $campaign);
        $campaign->load(['publishJobs.distributionChannel']);

        $jobs = $campaign->publishJobs->map(fn (PublishJob $job) => [
            'id' => $job->id,
            'channel' => $job->distributionChannel->name,
            'status' => $job->status,
            'published_url' => $job->published_url,
            'progress' => $this->calculateProgress($job->status, $job->logs),
            'logs' => $job->logs ?: [],
        ]);

        $published = $campaign->publishJobs->where('status', 'published')->count();
        $total = $campaign->publishJobs->count();

        return response()->json([
            'campaign_status' => $campaign->status,
            'progress_summary' => "{$published}/{$total}",
            'jobs' => $jobs,
        ]);
    }

    public function monitor(PublishJob $publish): View
    {
        $this->authorize('view', $publish);
        $publish->load(['mediaAsset', 'distributionChannel', 'publishCampaign']);

        return view('distribution.publish.monitor', ['job' => $publish]);
    }

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

    public function retry(PublishJob $publish): RedirectResponse
    {
        $this->authorize('update', $publish);

        if ($publish->status !== 'failed') {
            return back()->with('status', 'Only failed jobs can be retried.');
        }

        $publish->update(['status' => 'pending', 'error_message' => null, 'logs' => []]);
        ProcessPublishJob::dispatch($publish);

        return back()->with('status', 'Publish job queued for retry.');
    }

    public function destroy(PublishJob $publish): RedirectResponse
    {
        $this->authorize('delete', $publish);
        $publish->delete();

        return redirect()->route('distribution.publish.index')->with('status', 'Publish job removed.');
    }

    public function destroyCampaign(PublishCampaign $campaign): RedirectResponse
    {
        $this->authorize('delete', $campaign);
        $campaign->publishJobs()->delete();
        $campaign->delete();

        return redirect()->route('distribution.publish.index')->with('status', 'Campaign removed.');
    }

    private function calculateProgress(string $status, ?array $logs): int
    {
        if ($status === 'published' || $status === 'failed') {
            return 100;
        }
        if ($status === 'pending') {
            return 10;
        }
        if ($status === 'processing') {
            if (!$logs) {
                return 20;
            }
            $count = count($logs);
            return min(90, 20 + ($count * 10));
        }

        return 0;
    }
}
