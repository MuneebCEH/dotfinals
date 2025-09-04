<?php

namespace App\Providers;

use App\Models\Callback;
use App\Models\Lead;
use App\Models\LeadIssue;
use App\Models\Note;
use App\Policies\CallbackPolicy;
use App\Policies\LeadIssuePolicy;
use App\Policies\LeadPolicy;
use App\Policies\NotePolicy;
use App\Models\Attachment;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Lead::class => LeadPolicy::class,
        Note::class => NotePolicy::class,
        Callback::class => CallbackPolicy::class,
        LeadIssue::class => LeadIssuePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    // public function boot(): void
    // {
    //     $this->registerPolicies();

    //     // Define admin check
    //     Gate::define('admin', function ($user) {
    //         return $user->role === 'admin';
    //     });
    // }


    public function boot()
    {
        $this->registerPolicies();

        // Define a Gate: can the user view this attachment?
        Gate::define('view-attachment', function (User $user, Attachment $attachment) {
            return $user->id === $attachment->user_id   // user uploaded it
                || $user->id === $attachment->issue->reporter_id // or reported issue
                || $user->hasRole('admin');             // or is admin
        });
    }
}
