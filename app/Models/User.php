<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory; // ← add this
    use Notifiable;
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'company'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'dark_mode' => 'boolean'
    ];

    public const ROLES = [
        'user',
        'closer',
        'super_agent',
        'admin',
        'report_manager',
        'lead_manager'
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isLeadManager(): bool
    {
        return $this->role === 'lead_manager';
    }

    public function isCloser(): bool
    {
        return $this->role === 'closer';
    }

    public function isSuperAgent(): bool
    {
        return $this->role === 'super_agent';
    }

    /** Regular agents (TO list): regular users */
    public function scopeAgents(Builder $q): Builder
    {
        return $q->where('role', 'user');
    }

    /** Super agents list */
    public function scopeSuperAgents(Builder $q): Builder
    {
        return $q->where('role', 'super_agent');
    }

    public function leads()
    {
        return $this->belongsToMany(Lead::class, 'lead_user', 'user_id', 'lead_id')
            ->withTimestamps();
    }
}
