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

        // For report managers, include issues where they are the resolver
        $isReportManager = $user->role === 'report_manager';

        // Determine the assignee column name used by the schema
        $assigneeColumn = Schema::hasColumn('leads', 'assigned_to')
            ? 'assigned_to'
            : (Schema::hasColumn('leads', 'assigned_id') ? 'assigned_id' : 'assigned_to');

        // Build query for leads and issues
        $query = Lead::query()
            ->select(
                'leads.id',
                'leads.first_name',
                'leads.surname',
                'leads.status',
                $assigneeColumn . ' as assigned_to',
                'leads.updated_at',
                'lead_issues.id as issue_id',
                'lead_issues.resolver_id',
                'lead_issues.title as issue_title',
                'lead_issues.status as issue_status',
                'lead_issues.updated_at as issue_updated_at'
            )
            ->leftJoin('lead_issues', 'leads.id', '=', 'lead_issues.lead_id')
            ->where(function($q) use ($user, $assigneeColumn, $isReportManager) {
                $q->where($assigneeColumn, $user->id);
                
                // If user is report manager, also get leads with open issues assigned to them
                if ($isReportManager) {
                    $q->orWhere(function($query) use ($user) {
                        $query->where('lead_issues.resolver_id', $user->id)
                              ->where('lead_issues.status', 'open');
                    });
                }
            })
            ->where(function($q) use ($sinceAt) {
                $q->where('leads.updated_at', '>', $sinceAt)
                  ->orWhere('lead_issues.updated_at', '>', $sinceAt);
            })
            ->orderBy('leads.updated_at', 'desc')
            ->limit(50);

        $items = $query->get();

        // Map the response for the client dropdown / UI
        $payload = [
            'since'  => $sinceAt->toIso8601String(),
            'count'  => $items->count(),
            'items'  => $items->map(function ($item) {
                $data = [
                    'id'           => $item->id,
                    'first_name'   => $item->first_name ?? null,
                    'surname'      => $item->surname ?? null,
                    'status'       => $item->status ?? null,
                    'assigned_to'  => $item->assigned_to,
                    'updated_at'   => optional($item->updated_at)->toIso8601String(),
                ];

                // Include issue information if present
                if ($item->issue_id) {
                    $data['issue'] = [
                        'id' => $item->issue_id,
                        'resolver_id' => $item->resolver_id,
                        'title' => $item->issue_title,
                        'status' => $item->issue_status,
                        'updated_at' => $item->issue_updated_at,
                        'url' => route('issues.show', ['issue' => $item->issue_id])
                    ];
                    
                    // Add notification type and message
                    $data['type'] = 'issue_update';
                    $data['message'] = "Report request: {$item->issue_title}";
                    $data['url'] = route('issues.show', ['issue' => $item->issue_id]);
                }

                return $data;
            })->all(),
        ];

        return response()->json($payload);
    }
}
