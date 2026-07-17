<?php

namespace App\Http\Controllers\Distribution;

use App\Http\Controllers\Controller;
use App\Models\DistributionChannel;
use App\Models\UserPlatformAccount;
use App\Services\Distribution\PlatformOAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlatformAccountController extends Controller
{
    public function __construct(private PlatformOAuthService $oauthService) {}

    public function index(): View
    {
        $userId = auth()->id();
        $channels = DistributionChannel::query()->where('is_active', true)->orderBy('name')->get();
        $accounts = UserPlatformAccount::query()
            ->where('user_id', $userId)
            ->with('distributionChannel')
            ->get()
            ->keyBy('distribution_channel_id');

        $channelMeta = $channels->mapWithKeys(function (DistributionChannel $channel) {
            return [$channel->id => [
                'requires_oauth' => $this->oauthService->requiresOAuth($channel),
                'is_configured' => $this->oauthService->isConfigured($channel),
            ]];
        });

        return view('distribution.platform-accounts.index', compact('channels', 'accounts', 'channelMeta'));
    }

    public function connect(DistributionChannel $channel): RedirectResponse
    {
        if (!$this->oauthService->requiresOAuth($channel)) {
            return back()->with('status', "{$channel->name} does not require OAuth connection.");
        }

        if (!$this->oauthService->isConfigured($channel)) {
            return back()->with('error', "OAuth credentials for {$channel->name} are not configured. Add them in .env.");
        }

        return redirect()->away($this->oauthService->getAuthorizationUrl($channel));
    }

    public function callback(Request $request, string $platform): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
            'state' => ['required', 'string'],
        ]);

        try {
            $this->oauthService->handleCallback($platform, $request->string('code'), $request->string('state'), auth()->id());
        } catch (\Throwable $e) {
            return redirect()->route('distribution.accounts.index')->with('error', 'Connection failed: ' . $e->getMessage());
        }

        return redirect()->route('distribution.accounts.index')->with('status', ucfirst($platform) . ' account connected.');
    }

    public function disconnect(UserPlatformAccount $account): RedirectResponse
    {
        $this->authorize('delete', $account);
        $this->oauthService->disconnect($account);

        return back()->with('status', 'Account disconnected.');
    }
}
