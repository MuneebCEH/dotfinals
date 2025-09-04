<?php

namespace App\Policies;

use App\Models\Callback;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CallbackPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        // Both admin and regular users can view callbacks
        return true;
    }

    public function view(User $user, Callback $callback)
    {
        return $callback->user_id === $user->id || $user->role === 'admin';
    }

    public function create(User $user)
    {
        return in_array($user->role, ['admin', 'agent']);
    }

    public function update(User $user, Callback $callback)
    {
        return $callback->user_id === $user->id || $user->role === 'admin';
    }

    public function delete(User $user, Callback $callback)
    {
        return $callback->user_id === $user->id || $user->role === 'admin';
    }
}
