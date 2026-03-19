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
        // Admin, Lead Manager, or Report Manager can view all
        if ($user->isAdmin() || in_array($user->role, ['lead_manager', 'report_manager'])) {
            return true;
        }

        // Otherwise, the user must be involved in the lead
        return $lead->assigned_to === $user->id || 
               $lead->super_agent_id === $user->id || 
               $lead->closer_id === $user->id ||
               $lead->created_by === $user->id;
    }

    public function create(User $user): bool
    {
        // Admins, Lead Managers, Super Agents, Report Managers, and Standard Agents can create leads
        return $user->isAdmin() || in_array($user->role, ['lead_manager', 'super_agent', 'report_manager', 'user']);
    }

    public function update(User $user, Lead $lead): bool
    {
        // Standard Agents cannot update leads (as requested previously)
        if ($user->role === 'user') {
            return false;
        }

        // Admin, Lead Manager, Report Manager, or involved in the lead
        return $user->isAdmin() || 
               $user->role === 'lead_manager' ||
               $user->role === 'report_manager' ||
               $lead->assigned_to === $user->id || 
               $lead->super_agent_id === $user->id || 
               $lead->closer_id === $user->id ||
               $lead->created_by === $user->id;
    }

    public function delete(User $user, Lead $lead): bool
    {
        // Admins and Lead Managers can delete
        return $user->isAdmin() || $user->role === 'lead_manager';
    }
}
