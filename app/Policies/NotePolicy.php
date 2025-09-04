<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        // Both admin and regular users can view notes
        return true;
    }

    public function view(User $user, Note $note)
    {
        // Users can view notes if they're related to their leads or if they're an admin
        return $user->isAdmin() || $note->lead->users->contains($user);
    }

    public function create(User $user)
    {
        // Both admin and regular users can create notes
        return true;
    }

    public function delete(User $user, Note $note)
    {
        // Users can delete their own notes or if they're an admin
        return $user->isAdmin() || $note->user_id === $user->id;
    }
}
