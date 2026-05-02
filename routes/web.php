<?php

use App\Http\Controllers\Admin\AboutSettingController;
use App\Http\Controllers\Admin\BoothSettingController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmailSettingController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\FeatureController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\GeneralSettingController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\PaymentSettingController;
use App\Http\Controllers\Admin\PhotoSessionController;
use App\Http\Controllers\Admin\RecaptchaSettingController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\StepController;
use App\Http\Controllers\Admin\TemplateController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Booth\BoothController;
use App\Models\Branch;
use App\Models\Faq;
use App\Models\Gallery;
use App\Models\Package;
use App\Models\Setting;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return inertia('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
        'galleries' => Gallery::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'title', 'image_path']),
    ]);
})->name('home');

Route::get('/profil', function () {
    return inertia('profile', [
        'about' => Setting::getMany([
            'about_title',
            'about_tagline',
            'about_description',
            'about_vision',
            'about_mission',
            'about_founded_year',
            'about_total_sessions',
            'about_total_branches',
            'about_total_clients',
            'about_hero_image_path',
        ]),
    ]);
})->name('profile');

Route::get('/harga', function () {
    $packages = Package::where('is_active', true)
        ->orderBy('price')
        ->get(['id', 'name', 'description', 'photo_count', 'print_size', 'price']);

    $faqs = Faq::query()
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('id')
        ->get(['id', 'question', 'answer']);

    return inertia('harga', [
        'packages' => $packages,
        'faqs' => $faqs,
        'whatsapp_number' => Setting::get('whatsapp_number'),
    ]);
})->name('harga');

Route::get('/kontak', function () {
    return inertia('contact', [
        'contact' => Setting::getMany([
            'site_description',
            'google_maps_embed',
            'instagram_url',
            'facebook_url',
            'x_url',
            'tiktok_url',
            'whatsapp_number',
        ]),
    ]);
})->name('contact');

Route::get('/cabang', function () {
    $branches = Branch::query()
        ->where('is_active', true)
        ->withCount(['photoSessions', 'transactions'])
        ->orderBy('name')
        ->get(['id', 'name', 'code', 'address', 'phone', 'photo']);

    return inertia('branches/index', [
        'branches' => $branches->map(fn (Branch $branch) => [
            'id' => $branch->id,
            'name' => $branch->name,
            'code' => $branch->code,
            'address' => $branch->address,
            'phone' => $branch->phone,
            'photo' => $branch->photo ? asset('storage/'.$branch->photo) : null,
            'photo_sessions_count' => $branch->photo_sessions_count,
            'transactions_count' => $branch->transactions_count,
        ]),
    ]);
})->name('branches.public');

Route::get('/cabang/{branch:code}', function (Branch $branch) {
    abort_if(! $branch->is_active, 404);

    $branch->loadCount(['photoSessions', 'transactions']);

    $otherBranches = Branch::query()
        ->where('is_active', true)
        ->whereKeyNot($branch->id)
        ->orderBy('name')
        ->limit(3)
        ->get(['id', 'name', 'code', 'address', 'phone', 'photo']);

    return inertia('branches/show', [
        'branch' => [
            'id' => $branch->id,
            'name' => $branch->name,
            'code' => $branch->code,
            'address' => $branch->address,
            'phone' => $branch->phone,
            'photo' => $branch->photo ? asset('storage/'.$branch->photo) : null,
            'photo_sessions_count' => $branch->photo_sessions_count,
            'transactions_count' => $branch->transactions_count,
        ],
        'otherBranches' => $otherBranches->map(fn (Branch $item) => [
            'id' => $item->id,
            'name' => $item->name,
            'code' => $item->code,
            'address' => $item->address,
            'phone' => $item->phone,
            'photo' => $item->photo ? asset('storage/'.$item->photo) : null,
        ]),
    ]);
})->name('branches.public.show');

