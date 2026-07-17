<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PublishCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'media_asset_id',
        'caption',
        'scheduled_at',
        'status',
        'record_cost',
        'estimated_cost_per_channel',
        'currency',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'record_cost' => 'boolean',
            'estimated_cost_per_channel' => 'decimal:2',
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

    public function publishJobs(): HasMany
    {
        return $this->hasMany(PublishJob::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public static function statuses(): array
    {
        return ['draft', 'queued', 'partial', 'completed', 'failed'];
    }

    public function refreshStatus(): void
    {
        $jobs = $this->publishJobs()->get();
        if ($jobs->isEmpty()) {
            return;
        }

        $published = $jobs->where('status', 'published')->count();
        $failed = $jobs->where('status', 'failed')->count();
        $total = $jobs->count();

        if ($published === $total) {
            $status = 'completed';
        } elseif ($failed === $total) {
            $status = 'failed';
        } elseif ($published > 0 || $failed > 0) {
            $status = 'partial';
        } else {
            $status = 'queued';
        }

        if ($this->status !== $status) {
            $this->update(['status' => $status]);
        }
    }
}
