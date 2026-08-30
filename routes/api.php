<?php

use App\Http\Controllers\Api\DdoApiController;
use App\Http\Middleware\AuthenticateApiKey;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Secured endpoints for external enterprise integrations (e.g. SAP ERP).
|
*/

Route::middleware([AuthenticateApiKey::class])->group(function () {
    Route::prefix('ddos')->name('api.ddos.')->group(function () {
        Route::get('/', [DdoApiController::class, 'index'])->name('index');
        Route::get('/{id}', [DdoApiController::class, 'show'])->name('show');
    });
});
