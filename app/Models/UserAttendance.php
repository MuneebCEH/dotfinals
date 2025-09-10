<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class UserAttendance extends Model
{
    use HasFactory;

    protected $table = 'user_attendances';

    protected $fillable = [
        'user_id',
        'check_in',
        'check_out',
        'hours_worked',
        'last_heartbeat_at',
        'status', // 'in' | 'out'
        'notes',
    ];

    protected $casts = [
        'check_in'          => 'datetime',
        'check_out'         => 'datetime',
        'last_heartbeat_at' => 'datetime',
        // Note: decimal casts return strings to preserve precision
        'hours_worked'      => 'decimal:2',
    ];

    /* ========= Relationships ========= */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* ========= Accessors / Mutators ========= */

    /**
     * Return hours_worked as float while preserving decimal storage.
     */
    protected function hoursWorked(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value !== null ? (float) $value : null
        );
    }

    /* ========= Scopes ========= */

    /**
     * Scope: records for the user's "today" in a given timezone (default Asia/Karachi).
     */
    public function scopeTodayForUser($query, int $userId, string $tz = 'Asia/Karachi')
    {
        $startUtc = now($tz)->startOfDay()->setTimezone('UTC');
        $endUtc   = now($tz)->endOfDay()->setTimezone('UTC');

        return $query->where('user_id', $userId)
            ->whereBetween('check_in', [$startUtc, $endUtc]);
    }

    /**
     * Scope: open (checked-in) records.
     */
    public function scopeOpen($query)
    {
        return $query->whereNull('check_out')->where('status', 'in');
    }

    /* ========= Helpers ========= */

    public function isOpen(): bool
    {
        return $this->check_out === null && $this->status === 'in';
    }

    /**
     * Close the current attendance now, computing hours and optionally appending a note.
     */
    public function closeNow(?string $extraNote = null): void
    {
        $now = now();
        $minutes = max(0, Carbon::parse($this->check_in)->diffInMinutes($now));
        $hours = round($minutes / 60, 2);

        $this->check_out         = $now;
        $this->hours_worked      = $hours;   // matches DECIMAL(7,2) from migration suggestion
        $this->last_heartbeat_at = $now;
        $this->status            = 'out';

        if ($extraNote !== null && trim($extraNote) !== '') {
            $this->notes = $this->notes && trim($this->notes) !== ''
                ? ($this->notes . PHP_EOL . trim($extraNote))
                : trim($extraNote);
        }

        $this->save();
    }

    /**
     * Refresh heartbeat timestamp without altering state.
     */
    public function heartbeat(): void
    {
        $this->last_heartbeat_at = now();
        $this->save();
    }
}
