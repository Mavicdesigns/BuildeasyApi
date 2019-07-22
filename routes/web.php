<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/home', 'HomeController@index')->name('home');


Route::get('/home', 'HomeController@index')->name('home');




Route::group(['guard' => 'auth:api'], function(){

    Route::get('/suppliers', 'SupplierController@index')->middleware('ApiKey');
    Route::get('/suppliers/{supplier_id}', 'SupplierController@getSupplier')->middleware('ApiKey');
    Route::post('/suppliers/createNewSupplier', 'SupplierController@createSupplier')->middleware('ApiKey');


//Order Routes
    Route::get('/admin/allOrders', 'OrdersController@index')->middleware('ApiKey');
    Route::get('/allOrders', 'OrdersController@supplierOrders')->middleware('ApiKey');
    Route::get('/allOrders/unverified', 'OrdersController@unverifiedOrders')->middleware('ApiKey');
    Route::get('/allOrders/processed', 'OrdersController@processedOrders')->middleware('ApiKey');
    Route::get('/allOrders/awaitingDelivery', 'OrdersController@awaitingDelivery')->middleware('ApiKey');
    Route::get('/allOrders/completedOrders', 'OrdersController@completedOrders')->middleware('ApiKey');
    Route::get('/allOrders', 'OrdersController@index')->middleware('ApiKey');
    Route::get('/allOrders/{order_id}/reject', 'OrdersController@rejectOrder')->middleware('ApiKey');
    Route::get('/allOrders/{order_id}', 'OrdersController@getSingleOrder')->middleware('ApiKey');
    Route::post('/allOrders/{order_id}/verify', 'OrdersController@verifyToken')->middleware('ApiKey');
    Route::get('/allOrders/{order_id}/accept', 'OrdersController@acceptOrder')->middleware('ApiKey');
    Route::get('/allOrders/{order_id}/deliver', 'OrdersController@deliverOrder')->middleware('ApiKey');
    Route::post('/createOrder', 'OrdersController@createOrder')->middleware('ApiKey');


//Product Routes
    Route::post('/products/createProduct', 'ProductsController@createProduct')->middleware('ApiKey');
    Route::get('/products/deleteProduct/{product_id}', 'ProductsController@deleteProduct')->middleware('ApiKey');
    Route::post('/products/updateProduct', 'ProductsController@updateProduct')->middleware('ApiKey');
    Route::get('/products', 'ProductsController@index')->middleware('ApiKey');
    Route::get('/products/{product_id}', 'ProductsController@getCurrentId')->middleware('ApiKey');
    Route::get('/category/', 'ProductsController@getCategoryProduct')->middleware('ApiKey');
    Route::get('/category/getProductInCategory', 'ProductsController@getProductInCategory')->middleware('ApiKey');
    Route::get('/category/getProducts', 'ProductsController@getProductByCategory')->middleware('ApiKey');
    Route::get('/products/getSupplierProducts/{supplier_id}', 'ProductsController@getSupplierProduct')->middleware('ApiKey');

//Customers Routes
    Route::get('/customers', 'CustomersController@index')->middleware('ApiKey');
    Route::get('/customers/{customer_id}', 'CustomersController@getCustomer')->middleware('ApiKey');
    Route::get('/customers/data/orders', 'CustomersController@getCustomersOrders')->middleware('ApiKey');
    Route::post('/customers/users/authenticate', 'CustomersController@authenticate')->middleware('ApiKey');
    Route::get('/customers/users/getAuthenticated', 'CustomersController@getAuthenticated')->middleware('ApiKey');
    Route::post('/customers/users/register', 'CustomersController@registerUser')->middleware('ApiKey');
    Route::post('/customers/register/{token}', 'CustomersController@registerCustomer')->middleware('ApiKey');


    Route::post('/api/getCloserSupplier', 'ApiController@getCloserSupplier')->middleware('ApiKey');
    Route::post('/api/uploadImage', 'ApiController@UploadImage')->middleware('ApiKey');
    Route::post('/api/createUser', 'ApiController@createUser')->middleware('ApiKey');
    Route::get('/api/getSupplierImages', 'ApiController@getSupplierImages')->middleware('ApiKey');
    Route::get('/api/sendSms', 'ApiController@sendSms');
    Route::post('/testAngular', 'ApiController@testAngular')->middleware('ApiKey');


});


Auth::routes(['verify' => true]);

Route::get('/home', 'HomeController@index')->middleware('verified');
