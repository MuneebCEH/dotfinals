<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        // anyone logged in can view list (or restrict if needed)
        return true;
    }

    // public function view(User $user, Lead $lead): bool
    // {
    //     // admins OR assigned users can view
    //     return $user->isAdmin() || $lead->assignee()->where('user_id', $user->id)->exists();
    // }

    public function view(User $user, Lead $lead): bool
    {
        return $user->isAdmin() || $lead->assignee_id === $user->id;
    }

    public function update(User $user, Lead $lead): bool
    {
        return $user->isAdmin() || $lead->assignee_id === $user->id;
    }


    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    // public function update(User $user, Lead $lead): bool
    // {
    //     // admin or assigned
    //     return $user->isAdmin() || $lead->assignee()->where('user_id', $user->id)->exists();
    // }

    public function delete(User $user, Lead $lead): bool
    {
        return $user->isAdmin();
    }
}