<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

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

        // Parse "since" query param; fallback to 24h ago if missing/invalid
        $since = $request->query('since');
        try {
            $sinceAt = $since ? Carbon::parse($since) : now()->subDay();
        } catch (\Throwable $e) {
            $sinceAt = now()->subDay();
        }

        // Determine the assignee column name used by the schema
        $assigneeColumn = Schema::hasColumn('leads', 'assigned_to')
            ? 'assigned_to'
            : (Schema::hasColumn('leads', 'assigned_id') ? 'assigned_id' : 'assigned_to');

        // Build a strict query limited to the current user's assigned leads
        $items = Lead::query()
            ->where($assigneeColumn, $user->id)
            ->where('updated_at', '>', $sinceAt)
            ->orderBy('updated_at', 'asc')
            ->limit(50)
            ->get([
                'id',
                'first_name',
                'surname',
                'status',
                // Return a consistent key in the payload for the client
                $assigneeColumn . ' as assigned_to',
                'updated_at',
            ]);

        // Map the response for the client dropdown / UI
        $payload = [
            'since'  => $sinceAt->toIso8601String(),
            'count'  => $items->count(),
            'items'  => $items->map(function ($lead) {
                return [
                    'id'           => $lead->id,
                    'first_name'   => $lead->first_name ?? null,
                    'surname'      => $lead->surname ?? null,
                    'status'       => $lead->status ?? null,
                    'assigned_to'  => $lead->assigned_to, // normalized alias above
                    'updated_at'   => optional($lead->updated_at)->toIso8601String(),
                ];
            })->all(),
        ];

        return response()->json($payload);
    }
}
