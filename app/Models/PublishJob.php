<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublishJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'publish_campaign_id',
        'media_asset_id',
        'distribution_channel_id',
        'user_platform_account_id',
        'status',
        'caption',
        'platform_options',
        'scheduled_at',
        'published_at',
        'published_url',
        'external_post_id',
        'logs',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'published_at' => 'datetime',
            'logs' => 'array',
            'platform_options' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function publishCampaign(): BelongsTo
    {
        return $this->belongsTo(PublishCampaign::class);
    }

    public function mediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class);
    }

    public function distributionChannel(): BelongsTo
    {
        return $this->belongsTo(DistributionChannel::class);
    }

    public function userPlatformAccount(): BelongsTo
    {
        return $this->belongsTo(UserPlatformAccount::class);
    }

    public static function statuses(): array
    {
        return ['pending', 'scheduled', 'processing', 'published', 'failed'];
    }
}
