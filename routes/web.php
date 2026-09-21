<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\RoomController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Public Check-In Routes (Accessed via QR code scan without auth requirement)
Route::get('/check-in/{token}', [CheckInController::class, 'show'])->name('checkin.show');
Route::post('/check-in/{token}', [CheckInController::class, 'confirm'])->name('checkin.confirm');

Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rooms (Resource)
    Route::resource('rooms', RoomController::class);

    // Reservations Calendar (Must be before resource route to avoid conflict with {reservation})
    Route::get('/reservations/calendar', [ReservationController::class, 'calendar'])->name('reservations.calendar');

    // QR Code generation for reservation
    Route::get('/reservations/{reservation}/qr-code', [CheckInController::class, 'qrCode'])->name('reservations.qr-code');

    // Reservations (Resource)
    Route::resource('reservations', ReservationController::class)->except(['edit', 'update']);

    // Approvals (Manual routes)
    Route::prefix('approvals')->name('approvals.')->middleware('permission:approve-reservation')->group(function () {
        Route::get('/', [ApprovalController::class, 'index'])->name('index');
        Route::post('/{reservation}/approve', [ApprovalController::class, 'approve'])->name('approve');
        Route::post('/{reservation}/reject', [ApprovalController::class, 'reject'])->name('reject');
    });

    // Reports (Excel & PDF export)
    Route::prefix('reports')->name('reports.')->middleware('permission:view-reports')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/export-excel', [ReportController::class, 'exportExcel'])->name('export-excel');
        Route::get('/export-pdf', [ReportController::class, 'exportPdf'])->name('export-pdf');
    });

    // Analytics Dashboard
    Route::get('/analytics', [AnalyticsController::class, 'index'])
        ->middleware('permission:view-reports')
        ->name('analytics.index');

    // Notifications (JSON API)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
});

require __DIR__.'/auth.php';
