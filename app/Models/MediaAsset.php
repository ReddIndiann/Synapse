<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MediaAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'filename',
        'path',
        'mime_type',
        'size',
        'status',
        'notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function publishJobs(): HasMany
    {
        return $this->hasMany(PublishJob::class);
    }

    public static function statuses(): array
    {
        return ['draft', 'processing', 'ready'];
    }
}
