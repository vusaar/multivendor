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


//middleware('auth:sanctum')->

Route::prefix('storefront')->group(function () {
    // Flexible search endpoint
    Route::post('products/search', [StorefrontProductController::class, 'search']);

    Route::post('products/search2', [StorefrontProductController::class, 'search2']);
    // List/search products (original index)
    Route::get('products', [StorefrontProductController::class, 'index']);
    // Show a single product by ID
    Route::get('products/{product}', [StorefrontProductController::class, 'show']);
    // List categories 
    Route::get('categorieslist', [StorefrontProductController::class, 'categories']);

    // list products
    Route::get('productslist', [StorefrontProductController::class, 'products']); 
});
