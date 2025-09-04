<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Display the reports dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Current month statistics
        $totalLeads = Lead::forUser($user->id)->count();
        $activeLeads = Lead::forUser($user->id)->where('status', 'active')->count();
        $convertedLeads = Lead::forUser($user->id)->where('status', 'converted')->count();
        $pendingLeads = Lead::forUser($user->id)->where('status', 'pending')->count();
        
        // Last month statistics
        $lastMonthTotal = Lead::forUser($user->id)
            ->where('created_at', '<', now()->startOfMonth())
            ->where('created_at', '>=', now()->subMonth()->startOfMonth())
            ->count();
        $lastMonthActive = Lead::forUser($user->id)
            ->where('status', 'active')
            ->where('created_at', '<', now()->startOfMonth())
            ->where('created_at', '>=', now()->subMonth()->startOfMonth())
            ->count();
        $lastMonthConverted = Lead::forUser($user->id)
            ->where('status', 'converted')
            ->where('created_at', '<', now()->startOfMonth())
            ->where('created_at', '>=', now()->subMonth()->startOfMonth())
            ->count();
            
        // Calculate growth percentages
        $leadGrowth = $lastMonthTotal > 0 ? round((($totalLeads - $lastMonthTotal) / $lastMonthTotal) * 100, 1) : 0;
        
        // Calculate current and last month conversion rates
        $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 1) : 0;
        $lastMonthConversionRate = $lastMonthTotal > 0 ? round(($lastMonthConverted / $lastMonthTotal) * 100, 1) : 0;
        $conversionRateGrowth = $lastMonthConversionRate > 0 ? 
            round((($conversionRate - $lastMonthConversionRate) / $lastMonthConversionRate) * 100, 1) : 0;
        
        // Get categories count and growth
        $currentCategories = Category::whereHas('leads', function ($query) use ($user) {
            $query->forUser($user->id);
        })->count();

        $lastMonthCategories = Category::whereHas('leads', function ($query) use ($user) {
            $query->forUser($user->id)
                ->where('created_at', '<', now()->startOfMonth())
                ->where('created_at', '>=', now()->subMonth()->startOfMonth());
        })->count();

        $categoryGrowth = $lastMonthCategories > 0 ? 
            round((($currentCategories - $lastMonthCategories) / $lastMonthCategories) * 100, 1) : 0;

        // Get top categories
        $topCategories = Category::withCount(['leads' => function ($query) use ($user) {
            $query->forUser($user->id);
        }])->orderBy('leads_count', 'desc')->take(5)->get();
        
        // Get top users (admin only)
        $topUsers = null;
        if ($user->is_admin) {
            $topUsers = User::withCount('leads')->orderBy('leads_count', 'desc')->take(5)->get();
        }
        
        // Get recent activity
        $recentActivity = Lead::forUser($user->id)
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($lead) {
                return (object) [
                    'description' => "Lead '{$lead->name}' was {$lead->status}",
                    'created_at' => $lead->created_at,
                ];
            });
        
        return view('reports.index', compact(
            'totalLeads',
            'activeLeads',
            'convertedLeads',
            'pendingLeads',
            'conversionRate',
            'conversionRateGrowth',
            'leadGrowth',
            'categoryGrowth',
            'topCategories',
            'topUsers',
            'recentActivity'
        ));
    }

    /**
     * Export the report data
     */
    public function export()
    {
        $user = Auth::user();
        
        // Get statistics
        $totalLeads = Lead::forUser($user->id)->count();
        $activeLeads = Lead::forUser($user->id)->where('status', 'active')->count();
        $convertedLeads = Lead::forUser($user->id)->where('status', 'converted')->count();
        $pendingLeads = Lead::forUser($user->id)->where('status', 'pending')->count();
        
        // Calculate conversion rate
        $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 1) : 0;
        
        // Get top categories
        $topCategories = Category::withCount(['leads' => function ($query) use ($user) {
            $query->forUser($user->id);
        }])->orderBy('leads_count', 'desc')->take(5)->get();

        // Format data for PDF
        $data = [
            'user' => $user,
            'totalLeads' => $totalLeads,
            'activeLeads' => $activeLeads,
            'convertedLeads' => $convertedLeads,
            'pendingLeads' => $pendingLeads,
            'conversionRate' => $conversionRate,
            'topCategories' => $topCategories,
            'generatedAt' => now()->format('Y-m-d H:i:s')
        ];

        // Generate PDF
        $pdf = PDF::loadView('reports.export', $data);
        
        return $pdf->download('leads-report.pdf');
    }
}