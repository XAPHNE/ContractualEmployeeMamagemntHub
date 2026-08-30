<?php

use App\Http\Controllers\Api\DdoApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
|
*/

Route::prefix('ddos')->name('api.ddos.')->group(function () {
    Route::get('/', [DdoApiController::class, 'index'])->name('index');
    Route::get('/{id}', [DdoApiController::class, 'show'])->name('show');
});
