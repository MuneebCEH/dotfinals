<?php

// routes/web.php
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CallbackController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\IssueCommentController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadIssueController;
use App\Http\Controllers\ManagerNotificationController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProfileController;



Route::redirect('/', '/login');

// Public auth routes
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout')->middleware('auth');

// routes/web.php

Route::middleware('auth')->group(function () {
    // Preview route used by the Blade (no direct downloads)
    Route::get('/attachments/{path}/preview', [AttachmentController::class, 'preview'])
        ->name('attachments.preview');
});

Route::middleware(['auth', 'role:lead_manager'])->group(function () {
    // Lead Manager specific routes
    Route::resource('leads', LeadController::class);
});

// Protected area
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('/leads/{lead}/pdf', [LeadController::class, 'downloadTxt'])->name('leads.pdf');
    Route::get('/leads/{lead}/text-report', [LeadController::class, 'downloadTextReport'])->name('leads.text-report');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');
    Route::resource('leads', LeadController::class);
    Route::post('/issues/{issue}/comments', [IssueCommentController::class, 'store'])->name('issues.comments.store');
    Route::post('/leads/import', [LeadController::class, 'import'])->name('leads.import');

    // Reporter (assigned user) creates issue from Leads Edit page
    Route::post('/leads/{lead}/issues', [LeadIssueController::class, 'store'])
        ->name('leads.issues.store');
    Route::get('/leads/issues/{issue}', [LeadIssueController::class, 'showForUser'])->name('leads.issues.show');

    Route::post('/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.checkIn');
    Route::post('/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.checkOut');
    Route::get('/attendance-history', [AttendanceController::class, 'history'])->name('attendance.history');

    // Report Manager portal

    Route::middleware(['auth', 'role:admin|report_manager'])->group(function () {
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
        // Leads full CRUD + assign
        // Route::resource('leads', LeadController::class);
        Route::post('leads/{lead}/assign', [LeadController::class, 'assign'])->name('leads.assign');
        // Categories, Reports…
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('reports/export', [ReportController::class, 'export'])->name('reports.export');
        Route::post('/leads/bulk-assign', [LeadController::class, 'bulkAssign'])->name('leads.bulk-assign');
    });

    // Regular user: only see/update assigned leads
    Route::middleware(['role:user|super_agent|closer'])->group(function () {
        Route::get('my-leads', [LeadController::class, 'myLeads'])->name('leads.mine');
        Route::put('my-leads/{lead}', [LeadController::class, 'updateAssigned'])->name('leads.assigned.update');

        // Notes routes
        Route::resource('notes', NoteController::class)
            ->only(['index', 'store', 'update', 'destroy', 'edit']);
        // Route::post('my-leads/{lead}/notes', [NoteController::class, 'store'])->name('notes.store');
        // Route::delete('my-leads/{lead}/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');

        // Lead-specific notes
        Route::get('my-leads/{lead}/notes', [NoteController::class, 'leadNotes'])->name('leads.notes.index');

        Route::resource('callbacks', CallbackController::class);
        Route::post('/callbacks/{callback}/complete', [CallbackController::class, 'complete'])->name('callbacks.complete');
        Route::post('/callbacks/{callback}/reschedule', [CallbackController::class, 'reschedule'])->name('callbacks.reschedule');
    });
});
