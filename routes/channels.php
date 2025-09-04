<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\LeadIssue;

Broadcast::channel('report-managers', function ($user) {
    if (in_array($user->role, ['report_manager', 'admin'])) {
        // presence channel requires returning user info array
        return ['id' => $user->id, 'name' => $user->name];
    }
    return false;
});

Broadcast::channel('issues.{issueId}', function ($user, $issueId) {
    $issue = LeadIssue::find($issueId);
    return $issue && (
        in_array($user->role, ['admin', 'report_manager'])
        || $issue->reporter_id === $user->id
    );
});
