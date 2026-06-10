<?php

namespace App\Providers;

use App\Models\AssistantMessage;
use App\Models\Budget;
use App\Models\MediaAsset;
use App\Models\PublishJob;
use App\Models\Task;
use App\Models\Transaction;
use App\Policies\BudgetPolicy;
use App\Policies\MediaAssetPolicy;
use App\Policies\PublishJobPolicy;
use App\Policies\TaskPolicy;
use App\Policies\TransactionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends AuthServiceProvider
{
    protected $policies = [
        Transaction::class => TransactionPolicy::class,
        Budget::class => BudgetPolicy::class,
        Task::class => TaskPolicy::class,
        MediaAsset::class => MediaAssetPolicy::class,
        PublishJob::class => PublishJobPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
