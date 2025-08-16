<?php

use App\Http\Controllers\AppController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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
//Auth::routes();
Auth::routes();
Route::post("/create.account", [UserController::class, "createEtsAccount"])->name("create.account");
Route::middleware(["auth", "check.day.access"])->group(function(){
    Route::view('/', "dashboard")->name("home");
    Route::view('/orders', "orders")->name("orders");
    Route::view('/sells', "sells")->name("sells");
    Route::view('/users', "users")->name("users");
    Route::view('/factures', "factures")->name("factures");
    Route::view('/serveurs', "serveurs")->name("serveurs");
    Route::view('/serveurs.activities', "serveurs_activities")->name("serveurs.activities");
    Route::view('/orders.portal', "serveur_portal")->name("orders.portal");
    Route::view('/products.categories', "product_categories")->name("products.categories");
    Route::view('/products.mvts', "products_mvts")->name("products.mvts");
    Route::view('/products', "products")->name("products");

    Route::view('/tables.occuped', "tables_occuped")->name("tables.occuped");
    Route::view('/tables.emplacements', "emplacements")->name("tables.emplacements");
    Route::view('/tables', "tables")->name("tables");
    Route::view('/reports.global', "reports_global")->name("reports.global");
});

