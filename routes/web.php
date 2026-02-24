<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ApiTokenController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin vendor management
    Route::resource('/admin/vendors', \App\Http\Controllers\AdminVendorController::class, [
        'names' => [
            'index' => 'admin.vendors.index',
            'create' => 'admin.vendors.create',
            'store' => 'admin.vendors.store',
            'edit' => 'admin.vendors.edit',
            'update' => 'admin.vendors.update',
            'destroy' => 'admin.vendors.destroy',
        ]
    ]);
    
        // Admin brand management
        Route::resource('/admin/brands', \App\Http\Controllers\BrandController::class, [
            'names' => [
                'index' => 'admin.brands.index',
                'create' => 'admin.brands.create',
                'store' => 'admin.brands.store',
                'edit' => 'admin.brands.edit',
                'update' => 'admin.brands.update',
                'destroy' => 'admin.brands.destroy',
            ]
        ]);

    // Admin role management
    Route::resource('/admin/roles', \App\Http\Controllers\RoleController::class, [
        'names' => [
            'index' => 'admin.roles.index',
            'create' => 'admin.roles.create',
            'store' => 'admin.roles.store',
            'edit' => 'admin.roles.edit',
            'update' => 'admin.roles.update',
            'destroy' => 'admin.roles.destroy',
        ]
    ]);

    // Admin permission management
    Route::resource('/admin/permissions', \App\Http\Controllers\PermissionController::class, [
        'names' => [
            'index' => 'admin.permissions.index',
            'create' => 'admin.permissions.create',
            'store' => 'admin.permissions.store',
            'edit' => 'admin.permissions.edit',
            'update' => 'admin.permissions.update',
            'destroy' => 'admin.permissions.destroy',
        ]
    ]);

    // Admin user management
    Route::resource('/admin/users', \App\Http\Controllers\UserController::class, [
        'names' => [
            'index' => 'admin.users.index',
            'create' => 'admin.users.create',
            'store' => 'admin.users.store',
            'edit' => 'admin.users.edit',
            'update' => 'admin.users.update',
            'destroy' => 'admin.users.destroy',
        ]
    ]);

    // Admin category management
    Route::resource('/admin/categories', \App\Http\Controllers\CategoryController::class, [
        'names' => [
            'index' => 'admin.categories.index',
            'create' => 'admin.categories.create',
            'store' => 'admin.categories.store',
            'edit' => 'admin.categories.edit',
            'update' => 'admin.categories.update',
            'destroy' => 'admin.categories.destroy',
        ]
    ]);

    // Admin product management
    Route::resource('/admin/products', \App\Http\Controllers\ProductController::class, [
        'names' => [
            'index' => 'admin.products.index',
            'create' => 'admin.products.create',
            'store' => 'admin.products.store',
            'show' => 'admin.products.show',
            'edit' => 'admin.products.edit',
            'update' => 'admin.products.update',
            'destroy' => 'admin.products.destroy',
        ]
    ]);
    Route::delete('/admin/products/image/{image}', [\App\Http\Controllers\ProductController::class, 'destroyImage'])->name('admin.products.destroyImage');

    // Admin variation attribute management
    Route::resource('/admin/variation-attributes', \App\Http\Controllers\VariationAttributeController::class, [
        'names' => [
            'index' => 'admin.variation-attributes.index',
            'create' => 'admin.variation-attributes.create',
            'store' => 'admin.variation-attributes.store',
            'edit' => 'admin.variation-attributes.edit',
            'update' => 'admin.variation-attributes.update',
            'destroy' => 'admin.variation-attributes.destroy',
        ]
    ]);

    // Admin variation attribute values management
    Route::resource('/admin/variation-attribute-values', \App\Http\Controllers\VariationAttributeValueController::class, [
        'names' => [
            'index' => 'admin.variation-attribute-values.index',
            'create' => 'admin.variation-attribute-values.create',
            'store' => 'admin.variation-attribute-values.store',
            'edit' => 'admin.variation-attribute-values.edit',
            'update' => 'admin.variation-attribute-values.update',
            'destroy' => 'admin.variation-attribute-values.destroy',
        ]
    ]);

    Route::get('/admin/variation-attributes/{attribute}/values', function ($attributeId) {
        $values = \App\Models\VariationAttributeValue::where('variation_attribute_id', $attributeId)->get(['id', 'value']);
        return response()->json($values);
    })->name('admin.variation-attributes.values');

    // API Token management (admin only)
    Route::get('/admin/api-tokens/create', [ApiTokenController::class, 'create'])->name('admin.api-tokens.create');
    Route::post('/admin/api-tokens', [ApiTokenController::class, 'store'])->name('admin.api-tokens.store');


    Route::get('/admin/master-products/search', [App\Http\Controllers\ProductController::class, 'masterProductSearch'])->name('admin.master-products.search');
    
    // Admin Master Product Management
    Route::post('/admin/master-products/sync', [\App\Http\Controllers\MasterProductController::class, 'sync'])->name('admin.master-products.sync');
    Route::resource('/admin/master-products', \App\Http\Controllers\MasterProductController::class, [
        'names' => 'admin.master-products'
    ]);

    // Product Integrity
    Route::get('/admin/product-integrity', [\App\Http\Controllers\Admin\ProductIntegrityController::class, 'index'])->name('admin.product-integrity.index');
    Route::post('/admin/product-integrity/auto-fix', [\App\Http\Controllers\Admin\ProductIntegrityController::class, 'autoFix'])->name('admin.product-integrity.auto-fix');
    Route::post('/admin/product-integrity/{product}/detach', [\App\Http\Controllers\Admin\ProductIntegrityController::class, 'detach'])->name('admin.product-integrity.detach');

    // Search Debugger
    Route::get('/admin/search-debug', [App\Http\Controllers\Admin\SearchDebugController::class, 'index'])->name('admin.search-debug.index');
    Route::post('/admin/search-debug/replay/{id}', [App\Http\Controllers\Admin\SearchDebugController::class, 'replay'])->name('admin.search-debug.replay');
});

Route::prefix('admin')->middleware(['web', 'auth', 'role:super.admin|admin'])->group(function () {
    Route::get('api-tokens/create', [ApiTokenController::class, 'create'])->name('admin.api-tokens.create');
    Route::post('api-tokens', [ApiTokenController::class, 'store'])->name('admin.api-tokens.store');
});

require __DIR__.'/auth.php';
