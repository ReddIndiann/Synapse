<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'category',
        'amount',
        'period',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'starts_at' => 'date',
            'ends_at' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function periods(): array
    {
        return ['monthly', 'quarterly', 'yearly'];
    }
}
