<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CallbackController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\IssueCommentController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadIssueController;
use App\Http\Controllers\ManagerNotificationController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

/*
|--------------------------------------------------------------------------
| Public (auth)
|--------------------------------------------------------------------------
*/
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Protected (auth required)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Dashboard & Profile (single definitions)
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Attachments preview (once)
    Route::get('/attachments/{path}/preview', [AttachmentController::class, 'preview'])
        ->where('path', '.*')
        ->name('attachments.preview');

    // Leads — single resource definition (use policies/authorizeResource to gate actions)
    Route::resource('leads', LeadController::class);

    // Lead exports/previews
    Route::get('/leads/{lead}/pdf', [LeadController::class, 'downloadTxt'])->name('leads.pdf');
    Route::get('/leads/{lead}/text-report', [LeadController::class, 'downloadTextReport'])->name('leads.text-report');

    // Reporter (assigned user) can create issues from Leads Edit page
    Route::post('/leads/{lead}/issues', [LeadIssueController::class, 'store'])->name('leads.issues.store');
    Route::get('/leads/issues/{issue}', [LeadIssueController::class, 'showForUser'])->name('leads.issues.show');

    // Attendance
    Route::post('/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.checkIn');
    Route::post('/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.checkOut');
    Route::get('/attendance-history', [AttendanceController::class, 'history'])->name('attendance.history');

    // Notes
    Route::resource('notes', NoteController::class)->only(['index', 'store', 'update', 'destroy', 'edit']);
    Route::get('my-leads/{lead}/notes', [NoteController::class, 'leadNotes'])->name('leads.notes.index');

    // Callbacks
    Route::resource('callbacks', CallbackController::class);
    Route::post('/callbacks/{callback}/complete', [CallbackController::class, 'complete'])->name('callbacks.complete');
    Route::post('/callbacks/{callback}/reschedule', [CallbackController::class, 'reschedule'])->name('callbacks.reschedule');

    /*
    |--------------------------------------------------------------------------
    | Role-scoped areas (no duplicate base routes)
    |--------------------------------------------------------------------------
    */

    // Regular user / super_agent / closer
    Route::middleware(['role:user|super_agent|closer'])->group(function () {
        Route::get('my-leads', [LeadController::class, 'myLeads'])->name('leads.mine');
        Route::put('my-leads/{lead}', [LeadController::class, 'updateAssigned'])->name('leads.assigned.update');
    });

    // Report Manager portal
    Route::middleware(['role:admin|report_manager'])->group(function () {
        Route::get('/issues', [LeadIssueController::class, 'index'])->name('issues.index');
        Route::get('/issues/{issue}', [LeadIssueController::class, 'show'])->name('issues.show');
        Route::patch('/issues/{issue}/status', [LeadIssueController::class, 'updateStatus'])->name('issues.updateStatus');
        Route::patch('/issues/{issue}/priority', [LeadIssueController::class, 'updatePriority'])->name('issues.updatePriority');

        Route::get('/notifications', [ManagerNotificationController::class, 'index'])->name('rm.notifications');
        Route::post('/notifications/read-all', [ManagerNotificationController::class, 'readAll'])->name('rm.notifications.readAll');
    });

    // Admin-only
    Route::middleware('role:admin')->group(function () {
        // Users management
        Route::resource('users', UserController::class);

        // Lead management extras
        Route::post('leads/{lead}/assign', [LeadController::class, 'assign'])->name('leads.assign');
        Route::post('/leads/bulk-assign', [LeadController::class, 'bulkAssign'])->name('leads.bulk-assign');
        Route::post('/leads/import', [LeadController::class, 'import'])->name('leads.import');

        // Categories, Reports
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('reports/export', [ReportController::class, 'export'])->name('reports.export');
    });
});