// Booth routes (public - no auth required)
Route::prefix('booth')->name('booth.')->group(function () {
    Route::get('{branch:code}', [BoothController::class, 'show'])->name('show');
    Route::post('voucher/validate', [BoothController::class, 'validateVoucher'])->name('voucher.validate');
    Route::post('session/start', [BoothController::class, 'startSession'])->name('session.start');
    Route::post('session/reissue', [BoothController::class, 'reissueSession'])->name('session.reissue');
    Route::post('session/create', [BoothController::class, 'createSession'])->name('session.create');
    Route::get('payment/{transaction}/status', [BoothController::class, 'checkPayment'])->name('payment.status');
    Route::post('payment/{transaction}/simulate', [BoothController::class, 'simulatePayment'])->name('payment.simulate');
    Route::post('payment/{transaction}/confirm-manual', [BoothController::class, 'confirmManualPayment'])->name('payment.confirm-manual');
    Route::post('photo/capture', [BoothController::class, 'capturePhoto'])->name('photo.capture');
    Route::post('session/template', [BoothController::class, 'chooseTemplate'])->name('session.template');
    Route::post('session/complete', [BoothController::class, 'completeSession'])->name('session.complete');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // Master Data
    Route::resource('packages', PackageController::class);
    Route::get('packages-data', [PackageController::class, 'data'])->name('packages.data');
    Route::get('templates/builder', [TemplateController::class, 'builder'])->name('templates.builder');
    Route::get('templates/{template}/builder', [TemplateController::class, 'builderEdit'])->name('templates.builder.edit');
    Route::resource('templates', TemplateController::class);
    Route::get('templates-data', [TemplateController::class, 'data'])->name('templates.data');

    // Operasional
    Route::resource('transactions', TransactionController::class)->only(['index', 'show']);
    Route::get('transactions-data', [TransactionController::class, 'data'])->name('transactions.data');
    Route::get('transactions/export/excel', [TransactionController::class, 'exportExcel'])->name('transactions.export.excel');
    Route::get('transactions/export/pdf', [TransactionController::class, 'exportPdf'])->name('transactions.export.pdf');
    Route::resource('photo-sessions', PhotoSessionController::class)->only(['index', 'show']);
    Route::get('photo-sessions-data', [PhotoSessionController::class, 'data'])->name('photo-sessions.data');
    Route::get('photos/{photo}/download', [PhotoSessionController::class, 'downloadPhoto'])->name('photos.download');

    // Laporan
    Route::get('reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
    Route::get('reports/revenue-data', [ReportController::class, 'revenueData'])->name('reports.revenue.data');
    Route::get('reports/revenue/export-excel', [ReportController::class, 'exportExcel'])->name('reports.revenue.export.excel');
    Route::get('reports/revenue/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.revenue.export.pdf');

    // Admin only
    Route::middleware('admin')->group(function () {
        Route::resource('branches', BranchController::class);
        Route::get('branches-data', [BranchController::class, 'data'])->name('branches.data');
        Route::resource('features', FeatureController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('features-data', [FeatureController::class, 'data'])->name('features.data');
        Route::resource('faqs', FaqController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('faqs-data', [FaqController::class, 'data'])->name('faqs.data');
        Route::resource('steps', StepController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('steps-data', [StepController::class, 'data'])->name('steps.data');
        Route::resource('galleries', GalleryController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('galleries-data', [GalleryController::class, 'data'])->name('galleries.data');

        Route::get('vouchers/bulk', [VoucherController::class, 'bulkCreate'])->name('vouchers.bulk.create');
        Route::post('vouchers/bulk', [VoucherController::class, 'bulkStore'])->name('vouchers.bulk.store');
        Route::post('vouchers/print', [VoucherController::class, 'bulkPrint'])->name('vouchers.print.bulk');
        Route::get('vouchers/{voucher}/print', [VoucherController::class, 'print'])->name('vouchers.print');
        Route::get('vouchers-data', [VoucherController::class, 'data'])->name('vouchers.data');
        Route::resource('vouchers', VoucherController::class)->except(['show']);

        Route::get('reports/branches', [ReportController::class, 'branches'])->name('reports.branches');
        Route::get('settings/general', [GeneralSettingController::class, 'edit'])->name('settings.general');
        Route::put('settings/general', [GeneralSettingController::class, 'update'])->name('settings.general.update');
        Route::get('settings/about', [AboutSettingController::class, 'edit'])->name('settings.about');
        Route::put('settings/about', [AboutSettingController::class, 'update'])->name('settings.about.update');
        Route::get('settings/payment', [PaymentSettingController::class, 'edit'])->name('settings.payment');
        Route::put('settings/payment', [PaymentSettingController::class, 'update'])->name('settings.payment.update');
        Route::get('settings/booth', [BoothSettingController::class, 'edit'])->name('settings.booth');
        Route::put('settings/booth', [BoothSettingController::class, 'update'])->name('settings.booth.update');
        Route::get('settings/email', [EmailSettingController::class, 'edit'])->name('settings.email');
        Route::put('settings/email', [EmailSettingController::class, 'update'])->name('settings.email.update');
        Route::post('settings/email/test', [EmailSettingController::class, 'test'])->name('settings.email.test');
        Route::get('settings/recaptcha', [RecaptchaSettingController::class, 'edit'])->name('settings.recaptcha');
        Route::put('settings/recaptcha', [RecaptchaSettingController::class, 'update'])->name('settings.recaptcha.update');
        Route::resource('users', UserController::class);
        Route::get('users-data', [UserController::class, 'data'])->name('users.data');
    });
});

require __DIR__.'/settings.php';
