<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
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
            'occurred_at' => 'date',
            'exchange_rate' => 'decimal:6',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function types(): array
    {
        return ['income', 'expense'];
    }
}
