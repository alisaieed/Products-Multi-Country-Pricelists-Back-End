<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UnitOfMeasureController;
use App\Http\Controllers\ProductUnitController;
use App\Http\Controllers\ProductPriceController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\SerialNumberController;
use App\Http\Controllers\WarehouseLocationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|--------------------------------------------------------------------------
*/



// Tenants
Route::apiResource('tenants', TenantController::class);

// Countries
Route::apiResource('countries', CountryController::class);

// Categories
Route::apiResource('categories', CategoryController::class);

// Units of Measure
Route::apiResource('units-of-measure', UnitOfMeasureController::class);

// Products
Route::apiResource('products', ProductController::class);
Route::get('products/{productId}/country/{countryId}',
    [ProductController::class, 'getProductByIdAndCountry']);
Route::get('products/{productId}/country/{countryId}/price',
    [ProductController::class, 'getPriceByProductAndCountry']);
// Country only
Route::get('products/country/{countryId}',
    [ProductController::class, 'getByCountryAndCategory']);
// Country + Category
Route::get('products/country/{countryId}/category/{categoryId}',
    [ProductController::class, 'getByCountryAndCategory']);


// Product Units
Route::apiResource('product-units', ProductUnitController::class);

// Product Prices
Route::apiResource('product-prices', ProductPriceController::class);

// Custom route for product-country price lookup
Route::get('product-prices/product/{productId}/country/{countryId}',
    [ProductPriceController::class, 'getPriceByProductAndCountry']);

// Batches
Route::apiResource('batches', BatchController::class);

// Serial Numbers
Route::apiResource('serial-numbers', SerialNumberController::class);

// Warehouse Locations
Route::apiResource('warehouse-locations', WarehouseLocationController::class);
