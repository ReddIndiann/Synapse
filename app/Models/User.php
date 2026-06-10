<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = ['name', 'first_name', 'last_name', 'phone', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    public function tasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function budgets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function mediaAssets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MediaAsset::class);
    }

    public function publishJobs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PublishJob::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
