<?php

namespace App\Listeners;

use App\Events\PublishJobPublished;
use App\Services\PublishAccountingService;

class RecordPublishExpense
{
    public function __construct(private PublishAccountingService $accountingService) {}

    public function handle(PublishJobPublished $event): void
    {
        $this->accountingService->recordPublishExpense($event->publishJob);
    }
}
