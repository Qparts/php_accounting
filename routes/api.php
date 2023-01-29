<?php

use App\Http\Controllers\Api\BillsController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\InventoryController;
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
    Route::post('vendors', [VendorController::class, 'store']);
    Route::get('vendors', [VendorController::class, 'listVendors']);
    Route::get('vendor/{id}', [VendorController::class, 'getVendor']);

    //invoice
    Route::post('invoice', [InvoiceController::class, 'createInvoice']);
    Route::post('sales/customer-payment', [InvoiceController::class, 'customerPayment']);
    Route::post('sales/credit-note', [InvoiceController::class, 'createCreditNote']);

    //product
    Route::post('products', [ProductController::class, 'createProduct']);
    Route::post('categories', [ProductController::class, 'createCategory']);
    Route::get('products/{sku}', [ProductController::class, 'getProduct']);
    Route::get('product-name/{name}', [ProductController::class, 'getProductByName']);

    //user
    Route::post('register', [ApiController::class, 'register']);
    Route::get('current-user', [ApiController::class, 'getDataFromLoggedInUser']);

    //inventory / warehouses

    Route::post('inventories',[InventoryController::class,'store']);
    Route::get('inventories',[InventoryController::class,'show']);
    Route::post('adjust-inventory',[InventoryController::class,'purchaseProductsForInventory']);

    //bills
    Route::post('bills',[BillsController::class,'store']);
    Route::get('bills',[BillsController::class,'listBills']);
    Route::get('bill/{id}',[BillsController::class,'getBill']);

    Route::post('bills/{id}/allocations ',[BillsController::class,'debitNote']);


});
