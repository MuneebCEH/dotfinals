<?php

namespace App\Models;

use App\Models\LeadStatusTransition;
use App\Models\Traits\GeneratesTextReport;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use GeneratesTextReport;
    public const STATUSES = [
        'Voice Mail',
        'Wrong Info',
        'Not Interested',
        'Deal',
        'Call Back',
        'Disconnected Number',
        'Hangup',
        'Max Out',
        'Paid Off',
        'Not Qualified (NQ)',
        'Submitted',
        'New Lead',
        'Super Lead',
        'Death Submitted'
    ];

    protected $fillable = [
        'first_name',
        'middle_initial',
        'surname',
        'gen_code',
        'street',
        'city',
        'state_abbreviation',
        'zip_code',
        'age',
        'xfc06',
        'xfc07',
        'demo7',
        'demo9',
        'fico',
        'cards_json',
        'balance',
        'credits',
        'ssn',
        'category_id',
        'status',
        'created_by',
        'assigned_to',
        'super_agent_id',
        'closer_id',
        'numbers',
        'lead_pdf_path',
        'notes',
        'assigned_time'
    ];

    protected $casts = [
        'numbers' => 'array',
        'cards_json' => 'array',
        'age' => 'integer',
        'fico' => 'integer',
        'balance' => 'float',
        'credits' => 'float',
        'ssn' => 'encrypted'
    ];

    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        $like = '%' . str_replace(' ', '%', $term) . '%';

        return $query->where(function ($q) use ($like, $term) {
            $q->where('first_name', 'like', $like)
                ->orWhere('surname', 'like', $like)
                ->orWhere('city', 'like', $like)
                ->orWhere('street', 'like', $like)
                ->orWhere('zip_code', 'like', $like)
                ->orWhere('status', 'like', $like);

            // Optional: search inside numbers (JSON array)
            // Works on MySQL 5.7+/8.0+
            $q->orWhereRaw("JSON_SEARCH(numbers, 'one', ?) IS NOT NULL", [$term])
                ->orWhereRaw("JSON_SEARCH(numbers, 'one', ?) IS NOT NULL", ['%' . $term . '%']);
        });
    }

    /** Filter by exact status (your hardcoded list). */
    public function scopeStatus($query, ?string $status)
    {
        $status = trim((string) $status);
        if ($status === '') {
            return $query;
        }
        return $query->where('status', $status);
    }

    /** Filter by category id. */
    public function scopeCategory($query, $categoryId)
    {
        if (empty($categoryId)) {
            return $query;
        }
        return $query->where('category_id', $categoryId);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function superAgent()
    {
        return $this->belongsTo(User::class, 'super_agent_id');
    }

    public function closer()
    {
        return $this->belongsTo(User::class, 'closer_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users()
    {
        // pivot table is 'lead_user' (not 'lead_users')
        return $this->belongsToMany(User::class, 'lead_user', 'lead_id', 'user_id')
            ->withTimestamps();
    }

    // Computed full name
    public function getFullNameAttribute(): string
    {
        $first = trim((string) $this->first_name);
        $last = trim((string) $this->surname);
        $name = trim($first . ' ' . $last);
        return $name !== '' ? $name : "Lead #{$this->id}";
    }

    // Primary phone from numbers JSON (first value)
    public function getPrimaryPhoneAttribute(): ?string
    {
        $nums = $this->numbers ?? [];
        if (is_array($nums) && count($nums)) {
            // pick first non-empty value
            foreach ($nums as $n) {
                if (is_string($n) && trim($n) !== '') {
                    return $n;
                }
            }
        }
        return null;
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('assigned_to', $userId)
                ->orWhere('super_agent_id', $userId)
                ->orWhere('closer_id', $userId)
                ->orWhere('created_by', $userId)
                ->orWhereHas('users', function ($q2) use ($userId) {
                    $q2->where('users.id', $userId);
                });
        });
    }

    public function issues()
    {
        return $this->hasMany(LeadIssue::class);
    }


    public function scopeVisibleTo($query, \App\Models\User $user)
    {
        // Admins & lead managers see all
        if (
            (method_exists($user, 'isAdmin') && $user->isAdmin()) ||
            (method_exists($user, 'hasRole') && $user->hasRole('lead_manager')) ||
            (($user->role ?? null) === 'lead_manager')
        ) {
            return $query;
        }

        // Super agents: only where they are the super_agent_id
        if (
            (method_exists($user, 'isSuperAgent') && $user->isSuperAgent()) ||
            (method_exists($user, 'hasRole') && $user->hasRole('super_agent')) ||
            (($user->role ?? null) === 'super_agent')
        ) {
            return $query->where('super_agent_id', $user->id);
        }

        // Closers (or normal users): only their assigned leads
        if (method_exists($user, 'isCloser') && $user->isCloser()) {
            return $query->where('assigned_to', $user->id);
        }

        // Fallback: nothing or your preferred owner column
        return $query->where('assigned_to', $user->id);
    }

    public function callbacks()
    {
        // assumes callbacks table has a `lead_id` FK to leads.id
        return $this->hasMany(Callback::class, 'lead_id');
    }

    public function statusTransitions()
    {
        return $this->hasMany(LeadStatusTransition::class);
    }

    public function lastMaxOutExit()
    {
        return $this->hasOne(LeadStatusTransition::class)
            ->where('from_status', 'Max Out')
            ->where('to_status', '!=', 'Max Out')
            ->latestOfMany();
    }

    /**
     * Determine if the lead was ever moved out of the Max Out status.
     */
    public function hasMaxOutHistory(): bool
    {
        if ($this->relationLoaded('statusTransitions')) {
            return $this->statusTransitions->contains(
                fn($transition) => strcasecmp($transition->from_status ?? '', 'Max Out') === 0
            );
        }

        return $this->statusTransitions()
            ->where('from_status', 'Max Out')
            ->exists();
    }
}
