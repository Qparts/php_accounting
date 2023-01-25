<?php

use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\ProductController;
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




    //Customer
    Route::post('sales/customer', [CustomerController::class, 'store']);

    //vendor
    Route::post('vendor', [VendorController::class, 'store']);

    //invoice
    Route::post('invoice', [InvoiceController::class, 'createInvoice']);
    Route::post('sales/customer-payment', [InvoiceController::class, 'customerPayment']);

    //product
    Route::post('products', [ProductController::class, 'createProduct']);

    //user
    Route::post('register', [ApiController::class, 'register']);

    Route::get('get-data', [ApiController::class, 'getDataFromLoggedInUser']);

});
