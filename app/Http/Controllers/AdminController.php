<?php

namespace App\Http\Controllers;

use App\Models\AccessAllow;
use App\Models\Currencie;
use App\Models\Emplacement;
use App\Models\Facture;
use App\Models\MouvementStock;
use App\Models\Payments;
use App\Models\RestaurantTable;
use App\Models\SaleDay;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;

class AdminController extends Controller
{
    /**
     * Fonction permettant de commencer une journée de ventes
     * @param Request $request
     * @return 
    */
    public function startDay(Request $request){
        try{
            $data = $request->validate([
                "currencie_value"=>"required|numeric"
            ]);

            $currencie = Currencie::updateOrCreate(
                ["currencie_date"=>Carbon::now()->toDateString(),],
            [
                "currencie_date"=>Carbon::now()->toDateString(),
                "currencie_value"=>$data["currencie_value"],
            ]);

            $saleDay = SaleDay::updateOrCreate(
                ["sale_date"=>Carbon::now()->toDateString()],
                [
                "sale_date"=>Carbon::now()->toDateString(),
                "start_time"=>Carbon::now()->setTimezone("Africa/Kinshasa"),
                "end_time"=>null
            ]);
            $accessAllow = AccessAllow::latest()->first();
            $accessAllow->update([
                "allowed"=>true
            ]);

            return response()->json([
                "status"=>"success",
                "result"=>[
                    "saleDay"=>$saleDay,
                    "currencie"=>$currencie,
                    "access"=>$accessAllow
                ]
            ]);

        }
        catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            return response()->json(['errors' => $errors]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['errors' => $e->getMessage()]);
        }
    }


    //GET ALL USERS 
    public function getAllUsersWithLatestLog(Request $request){
        $role = $request->query("role") ?? null;
        $query = User::with(["lastLog", "emplacement","permissions", "roles.permissions"])
                ->orderBy("name");
        if($role){
            $query->where("role", $role);
        }
        $users = $query->get();
        return response()->json([
            "status"=>"success",
            "users"=>$users
        ]);
    }
    //GET ALL SERVEURS SERVICES
    public function getAllServeursServices(Request $request)
    {
        $saleDay = SaleDay::whereNull("end_time")->latest()->first();

        $serveurs = Facture::with("user.lastLog")
            ->selectRaw("user_id, SUM(total_ttc) as total_encaisse")
            ->where("sale_day_id", $saleDay->id)
            ->whereHas("user", function ($q) {
                $q->where("role", "serveur"); // filtrer uniquement les serveurs
            })->where("statut", "payée")
            ->groupBy("user_id")
            ->get();

        return response()->json([
            "status" => "success",
            "serveurs" => $serveurs
        ]);
    }

    //GET ALL USER PERMISSION
    public function getAllPermissions(){
        $permissions = Permission::all();
        return response()->json([
            "status"=>"success",
            "permissions"=>$permissions
        ]);
    }


    //UPDATE SELECTED USER PERMISSION
    public function updateUserPermissions(Request $request)
    {
        try{
            // Validation
            $data = $request->validate([
                'user_id'=>'required|int|exists:users,id',
                'permissions' => 'required|array',
                'permissions.*' => 'exists:permissions,id'
            ]);
            $user = User::findOrFail($data["user_id"]);

            $permissionNames = Permission::whereIn('id', $data['permissions'])
                                ->pluck('name')
                                ->toArray();
            \Log::info('Permissions à synchroniser: ', $permissionNames);
            // Synchroniser les permissions
            $user->syncPermissions($permissionNames);
            return response()->json([
                'status'=>'success',
                'message' => 'Permissions mises à jour avec succès',
                'result' => $user->getAllPermissions()
            ]);
        }
        catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            return response()->json(['errors' => $errors]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['errors' => $e->getMessage()]);
        }
        
    }

    /**
     * Create Emplacement
     * @param Request $request
     * @return mixed
    */
    public function createEmplacement(Request $request){
        try{
            // Validation
            $data = $request->validate([
                "libelle"=>"required|string",
                "type"=>"required|string",
            ]);
            $data["ets_id"] = Auth::user()->ets_id;
            $emplacement = Emplacement::updateOrCreate([
                "id"=>$request->id ?? null
            ],$data);
           
            return response()->json([
                'status'=>'success',
                'message' => 'Nouveau emplacement créé avec succès !',
                'result' => $emplacement
            ]);
        }
        catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            return response()->json(['errors' => $errors]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['errors' => $e->getMessage()]);
        }
    }


    //GET ALL Emplacements with tables
    public function getAllEmplacements(){
        $emplacements = Emplacement::with(["tables", "beds"])->orderBy("libelle")->get();
        return response()->json([
            "status"=>"success",
            "emplacements"=>$emplacements
        ]);
    }

    /**
     * Create Table
     * @param Request $request
     * @return mixed
    */
    public function createTable(Request $request){
        try{
            // Validation
            $data = $request->validate([
                "tables.*.numero"=>"required|string",
                "tables.*.emplacement_id"=>"required|int|exists:emplacements,id",
                "tables.*.id"=>"nullable|int",
            ]);
            $tables = $data["tables"];
            foreach ($tables as $table) {
                if(isset($table["id"])){
                    $cdts["id"] = $table["id"];
                }
                else{
                    $cdts = [
                        "numero"=>$table["numero"],
                        "emplacement_id"=>$table["emplacement_id"]
                    ];
                }
                Log::info($cdts);
                RestaurantTable::updateOrCreate($cdts,["numero"=>$table["numero"], "emplacement_id"=>$table["emplacement_id"]]);
            }
            return response()->json([
                'status'=>'success',
                'message' => 'Nouvelle table créée avec succès !',
                'result' => 'Nouvelle table créée avec succès !',
            ]);
        }
        catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            return response()->json(['errors' => $errors]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['errors' => $e->getMessage()]);
        }
    }

    //GET ALL TABLES
    public function getAllTables(Request $request)
    {
        $placeId = $request->query("place") ?? null;

        $query = RestaurantTable::with([
            "emplacement",
            "commandes" => function ($query) {
                $query->where("statut", "!=", "payée")
                    ->with(["details.produit"]);
            }
        ])->orderBy("numero");

        if ($placeId) {
            $query->where("emplacement_id", $placeId);
        } else {
            $query->where("emplacement_id", Auth::user()->emplacement_id);
        }

        $tables = $query->get();

        return response()->json([
            "status" => "success",
            "tables" => $tables
        ]);
    }


    public function triggerTableOperation(Request $request)
    {
        $operation = $request->op;

        // 🔹 Transfert
        if ($operation === 'transfert') {
            $request->validate([
                'source_id' => 'required|integer|exists:restaurant_tables,id',
                'cible_id'  => 'required|integer|exists:restaurant_tables,id',
            ]);

            $tableSource = RestaurantTable::find($request->source_id);
            $tableCible  = RestaurantTable::find($request->cible_id);

            if ($tableSource->id === $tableCible->id) {
                return response()->json([
                    "errors" => "Impossible de transférer vers la même table."
                ]);
            }

            DB::transaction(function () use ($tableSource, $tableCible) {
                $tableSource->update(["statut" => "libre"]);
                $tableCible->update(["statut" => "occupée"]);

                // Déplacer toutes les commandes de la source vers la cible
                $tableSource->commandes()->update([
                    "table_id" => $tableCible->id
                ]);
            });

            return response()->json([
                "status" => "success",
                "result" => "Transfert effectué avec succès !"
            ]);
        }

        // 🔹 Combinaison
        if ($operation === 'combiner') {
            $request->validate([
                'table1_id' => 'required|integer|exists:restaurant_tables,id',
                'table2_id' => 'required|integer|exists:restaurant_tables,id',
            ]);

            $table1 = RestaurantTable::find($request->table1_id);
            $table2 = RestaurantTable::find($request->table2_id);

            if ($table1->id === $table2->id) {
                return response()->json([
                    "errors" => "Impossible de combiner une table avec elle-même."
                ]);
            }

            DB::transaction(function () use ($table1, $table2) {
                // Déplacer toutes les commandes de la table1 vers la table2
                $table1->commandes()->update([
                    "table_id" => $table2->id
                ]);

                // Libérer la table1
                $table1->update(["statut" => "libre"]);

                // Table2 reste occupée
                $table2->update(["statut" => "occupée"]);
            });

            return response()->json([
                "status" => "success",
                "result" => "Tables combinées avec succès !"
            ]);
        }

        return response()->json([
            "errors" => "Opération inconnue ou non supportée."
        ]);
    }
    public function libererTable(Request $request)
    {
        $table = RestaurantTable::find((int)$request->table_id);
        if($table){
            $table->update(["statut"=>"libre"]);
        }
        return response()->json([
            "status"=>"success",
            "result" => "Table liberée avec succès !"
        ]);
    }



    /* public function triggerTableOperation(Request $request)
    {
        $operation = $request->op;

        // 🔹 Transfert (inchangé)
        if ($operation === 'transfert') {
            $request->validate([
                'source_id' => 'required|integer|exists:restaurant_tables,id',
                'cible_id'  => 'required|integer|exists:restaurant_tables,id',
            ]);

            $tableSource = RestaurantTable::find($request->source_id);
            $tableCible  = RestaurantTable::find($request->cible_id);

            if ($tableSource->id === $tableCible->id) {
                return response()->json([
                    "errors" => "Impossible de transférer vers la même table."
                ]);
            }

            DB::transaction(function () use ($tableSource, $tableCible) {
                $tableSource->update(["statut" => "libre"]);
                $tableCible->update(["statut" => "occupée"]);

                $tableSource->commandes()->update([
                    "table_id" => $tableCible->id
                ]);
            });

            return response()->json([
                "status" => "success",
                "result" => "Transfert effectué avec succès !"
            ]);
        }

        // 🔹 Combinaison
        if ($operation === 'combiner') {
            $request->validate([
                'table1_id' => 'required|integer|exists:restaurant_tables,id',
                'table2_id' => 'required|integer|exists:restaurant_tables,id',
            ]);

            $table1 = RestaurantTable::find($request->table1_id);
            $table2 = RestaurantTable::find($request->table2_id);

            if ($table1->id === $table2->id) {
                return response()->json([
                    "errors" => "Impossible de combiner une table avec elle-même."
                ]);
            }
            $facturePrincipale = null;
            DB::transaction(function () use ($table1, $table2, &$facturePrincipale) {
                // Toutes les factures de table1 et table2
                $factures1 = $table1->commandes()->get();
                $factures2 = $table2->commandes()->get();

                if ($factures1->isEmpty() && $factures2->isEmpty()) {
                    return; // rien à combiner
                }

                // On choisit la facture "principale" = la plus récente de table2
                $facturePrincipale = $factures2->sortByDesc("created_at")->first();

                // Si table2 n’a pas de facture, on prend celle de table1 comme principale
                if (!$facturePrincipale && $factures1->isNotEmpty()) {
                    $facturePrincipale = $factures1->sortByDesc("created_at")->first();
                    $facturePrincipale->update(["table_id" => $table2->id]);
                }

                // Fusionner toutes les autres factures dans la principale
                $facturesAFusionner = $factures1->merge($factures2)->filter(fn($f) => $f->id !== $facturePrincipale->id);

                foreach ($facturesAFusionner as $facture) {
                    foreach ($facture->details as $detail) {
                        $existingDetail = $facturePrincipale->details()
                            ->where("produit_id", $detail->produit_id)
                            ->first();

                        if ($existingDetail) {
                            $newQuantite = $existingDetail->quantite + $detail->quantite;
                            $existingDetail->update([
                                "quantite"    => $newQuantite,
                                "total_ligne" => $newQuantite * $existingDetail->prix_unitaire,
                            ]);
                        } else {
                            $facturePrincipale->details()->create([
                                "produit_id"    => $detail->produit_id,
                                "quantite"      => $detail->quantite,
                                "prix_unitaire" => $detail->prix_unitaire,
                                "total_ligne"   => $detail->total_ligne,
                            ]);
                        }
                    }
                    // Supprimer facture fusionnée
                    $facture->delete();
                }

                // 🔹 Recalculer le montant de la facture principale
                $nouveauMontant = $facturePrincipale->details()->sum(DB::raw('quantite * prix_unitaire'));
                $facturePrincipale->update(["montant_total" => $nouveauMontant]);

                // Libérer table1
                $table1->update(["statut" => "libre"]);
                // Table2 reste occupée
                $table2->update(["statut" => "occupée"]);
            });

            return response()->json([
                "status" => "success",
                "result" => "Tables combinées avec succès !",
                "facture" => $facturePrincipale->load("details")
            ]);
        }

        return response()->json([
            "errors" => "Opération inconnue ou non supportée."
        ]);
    } */

    public function createPayment(Request $request){
        try{
            // Validation
            $data = $request->validate([
                "facture_id"=>"required|int|exists:factures,id",
                "user_id"=>"nullable|int|exists:users,id",
                "mode"=>"nullable|string",
                "mode_ref"=>"nullable|string",
            ]);

            $facture = Facture::find((int)$data["facture_id"]);
            if($facture->user_id !== Auth::user()->id && Auth::user()->role==="serveur"){
                return response()->json([
                    "errors"=>"Vous ne pouvez pas servir cette commande !"
                ]);
            }
            $saleDay = SaleDay::whereNull("end_time")->latest()->first();
            $userId = $data["user_id"] ?? Auth::id();

            if($facture){
                $payment = Payments::create([
                    "amount"=>$facture->total_ttc,
                    "devise"=>"CDF",
                    "mode"=>$data["mode"] ?? "cash",
                    "mode_ref"=>$data["mode_ref"] ?? null,
                    "pay_date"=>Carbon::now(tz:'Africa/Kinshasa'),
                    "emplacement_id"=>$facture->emplacement_id,
                    "facture_id"=>$facture->id,
                    "table_id"=>$facture->table_id,
                    "user_id"=>$userId,
                    "sale_day_id"=>$saleDay->id
                ]);
                if($payment){
                    foreach($facture->details as $detail){
                        MouvementStock::create([
                            "produit_id"=>$detail->produit_id,
                            "numdoc"=>$facture->id,
                            "type_mouvement"=>"vente",
                            "quantite"=>$detail->quantite,
                            "sale_day_id"=>$saleDay->id,
                            "date_mouvement"=>Carbon::now(tz:"Africa/Kinshasa"),
                            "user_id"=>$userId
                        ]);
                    }
                    $facture->update(["statut"=>"payée"]);
                }
            }

            return response()->json([
                'status'=>'success',
                'message' => 'Nouvelle table créée avec succès !',
                'result' => $payment,
            ]);
        }
        catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            return response()->json(['errors' => $errors]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['errors' => $e->getMessage()]);
        }
    }


    //GLOBAL REPORT GROUPED BY USER
    public function viewGlobalReports(Request $request)
    {
        $saleDays = SaleDay::with([
            "sales" => function ($query) {
                $query->where("type_mouvement", "vente")
                    ->with([
                        "produit",
                        "user" => function($q){
                            $q->whereNotIn("role", ["serveur", "cuisinier"]);
                        }
                    ]);
            },
            "factures" => function ($query) {
                $query->where("statut", "payée")->with("details");
            }
        ])
        ->orderByDesc("sale_date")
        ->get();

        $reports = collect();

        foreach ($saleDays as $saleDay) {
            // Grouper les ventes par utilisateur
            $groupedSales = $saleDay->sales
                ->filter(fn($s) => $s->user) // retirer les ventes sans user ou filtrés
                ->groupBy("user_id");
            foreach ($groupedSales as $userId => $sales) {
                $user = $sales->first()->user;

                // Calcul du total_factures à partir des ventes
                $totalFactures = $sales->reduce(function($carry, $sale) {
                    return $carry + ($sale->produit->prix_unitaire ?? 0) * $sale->quantite;
                }, 0);
                // Mouvements de vente
                $userSales = $sales
                    ->map(fn($s) => [
                        "numdoc" => $s->numdoc,
                        "produit" => $s->produit->name ?? null,
                        "quantite" => $s->quantite,
                        "prix_unitaire" => $s->produit->prix_unitaire ?? 0,
                        "total" => ($s->produit->prix_unitaire ?? 0) * $s->quantite,
                        "date_mouvement" => optional($s->date_mouvement)->format("d/m/Y H:i")
                    ]);

                $reports->push([
                    "sale_day"       => $saleDay,
                    "user"           =>$user,
                    "total_factures" => $totalFactures,
                    "sales"          => $userSales,
                ]);
            }
        }

        return response()->json([
            "status" => "success",
            "reports" => $reports->values()
        ]);
    }



}
