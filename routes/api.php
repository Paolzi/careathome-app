<?php

use App\Http\Controllers\Api\ServiceCatalogApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
|
*/

Route::prefix('v1')->group(function () {
    // SCRUM-41: BE API Master Katalog & Filtering Layanan
    Route::get('/katalog', [ServiceCatalogApiController::class, 'index'])->name('api.katalog.index');
    Route::get('/katalog/{slugOrId}', [ServiceCatalogApiController::class, 'show'])->name('api.katalog.show');
});
