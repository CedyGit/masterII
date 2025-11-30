<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InfrastructureController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/infrastructures', [InfrastructureController::class, 'index']);

Route::prefix('v1')->group(function () {
    // Liste tous les types d'infrastructures
    Route::get('/types', [InfrastructureController::class, 'getTypes']);
    
    // Liste toutes les infrastructures (avec pagination)
    Route::get('/infrastructures', [InfrastructureController::class, 'index']);
    
    // Infrastructures par type
    Route::get('/infrastructures/type/{type}', [InfrastructureController::class, 'byType']);
    
    // Recherche par ville
    Route::get('/infrastructures/city/{city}', [InfrastructureController::class, 'byCity']);
    
    // Recherche dans un rayon (latitude, longitude, rayon en km)
    Route::get('/infrastructures/nearby', [InfrastructureController::class, 'nearby']);
    
    // Détails d'une infrastructure
    Route::get('/infrastructures/{id}', [InfrastructureController::class, 'show']);
    
    // Statistiques
    Route::get('/stats', [InfrastructureController::class, 'stats']);
});