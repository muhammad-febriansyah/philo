<?php

use App\Http\Controllers\Booth\BoothController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Stateless endpoints used by external systems. Payment-gateway webhooks
| live here so they bypass session/CSRF middleware that the web group
| applies.
|
*/

Route::prefix('booth')->name('api.booth.')->group(function () {
    Route::post('payment/callback', [BoothController::class, 'dokuCallback'])
        ->name('payment.callback');
});
