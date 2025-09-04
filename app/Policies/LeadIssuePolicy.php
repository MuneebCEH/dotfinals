<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\LeadIssue;
use App\Models\User;

class LeadIssuePolicy
{
    public function create(User $user, Lead $lead): bool
    {
        return $user->role === 'admin' || $lead->assigned_to === $user->id;
    }

    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'report_manager']);
    }

    public function view(User $user, LeadIssue $issue): bool
    {
        return $this->viewAny($user) || $issue->reporter_id === $user->id;
    }

    public function update(User $user, LeadIssue $issue): bool
    {
        return in_array($user->role, ['admin', 'report_manager']);
    }
}
