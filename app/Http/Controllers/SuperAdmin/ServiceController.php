<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PublishJob;
use App\Services\LocalAiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        $queue = [
            'pending' => DB::table('jobs')->count(),
            'failed' => DB::table('failed_jobs')->count(),
            'driver' => config('queue.default'),
        ];

        $cache = [
            'driver' => config('cache.default'),
            'store' => get_class(Cache::store()),
        ];

        $database = [
            'connection' => config('database.default'),
            'driver' => DB::connection()->getDriverName(),
            'name' => DB::connection()->getDatabaseName(),
        ];

        $storage = [
            'disk' => config('filesystems.default'),
            'public_files' => count(Storage::disk('public')->allFiles()),
        ];

        $publishJobs = PublishJob::query()
            ->with('user')
            ->latest()
            ->limit(10)
            ->get();

        $migrations = DB::table('migrations')->count();
        $migrationBatch = DB::table('migrations')->max('batch');

        return view('superadmin.services.index', compact('queue', 'cache', 'database', 'storage', 'publishJobs', 'migrations', 'migrationBatch'));
    }

    public function clearCache(): RedirectResponse
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');

        return redirect()->route('superadmin.services.index')->with('status', 'All caches cleared successfully.');
    }

    public function optimize(): RedirectResponse
    {
        Artisan::call('optimize');

        return redirect()->route('superadmin.services.index')->with('status', 'Application optimized successfully.');
    }

    public function retryFailedJobs(): RedirectResponse
    {
        Artisan::call('queue:retry', ['--all' => true]);

        return redirect()->route('superadmin.services.index')->with('status', 'Retrying all failed jobs.');
    }

    public function purgeFailedJobs(): RedirectResponse
    {
        DB::table('failed_jobs')->truncate();

        return redirect()->route('superadmin.services.index')->with('status', 'Failed jobs table purged.');
    }
}
