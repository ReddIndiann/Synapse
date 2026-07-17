<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\Ai\AiProviderManager;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ApiController extends Controller
{
    public function index(AiProviderManager $manager): View
    {
        $providers = $manager->allProviderStatuses();
        $activeProvider = $manager->activeProviderName();
        $fallbackProviders = config('ai.fallback_providers', []);

        return view('superadmin.apis.index', compact('providers', 'activeProvider', 'fallbackProviders'));
    }

    public function test(string $provider, AiProviderManager $manager): JsonResponse
    {
        $result = $manager->test($provider);

        return response()->json($result);
    }
}
