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
        'company',
        'notifications_read_at'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'notifications_read_at' => 'datetime',
        'dark_mode' => 'boolean'
    ];

    public const ROLES = [
        'user',
        'closer',
        'super_agent',
        'admin',
        'report_manager',
        'lead_manager',
        'max_out',
        'death_submitted'
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

    public function isReportManager(): bool
    {
        return $this->role === 'report_manager';
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

    /**
     * Mark all notifications as read by updating the notifications_read_at timestamp
     */
    public function markAllNotificationsAsRead(): void
    {
        $this->update(['notifications_read_at' => now()]);
    }

    /**
     * Get the timestamp when all notifications were last marked as read
     */
    public function getNotificationsReadAt(): ?\Illuminate\Support\Carbon
    {
        return $this->notifications_read_at;
    }

    /**
     * Get human-readable label for the user's role
     */
    public function getRoleLabelAttribute(): string
    {
        $map = [
            'user'            => 'Standard Agent',
            'closer'          => 'Closer',
            'super_agent'     => 'Super agent',
            'report_manager'  => 'Report Manager',
            'lead_manager'    => 'TL Manager',
            'max_out'         => 'VM Protocol',
            'death_submitted' => 'verification manager',
            'admin'           => 'System Administrator',
        ];

        return $map[$this->role] ?? str_replace('_', ' ', $this->role);
    }
}
