<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        return view('dashboard');
        // $user = Auth::user();
        
        // // Get statistics
        // $totalLeads = Lead::forUser($user->id)->count();
        // $activeLeads = Lead::forUser($user->id)->byStatus('active')->count();
        // $convertedLeads = Lead::forUser($user->id)->byStatus('converted')->count();
        // $pendingLeads = Lead::forUser($user->id)->byStatus('pending')->count();
        
        // // Get recent leads
        // $recentLeads = Lead::forUser($user->id)
        //     ->with(['category'])
        //     ->latest()
        //     ->take(5)
        //     ->get();
        
        // return view('dashboard', compact(
        //     'totalLeads',
        //     'activeLeads', 
        //     'convertedLeads',
        //     'pendingLeads',
        //     'recentLeads'
        // ));
    }
} 