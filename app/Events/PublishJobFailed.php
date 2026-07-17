<?php

namespace App\Events;

use App\Models\PublishJob;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PublishJobFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(public PublishJob $publishJob) {}
}
