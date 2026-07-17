<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'publish_job_id',
        'publish_campaign_id',
        'media_asset_id',
        'type',
        'amount',
        'currency',
        'category',
        'description',
        'occurred_at',
        'reference',
        'payment_method',
        'exchange_rate',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'occurred_at' => 'datetime',
            'exchange_rate' => 'decimal:6',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function publishJob(): BelongsTo
    {
        return $this->belongsTo(PublishJob::class);
    }

    public function publishCampaign(): BelongsTo
    {
        return $this->belongsTo(PublishCampaign::class);
    }

    public function mediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class);
    }

    public static function types(): array
    {
        return ['income', 'expense'];
    }
}
