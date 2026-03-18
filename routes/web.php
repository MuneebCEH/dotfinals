<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AttendanceBeaconController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CallbackController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\IssueCommentController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadIssueController;
use App\Http\Controllers\LeadRealtimeController;
use App\Http\Controllers\ManagerNotificationController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});


Route::get('/online-users', [AttendanceController::class, 'onlineUsersJson'])
    ->name('users.online');

Route::middleware(['auth'])->group(function () {
    Route::get('/leads/realtime', [LeadRealtimeController::class, 'since'])
        ->name('leads.realtime');
});

Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])
    ->name('notifications.markAllAsRead')
    ->middleware('auth');
Route::post('/notifications/{notification}/mark-as-read', [NotificationController::class, 'markOneAsRead'])
    ->name('notifications.markOneAsRead')
    ->middleware('auth');

/*
|--------------------------------------------------------------------------
| Public (auth)
|--------------------------------------------------------------------------
*/
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout')->middleware('auth');
Route::post('/attendance/ping', [AttendanceController::class, 'ping'])->name('attendance.ping');
Route::post('/attendance/checkout-beacon', [AttendanceController::class, 'beaconCheckout'])->name('attendance.checkout');
Route::post('/logout-beacon', [LoginController::class, 'logoutBeacon'])->name('logout.beacon');
Route::post('/logout-pending', [LoginController::class, 'pending'])->name('logout.pending');
Route::post('/logout-cancel', [LoginController::class, 'cancel'])->name('logout.cancel');

/*
|--------------------------------------------------------------------------
| Protected (auth required)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/leads/maxout', [LeadController::class, 'maxOut'])->name('leads.maxout');
    Route::get('/leads/submitted', [LeadController::class, 'submitted'])->name('leads.submitted');
    // Dashboard & Profile (single definitions)
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

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

    Route::post('/issues/{issue}/comments', [IssueCommentController::class, 'store'])->name('issues.comments.store');

    // Attendance
    Route::post('/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.checkIn');
    // Route::post('/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.checkOut');
    Route::post('/beacon/attendance/checkout', [AttendanceController::class, 'checkoutBeacon'])
        ->name('attendance.checkout.beacon');
    Route::get('/attendance-history', [AttendanceController::class, 'history'])->name('attendance.history');

    // Notes
    Route::resource('notes', NoteController::class)->only(['index', 'store', 'update', 'destroy', 'edit']);
    Route::get('my-leads/{lead}/notes', [NoteController::class, 'leadNotes'])->name('leads.notes.index');

    // Callbacks
    Route::resource('callbacks', CallbackController::class);
    Route::post('/callbacks/{callback}/complete', [CallbackController::class, 'complete'])->name('callbacks.complete');
    Route::post('/callbacks/{callback}/reschedule', [CallbackController::class, 'reschedule'])->name('callbacks.reschedule');

    Route::post('/attendance/heartbeat', [AttendanceBeaconController::class, 'heartbeat'])
        ->name('attendance.heartbeat');
    Route::post('/attendance/close', [AttendanceBeaconController::class, 'close'])
        ->name('attendance.close');



    // Generic user notifications helpers
    Route::get('/notifications/unread-count', function () {
        return response()->json(['count' => auth()->user()->unreadNotifications()->count()]);
    })->name('notifications.unread_count');


    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');

    /*
    |--------------------------------------------------------------------------
    | Role-scoped areas (no duplicate base routes)
    |--------------------------------------------------------------------------
    */

    // Regular user / super_agent / closer
    Route::middleware(['role:user|super_agent|closer|lead_manager|report_manager'])->group(function () {
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
    Route::middleware(['role:auth|admin'])->group(function () {
        // Users management
        Route::resource('users', UserController::class);

        // Lead management extras
        Route::post('leads/{lead}/assign', [LeadController::class, 'assign'])->name('leads.assign');
        Route::post('/leads/bulk-assign', [LeadController::class, 'bulkAssign'])->name('leads.bulk-assign');
        Route::get('leads/ids', [LeadController::class, 'ids'])->name('leads.ids');
        Route::delete('leads/actions/bulk', [LeadController::class, 'bulkDestroy'])
            ->name('leads.bulk-destroy');
        Route::post('/leads/import', [LeadController::class, 'import'])->name('leads.import');

        // Categories, Reports
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('reports/export', [ReportController::class, 'export'])->name('reports.export');

        // Live Push Alert Ticker
        Route::get('ticker-alerts', [\App\Http\Controllers\TickerAlertController::class, 'index'])->name('ticker-alerts.index');
        Route::post('ticker-alerts', [\App\Http\Controllers\TickerAlertController::class, 'store'])->name('ticker-alerts.store');
        Route::patch('ticker-alerts/{ticker}/toggle', [\App\Http\Controllers\TickerAlertController::class, 'toggleStatus'])->name('ticker-alerts.toggle');
        Route::delete('ticker-alerts/{ticker}', [\App\Http\Controllers\TickerAlertController::class, 'destroy'])->name('ticker-alerts.destroy');
    });
    // Announcements
    Route::get('/announcements', [\App\Http\Controllers\AnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('/announcements', [\App\Http\Controllers\AnnouncementController::class, 'store'])->name('announcements.store');
    Route::patch('/announcements/{announcement}/toggle', [\App\Http\Controllers\AnnouncementController::class, 'toggleStatus'])->name('announcements.toggle');
    Route::delete('/announcements/{announcement}', [\App\Http\Controllers\AnnouncementController::class, 'destroy'])->name('announcements.destroy');
});
