<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;

class LeadRealtimeController extends Controller
{
    protected function isAdminUser(User $u): bool
    {
        return (method_exists($u, 'isAdmin') && $u->isAdmin())
            || (method_exists($u, 'hasRole') && $u->hasRole('admin'))
            || (($u->role ?? null) === 'admin');
    }

    protected function isElevated(User $u): bool
    {
        $isLeadManager = (method_exists($u, 'isLeadManager') && $u->isLeadManager())
            || (method_exists($u, 'hasRole') && $u->hasRole('lead_manager'))
            || (($u->role ?? null) === 'lead_manager');

        $isSuperAgent = (method_exists($u, 'isSuperAgent') && $u->isSuperAgent())
            || (method_exists($u, 'hasRole') && $u->hasRole('super_agent'))
            || (($u->role ?? null) === 'super_agent');

        $isCloser = (method_exists($u, 'isCloser') && $u->isCloser())
            || (method_exists($u, 'hasRole') && $u->hasRole('closer'))
            || (($u->role ?? null) === 'closer');

        return $isLeadManager || $isSuperAgent || $isCloser;
    }

    public function since(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 403);

        // Hide notifications for admins altogether
        if ($this->isAdminUser($user)) {
            return response()->json([
                'since'        => now()->toIso8601String(),
                'count'        => 0,
                'unread_count' => 0,
                'items'        => [],
            ]);
        }

        $sinceAt = $request->query('since')
            ? now()->parse($request->query('since'))
            : now()->subSeconds(120);

        $lastSeen = $request->query('last_seen') ? now()->parse($request->query('last_seen')) : null;

        // Get the user's notifications_read_at timestamp to filter out read notifications
        // This should only be used when explicitly marking all as read
        $notificationsReadAt = $user->getNotificationsReadAt();

        $q = Lead::query()
            ->when(!$this->isElevated($user), fn($q) => $q->where('assigned_to', $user->id))
            ->where('updated_at', '>', $sinceAt)
            ->orderBy('updated_at', 'asc')
            ->limit(50)
            ->select(['id', 'first_name', 'surname', 'status', 'assigned_to', 'updated_at']);

        $items = $q->get()->map(function ($lead) {
            return [
                'id'          => $lead->id,
                'name'        => trim(($lead->first_name ?? '') . ' ' . ($lead->surname ?? '')),
                'status'      => $lead->status,
                'assigned_to' => (int) $lead->assigned_to,
                'updated_at'  => optional($lead->updated_at)->toIso8601String(),
                'url'         => route('leads.show', $lead->id),
            ];
        });

        // NOTE: Do not filter items by notifications_read_at so read items still render in the dropdown.

        $unread = 0;
        if ($lastSeen) {
            $unread = $items->filter(function ($it) use ($lastSeen) {
                return $it['updated_at'] && now()->parse($it['updated_at'])->gt($lastSeen);
            })->count();
        } else {
            $unread = $items->count();
        }

        return response()->json([
            'since'        => now()->toIso8601String(),
            'count'        => $items->count(),
            'unread_count' => $unread,
            'items'        => $items,
        ]);
    }
}
