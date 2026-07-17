<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserPlatformAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'distribution_channel_id',
        'external_account_id',
        'account_name',
        'account_handle',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'scopes',
        'metadata',
        'is_active',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'token_expires_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'scopes' => 'array',
            'metadata' => 'array',
            'is_active' => 'boolean',
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

    public function publishJobs(): HasMany
    {
        return $this->hasMany(PublishJob::class);
    }

    public function isTokenExpired(): bool
    {
        if (!$this->token_expires_at) {
            return false;
        }

        return $this->token_expires_at->isPast();
    }

    public function tokenStatusLabel(): string
    {
        if (!$this->token_expires_at) {
            return 'Active';
        }

        return $this->isTokenExpired() ? 'Expired' : 'Active';
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    public function displayDetails(): array
    {
        $details = [
            ['label' => 'Account name', 'value' => $this->account_name ?: '—'],
        ];

        if ($this->account_handle) {
            $details[] = ['label' => 'Handle', 'value' => $this->account_handle];
        }

        if ($this->external_account_id) {
            $details[] = ['label' => 'Platform ID', 'value' => $this->external_account_id];
        }

        if ($this->last_synced_at) {
            $details[] = ['label' => 'Last synced', 'value' => $this->last_synced_at->format('M j, Y g:i A')];
        }

        if ($this->token_expires_at) {
            $details[] = [
                'label' => 'Token expires',
                'value' => $this->token_expires_at->format('M j, Y g:i A') . ' (' . $this->token_expires_at->diffForHumans() . ')',
            ];
        }

        $details[] = ['label' => 'Token status', 'value' => $this->tokenStatusLabel()];

        return $details;
    }
}
