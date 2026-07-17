<?php

namespace App\Providers;

use App\Events\PublishJobFailed;
use App\Events\PublishJobPublished;
use App\Models\Budget;
use App\Models\MediaAsset;
use App\Models\PublishCampaign;
use App\Models\PublishJob;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\UserPlatformAccount;
use App\Listeners\NotifyBatchComplete;
use App\Listeners\RecordPublishExpense;
use App\Policies\BudgetPolicy;
use App\Policies\MediaAssetPolicy;
use App\Policies\PublishCampaignPolicy;
use App\Policies\PublishJobPolicy;
use App\Policies\TaskPolicy;
use App\Policies\TransactionPolicy;
use App\Policies\UserPlatformAccountPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends AuthServiceProvider
{
    protected $policies = [
        Transaction::class => TransactionPolicy::class,
        Budget::class => BudgetPolicy::class,
        Task::class => TaskPolicy::class,
        MediaAsset::class => MediaAssetPolicy::class,
        PublishJob::class => PublishJobPolicy::class,
        PublishCampaign::class => PublishCampaignPolicy::class,
        UserPlatformAccount::class => UserPlatformAccountPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Event::listen(PublishJobPublished::class, RecordPublishExpense::class);
        Event::listen(PublishJobPublished::class, [NotifyBatchComplete::class, 'handle']);
        Event::listen(PublishJobFailed::class, [NotifyBatchComplete::class, 'handle']);
    }
}
