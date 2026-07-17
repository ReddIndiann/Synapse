<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublishCostRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'distribution_channel_id',
        'default_cost',
        'currency',
        'auto_record',
    ];

    protected function casts(): array
    {
        return [
            'default_cost' => 'decimal:2',
            'auto_record' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function distributionChannel(): BelongsTo
    {
        return $this->belongsTo(DistributionChannel::class);
    }
}
