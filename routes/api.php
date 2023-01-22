<?php

use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\VendorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
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
Route::post('login', 'ApiController@login');

Route::group(['middleware' => ['auth:sanctum']], function () {

    Route::post('logout', [ApiController::class, 'logout']);
    Route::get('get-projects', [ApiController::class, 'getProjects']);
    Route::post('add-tracker', [ApiController::class, 'addTracker']);
    Route::post('stop-tracker', [ApiController::class, 'stopTracker']);
    Route::post('upload-photos', [ApiController::class, 'uploadImage']);

    Route::get('get-data', [ApiController::class, 'getData']);

    //Customer
    Route::post('customer', [CustomerController::class, 'store']);

    //vendor
    Route::post('vendor', [VendorController::class, 'store']);

    //invoice
    Route::get('list/category', [InvoiceController::class, 'listCategory']);
    Route::get('list/invoice/number', [InvoiceController::class, 'listInvoiceNumber']);
    Route::get('product/invoice', [InvoiceController::class, 'productService']);
    Route::post('invoice', [InvoiceController::class, 'createInvoice']);
});
