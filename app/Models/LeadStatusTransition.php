<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadStatusTransition extends Model
{
    protected $fillable = [
        'lead_id',
        'from_status',
        'to_status',
        'changed_by',
    ];

    /**
     * Transition belongs to a lead.
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * User who performed the change (if tracked).
     */
    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
