<?php

namespace App\Console\Commands;

use App\Jobs\ProcessPublishJob;
use App\Models\PublishJob;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class DispatchScheduledPublishJobs extends Command
{
    protected $signature = 'publish:dispatch-scheduled';

    protected $description = 'Dispatch publish jobs whose scheduled_at time has passed';

    public function handle(): int
    {
        $jobs = PublishJob::query()
            ->where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', Carbon::now())
            ->get();

        $count = 0;

        foreach ($jobs as $job) {
            $job->update(['status' => 'pending']);
            ProcessPublishJob::dispatch($job);
            $count++;
        }

        $this->info("Dispatched {$count} scheduled publish job(s).");

        return self::SUCCESS;
    }
}
