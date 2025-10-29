<?php

use App\Models\User;
use App\Models\Chambre;
use App\Models\Facture;
use App\Models\Produit;
use App\Models\SaleDay;
use App\Models\Payments;
use App\Models\Categorie;
use App\Models\Emplacement;
use App\Models\MouvementStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\report\financeController;
use App\Http\Controllers\report\ProduitController;
use App\Http\Controllers\report\CommandeController;
use App\Http\Controllers\report\InventaireController;
use App\Http\Controllers\Commandes\commandesController;
use App\Http\Controllers\report\ConsommationController;
use App\Http\Controllers\report\VentreSrviceController;
use App\Http\Controllers\report\PerfomanceUserController;
use App\Http\Controllers\reservation\ReservationController;
use App\Http\Controllers\reservation\ChambrelibreController;

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
    Route::get('/licence.payment/{ets_id}', [UserController::class, 'redirectToPayment'])->name('licence.payment');
    Route::view('/', "home")->name("home");
    Route::view('/dashboard', "dashboard")->name("dashboard");
    Route::post("day.start", [AdminController::class, "startDay"])->name("day.start")->middleware("can:ouvrir-journee");
    Route::post("day.close", [AdminController::class, "closeDay"])->name("day.close")->middleware("can:cloturer-journee");
    Route::view('/licences/pricing', "licences.pricing")->name("licences.pricing");

    Route::view('/orders', "orders")->name("orders");
    Route::get('/sells', fn()=>view("sells",["serveurs"=>User::where("ets_id", Auth::user()->ets_id)->get(), "saleDay"=>SaleDay::whereNull("end_time")->where("ets_id", Auth::user()->ets_id)->latest()->first()]))->name("sells");
    Route::view('/factures', "factures")->name("factures");
    Route::view('/serveurs', "serveurs")->name("serveurs");
    Route::view('/serveurs.activities', "serveurs_activities")->name("serveurs.activities");
    Route::view('/orders.portal', "serveur_portal")->name("orders.portal");
    Route::view('/orders.interface', "orders_interface")->name("orders.interface");
    Route::view('/products.categories', "product_categories")->name("products.categories");
    Route::get('/products.mvts', fn()=>view("products_mvts", ["produits"=>Produit::orderBy("libelle")->where("ets_id", Auth::user()->ets_id)->get(), "emplacements" => Emplacement::where("ets_id", Auth::user()->ets_id)->get()]))->name("products.mvts");
    Route::get('/fiche_stock', function () {

    // Récupération des produits avec leurs mouvements de stock + emplacement
    $produits = Produit::where('ets_id', Auth::user()->ets_id)
        ->orderBy('libelle')
        ->get();

        foreach ($produits as $produit) {
            // Regrouper les mouvements du produit par type
            $mouvements = MouvementStock::select(
                    'produit_id',
                    'emplacement_id',
                    'type_mouvement',
                    DB::raw('SUM(quantite) as total')
                )
                ->where('produit_id', $produit->id)
                ->groupBy('produit_id', 'emplacement_id', 'type_mouvement')
                ->with('emplacement:id,libelle')
                ->get();

            // On peut prendre l’emplacement principal (ex. du premier mouvement)
            $produit->emplacement = $mouvements->first()->emplacement->libelle ?? '-';

            // Calculs par type
            $entree = $mouvements->firstWhere('type_mouvement', 'entrée')->total ?? 0;
            $sortie = $mouvements->firstWhere('type_mouvement', 'sortie')->total ?? 0;

            // Totaux
            $produit->total_entree = $entree;
            $produit->total_sortie = $sortie;
            $produit->solde = ($produit->qte_init ?? 0) + $entree - $sortie;
        }

        return view('fiche_stock', compact('produits'));
    })->name('fiche_stock');
    Route::get('/products', fn()=>view("products", ["categories"=>Categorie::where("ets_id", Auth::user()->ets_id)->get()]))->name("products");

    Route::view('/tables.occuped', "tables_occuped")->name("tables.occuped");
    Route::view('/beds.occuped', "bedroom_occuped")->name("beds.occuped");
    Route::view('/tables.emplacements', "emplacements")->name("tables.emplacements");
    Route::view('/tables', "tables")->name("tables");
    Route::view('/reports.global', "reports_global")->name("reports.global");

     Route::get('/users', function(){
        $places = Emplacement::where("ets_id", Auth::user()->ets_id)->get();
        return view("users", ["emplacements"=>$places]);
    })->name("users");
    //GET ALL USER WITH LATEST LOGS
    Route::get("users.all", [AdminController::class, "getAllUsersWithLatestLog"])->name("users.all")->middleware("can:voir-utilisateurs");
    Route::get("serveurs.all", [AdminController::class, "getAllServeurs"])->name("serveurs.all")->middleware("can:voir-serveurs");
    Route::get("/serveurs.services", [AdminController::class, "getAllServeursServices"])->name("serveurs.services");

    //GET ALL PERMISSIONS
    Route::get("/permissions", [AdminController::class, "getAllPermissions"])->name("users.all");
    Route::post("/user.give.access", [AdminController::class, "updateUserPermissions"])->name("user.give.access")->middleware("can:modifier-utilisateurs");
    Route::post("/user.create", [UserController::class, "createUser"])->name("user.create")->middleware("can:creer-utilisateurs");

    //==========PRODUCT MANAGEMENT===============//
    Route::post("/categorie.create", [ProductController::class, "createCategory"])->name("categorie.create")->middleware("can:creer-categories");
    Route::get("/categories.all", [ProductController::class, "getAllCategories"])->name("categories.all")->middleware("can:voir-categories");
    Route::post("/product.create", [ProductController::class, "createProduct"])->name("product.create")->middleware("can:creer-produits");
    Route::post("/product.update.quantified", [ProductController::class, "updateProductQuantified"])->name("product.update.quantified")->middleware("can:modifier-produits");
    Route::get("/products.all", [ProductController::class, "getAllProducts"])->name("products.all")->middleware("can:voir-produits");
    Route::post("/mvt.create", [ProductController::class, "createStockMvt"])->name("mvt.create")->middleware("can:creer-mouvements-stock");
    Route::get("/mvts.all", [ProductController::class, "getStockMvts"])->name("products.all")->middleware("can:voir-mouvements-stock");

    ///==========EMPLACEMENTS & TABLES MANAGEMENTS=======//
    Route::post("/emplacement.create", [AdminController::class, "createEmplacement"])->name("emplacement.create")->middleware("can:creer-emplacements");
    Route::get("/emplacements.all", [AdminController::class, "getAllEmplacements"])->name("emplacements.all")->middleware("can:voir-emplacements");
    Route::post("/table.create", [AdminController::class, "createTable"])->name("table.create")->middleware("can:creer-tables");
    Route::get("/tables.all", [AdminController::class, "getAllTables"])->name("tables.all")->middleware("can:voir-tables");
    Route::post("/table.operation", [AdminController::class, "triggerTableOperation"])->name("table.operation")->middleware("can:voir-tables");
    Route::post("/table.liberer", [AdminController::class, "libererTable"])->name("table.liberer");
    Route::post("/cmd.servir", [AdminController::class, "servirCommande"])->name("cmd.servir");
    Route::post("/chambre.status", [AdminController::class, "updateBedRoomStatus"])->name("chambre.status");
    
    
    ///==========PAYMENT & INVOICE=============//
    Route::post("/payment.create", [AdminController::class, "createPayment"])->name("payment.create");
    Route::get("/reports.all", [AdminController::class, "viewGlobalReports"])->name("reports.all");
    Route::get("/report.detail", [AdminController::class, "showDaySaleFacturesByCaissier"])->name("report.detail");
    Route::post("/facture.create", [HomeController::class, "saveFacture"])->name("facture.create")->middleware("can:creer-factures");
    Route::get("/factures.all", [HomeController::class, "getAllFacturesCmds"])->name("factures.all")->middleware("can:voir-factures");
    Route::get("/sells.all", [HomeController::class, "getAllSells"])->name("sells.all")->middleware("can:voir-ventes");
    Route::get("/counts.all", [HomeController::class, "dashboardCounter"])->name("counts.all");
    
    
    //============Module pour les hotel===================//
    Route::view("/bedroom.reserve", "hotel_reservation")->name("bedroom.reserve")->can("voir-chambres");
    Route::post("/reservation.action", [AdminController::class, "reserverChambreOrTable"])->name("reservation.action");
    Route::get("/chambres.all", [AdminController::class, "getAllChambres"])->name("chambres.all")->can("voir-chambres");
    //==================Module des rapports===================================//
    Route::get("/reports.service.vente", [VentreSrviceController::class, "index"])->name("reports.service.vente");

    Route::get("/reports/service/vente/emplacement/{emplacement_id}", [VentreSrviceController::class, "showEmplacementSales"])->name("reports.service_sales.emplacement");
    Route::get("/reports/service/vente/details/{id_saleDay}/{emplacement_id}", [VentreSrviceController::class, "showSaleDetails"])->name("reports.service.vente.details");
    //reports.performance
    Route::get("/reports.performance", [PerfomanceUserController::class, "index"])->name("reports.performance");
    //reports.produits
    Route::get("/reports.produits", [ProduitController::class, "index"])->name("reports.produits");
    Route::get('/reports/produits-plus-vendus/{emplacement_id}', [ProduitController::class, 'showProduitsPlusVendus'])->name('reports.produits.plusVendus.details');
    //reports.commandes
    Route::get("/reports.commandes", [CommandeController::class, "index"])->name("reports.commandes");
    // Route::get('/api/commandes/{commande}/details', [ReportController::class, 'getCommandeDetails']);
    Route::get('/reports/commandes/{id}', [CommandeController::class, 'getCommandeDetails'])->name('reports.commandes.details');
    //reports.inventaires
    Route::get('/reports.inventaires', [InventaireController::class, 'index'])->name('reports.inventaires');
    //reports.stocks
    Route::get('/reports.stocks', [InventaireController::class, 'stocks'])->name('reports.stocks');
    //reports.Mouvements
    Route::get('/reports.Mouvements', [InventaireController::class, 'mouvementstock'])->name('reports.Mouvements');
    //reports.finances
    Route::get('/reports.finances', [financeController::class, 'finances'])->name('reports.finances');
    Route::get('/reports/payment-details/{id}', [financeController::class, 'getPaymentDetails'])->name('reports.payment-details');
    //=============Reservation Hotel =============//
    //Reservations
    Route::get('/Reservations', [ReservationController::class, "viewReservations"])->name("Reservations");
    //reservation.created
    Route::get('/reservation.created', [ReservationController::class, "createReservationView"])->name("reservation.created");
    //reservation.create
    Route::get('/reservation/create/{chambre_id}', [ReservationController::class, "createReservation"])->name("reservation.create");
    //reservation.store
    Route::post('/reservation/store', [ReservationController::class, "storeReservation"])->name("reservation.store");
    //inventaires.create
    Route::get('/inventaires.create', [InventaireController::class, 'create'])->name('inventaires.create');
    Route::post('/inventaire/store', [InventaireController::class, 'store'])->name('inventaire.store');
    //inventaire.historiques
    Route::get('/inventaire.historiques', [InventaireController::class, 'historiques'])->name('inventaire.historiques');
    Route::get('/inventaire/{id}/reajuster', [InventaireController::class, 'showReajustement'])->name('inventaire.reajuster');

    // Route pour traiter le réajustement
    Route::post('/inventaire/{id}/reajuster', [InventaireController::class, 'processReajustement'])->name('inventaire.process-reajustement');
    //reservations.edit
    Route::get('/reservations.edit/{id}', [ReservationController::class, "editReservationView"])->name("reservations.edit"); 
    Route::put('/reservation/{reservation_id}/update', [ReservationController::class, 'updateReservation'])->name('reservation.update');
    //reservations.paie
    Route::get('/reservations.paie/{id}', [ReservationController::class, "payReservationView"])->name("reservations.paie"); 
    //reservation.occupe.chambre
    Route::get('/reservation/occupe/chambre/{id}', [ReservationController::class, "occupeChambre"])->name("reservation.occupe.chambre");  
    //reservation.delete
    Route::get('/reservation/annulee/{id}', [ReservationController::class, "annuleReseervation"])->name("reservation.delete"); 
    //reservation.autorise
    Route::get('/reservation/reactivee/{id}', [ReservationController::class, "reactiveReseervation"])->name("reservation.autorise"); 
    //reservations.see
    Route::get('/reservation/voir/{id}', [ReservationController::class, "voirReseervation"])->name("reservations.see");
    //Reservations.libres
    Route::get('/Reservations.libres', [ChambrelibreController::class, "chambreLibre"])->name("Reservations.libres");
    //Reservations.occupees
    Route::get('/Reservations.occupees', [ChambrelibreController::class, "chambreOccupee"])->name("Reservations.occupees");
    //Reservations.reserve
    Route::get('/Reservations.reserve', [ChambrelibreController::class, "chambreReserve"])->name("Reservations.reserve");
    //commandes
    Route::get('/commandes', [commandesController::class, "index"])->name("commandes");
    //servir.ok
    Route::get('/commandes/servir/{id}', [commandesController::class, "servir"])->name("servir.ok");
});

