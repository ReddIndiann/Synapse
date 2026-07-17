<?php

namespace App\Events;

use App\Models\PublishJob;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PublishJobPublished
{
    use Dispatchable, SerializesModels;

    public function __construct(public PublishJob $publishJob) {}
}
