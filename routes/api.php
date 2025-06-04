<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StorefrontProductController;

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

Route::middleware('auth:sanctum')->prefix('storefront')->group(function () {
    // List/search products
    Route::get('products', [StorefrontProductController::class, 'index']);
    // Show a single product by ID (now using controller method)
    Route::get('products/{product}', [StorefrontProductController::class, 'show']);
});
