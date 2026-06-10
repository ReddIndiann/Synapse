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
        'media_asset_id',
        'distribution_channel_id',
        'status',
        'caption',
        'scheduled_at',
        'published_at',
        'published_url',
        'logs',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'published_at' => 'datetime',
            'logs' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class);
    }

    public function distributionChannel(): BelongsTo
    {
        return $this->belongsTo(DistributionChannel::class);
    }

    public static function statuses(): array
    {
        return ['pending', 'scheduled', 'published', 'failed'];
    }
}
