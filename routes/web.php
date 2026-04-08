<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\FeatureController;
use App\Http\Controllers\Admin\GeneralSettingController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\PaymentSettingController;
use App\Http\Controllers\Admin\PhotoSessionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\StepController;
use App\Http\Controllers\Admin\TemplateController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Booth\BoothController;
use Illuminate\Support\Facades\Route;
use App\Models\Feature;
use App\Models\Step;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return inertia('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
        'features' => Feature::active()->get(['id', 'icon', 'title', 'description']),
        'steps' => Step::active()->get(['id', 'number', 'title', 'description']),
    ]);
})->name('home');

// Booth routes (public - no auth required)
Route::prefix('booth')->name('booth.')->group(function () {
    Route::get('{branch:code}', [BoothController::class, 'show'])->name('show');
    Route::post('session/start', [BoothController::class, 'startSession'])->name('session.start');
    Route::post('session/create', [BoothController::class, 'createSession'])->name('session.create');
    Route::get('payment/{transaction}/status', [BoothController::class, 'checkPayment'])->name('payment.status');
    Route::post('payment/{transaction}/simulate', [BoothController::class, 'simulatePayment'])->name('payment.simulate');
    Route::post('photo/capture', [BoothController::class, 'capturePhoto'])->name('photo.capture');
    Route::post('session/template', [BoothController::class, 'chooseTemplate'])->name('session.template');
    Route::post('session/complete', [BoothController::class, 'completeSession'])->name('session.complete');
    Route::post('payment/callback', [BoothController::class, 'duitkuCallback'])->name('payment.callback')->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // Master Data
    Route::resource('packages', PackageController::class);
    Route::get('packages-data', [PackageController::class, 'data'])->name('packages.data');
    Route::resource('templates', TemplateController::class);
    Route::get('templates-data', [TemplateController::class, 'data'])->name('templates.data');

    // Operasional
    Route::resource('transactions', TransactionController::class)->only(['index', 'show']);
    Route::get('transactions-data', [TransactionController::class, 'data'])->name('transactions.data');
    Route::resource('photo-sessions', PhotoSessionController::class)->only(['index', 'show']);
    Route::get('photo-sessions-data', [PhotoSessionController::class, 'data'])->name('photo-sessions.data');
    Route::get('photos/{photo}/download', [PhotoSessionController::class, 'downloadPhoto'])->name('photos.download');

    // Laporan
    Route::get('reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
    Route::get('reports/revenue-data', [ReportController::class, 'revenueData'])->name('reports.revenue.data');

    // Admin only
    Route::middleware('admin')->group(function () {
        Route::resource('branches', BranchController::class);
        Route::get('branches-data', [BranchController::class, 'data'])->name('branches.data');
        Route::resource('features', FeatureController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('features-data', [FeatureController::class, 'data'])->name('features.data');
        Route::resource('steps', StepController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('steps-data', [StepController::class, 'data'])->name('steps.data');
        Route::get('reports/branches', [ReportController::class, 'branches'])->name('reports.branches');
        Route::get('settings/general', [GeneralSettingController::class, 'edit'])->name('settings.general');
        Route::put('settings/general', [GeneralSettingController::class, 'update'])->name('settings.general.update');
        Route::get('settings/payment', [PaymentSettingController::class, 'edit'])->name('settings.payment');
        Route::put('settings/payment', [PaymentSettingController::class, 'update'])->name('settings.payment.update');
        Route::resource('users', UserController::class);
        Route::get('users-data', [UserController::class, 'data'])->name('users.data');
    });
});

require __DIR__.'/settings.php';
