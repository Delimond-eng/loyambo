<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AppController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Models\Categorie;
use App\Models\Emplacement;
use App\Models\Produit;
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
    Route::view('/licences/pricing', "licences.pricing")->name("licences.pricing");
    Route::view('/orders', "orders")->name("orders");
    Route::view('/sells', "sells")->name("sells");
    Route::view('/factures', "factures")->name("factures");
    Route::view('/serveurs', "serveurs")->name("serveurs");
    Route::view('/serveurs.activities', "serveurs_activities")->name("serveurs.activities");
    Route::view('/orders.portal', "serveur_portal")->name("orders.portal");
    Route::view('/orders.interface', "orders_interface")->name("orders.interface");
    Route::view('/products.categories', "product_categories")->name("products.categories");
    Route::get('/products.mvts', fn()=>view("products_mvts", ["produits"=>Produit::orderBy("libelle")->get(), "emplacements" => Emplacement::all()]))->name("products.mvts");
    Route::get('/products', fn()=>view("products", ["categories"=>Categorie::all()]))->name("products");

    Route::view('/tables.occuped', "tables_occuped")->name("tables.occuped");
    Route::view('/beds.occuped', "bedroom_occuped")->name("beds.occuped");
    Route::view('/tables.emplacements', "emplacements")->name("tables.emplacements");
    Route::view('/tables', "tables")->name("tables");
    Route::view('/reports.global', "reports_global")->name("reports.global");

    Route::post("day.start", [AdminController::class, "startDay"])->name("day.start")->middleware("can:ouvrir-journee");

     Route::get('/users', function(){
        $places = Emplacement::all();
        return view("users", ["emplacements"=>$places]);
    })->name("users");
    //GET ALL USER WITH LATEST LOGS
    Route::get("users.all", [AdminController::class, "getAllUsersWithLatestLog"])->name("users.all")->middleware("can:voir-utilisateurs");

    //GET ALL PERMISSIONS
    Route::get("/permissions", [AdminController::class, "getAllPermissions"])->name("users.all");
    Route::post("/user.give.access", [AdminController::class, "updateUserPermissions"])->name("user.give.access")->middleware("can:modifier-utilisateurs");
    Route::post("/user.create", [UserController::class, "createUser"])->name("user.create")->middleware("can:creer-utilisateurs");

    //PRODUCT MANAGEMENT
    Route::post("/categorie.create", [ProductController::class, "createCategory"])->name("categorie.create")->middleware("can:creer-categories");
    Route::get("/categories.all", [ProductController::class, "getAllCategories"])->name("categories.all")->middleware("can:voir-categories");
    Route::post("/product.create", [ProductController::class, "createProduct"])->name("product.create")->middleware("can:creer-produits");
    Route::post("/product.update.quantified", [ProductController::class, "updateProductQuantified"])->name("product.update.quantified")->middleware("can:modifier-produits");
    Route::get("/products.all", [ProductController::class, "getAllProducts"])->name("products.all")->middleware("can:voir-produits");
    Route::post("/mvt.create", [ProductController::class, "createStockMvt"])->name("mvt.create")->middleware("can:creer-mouvements-stock");
    Route::get("/mvts.all", [ProductController::class, "getStockMvts"])->name("products.all")->middleware("can:voir-mouvements-stock");

    //EMPLACEMENTS & TABLES MANAGEMENTS
    Route::post("/emplacement.create", [AdminController::class, "createEmplacement"])->name("emplacement.create")->middleware("can:creer-emplacements");
    Route::get("/emplacements.all", [AdminController::class, "getAllEmplacements"])->name("emplacements.all")->middleware("can:voir-emplacements");
    Route::post("/table.create", [AdminController::class, "createTable"])->name("table.create")->middleware("can:creer-tables");
    Route::get("/tables.all", [AdminController::class, "getAllTables"])->name("tables.all")->middleware("can:voir-tables");
    
    Route::post("/facture.create", [HomeController::class, "saveFacture"])->name("facture.create")->middleware("can:creer-factures");
});

