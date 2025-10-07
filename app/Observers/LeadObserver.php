<?php

namespace App\Observers;

use App\Models\Lead;
use App\Models\LeadStatusTransition;
use Illuminate\Support\Facades\Auth;

class LeadObserver
{
    /**
     * Persist a status transition whenever the lead status changes.
     */
    public function updated(Lead $lead): void
    {
        if (!$lead->wasChanged('status')) {
            return;
        }

        $fromStatus = (string) $lead->getOriginal('status');
        $toStatus = (string) $lead->status;

        if ($fromStatus === '' || $fromStatus === $toStatus) {
            return;
        }

        LeadStatusTransition::create([
            'lead_id'     => $lead->id,
            'from_status' => $fromStatus,
            'to_status'   => $toStatus,
            'changed_by'  => Auth::id(),
        ]);
    }
}
