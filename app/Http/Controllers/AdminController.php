<?php

namespace App\Http\Controllers;

use App\Models\AccessAllow;
use App\Models\Chambre;
use App\Models\Client;
use App\Models\Currencie;
use App\Models\Emplacement;
use App\Models\Facture;
use App\Models\MouvementStock;
use App\Models\Payments;
use App\Models\Reservation;
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
            $user = Auth::user();

            $currencie = Currencie::updateOrCreate(
                ["currencie_date"=>Carbon::now()->toDateString(),],
            [
                "currencie_date"=>Carbon::now()->toDateString(),
                "currencie_value"=>$data["currencie_value"],
                "ets_id"=>$user->ets_id
            ]);

            $saleDay = SaleDay::updateOrCreate(
                ["sale_date"=>Carbon::now()->toDateString()],
                [
                "sale_date"=>Carbon::now()->toDateString(),
                "start_time"=>Carbon::now()->setTimezone("Africa/Kinshasa"),
                "ets_id"=>$user->ets_id,
                "end_time"=>null
            ]);
            $accessAllow = AccessAllow::where("ets_id", $user->ets_id)->latest()->first();
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
        catch (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            return response()->json(['errors' => "Action non autorisée !"]);
        }
    }


    //GET ALL USERS 
    public function getAllUsersWithLatestLog(Request $request){
        $role = $request->query("role") ?? null;
        $user = Auth::user();
        $query = User::with(["lastLog", "emplacement","permissions", "roles.permissions"])
                ->orderBy("name")->where("ets_id", $user->ets_id);
        if($role){
            $query->where("role", $role);
        }
        $users = $query->get();
        return response()->json([
            "status"=>"success",
            "users"=>$users
        ]);
    }

    //Voir tous les serveurs
    public function getAllServeurs(Request $request){
        $user = Auth::user();
        $query = User::with(["lastLog", "emplacement","permissions", "roles.permissions"])
                ->orderBy("name")->where("ets_id", $user->ets_id)->where("role", "serveur");
        $users = $query->get();
        return response()->json([
            "status"=>"success",
            "users"=>$users
        ]);
    }
    //GET ALL SERVEURS SERVICES
    public function getAllServeursServices(Request $request)
    {
        $user = Auth::user();
        $saleDay = SaleDay::where("ets_id", $user->ets_id)->whereNull("end_time")->latest()->first();

        $req = Facture::with("user.lastLog")
            ->selectRaw("user_id, SUM(total_ttc) as total_encaisse")
            ->where("sale_day_id", $saleDay->id)
            ->whereHas("user", function ($q) {
                $q->where("role", "serveur"); 
            })
            ->where("statut", "payée")
            ->where("ets_id", $user->ets_id);
        if($user->role !== "admin" && $user->emplacement_id){
            $req->where("emplacement_id", $user->emplacement_id);
        }
        $serveurs = $req->groupBy("user_id")->get();
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
        catch (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            return response()->json(['errors' => "Action non autorisée !"]);
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
        catch (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            return response()->json(['errors' => "Action non autorisée !"]);
        }
    }


    //GET ALL Emplacements with tables
    public function getAllEmplacements(){
        $user = Auth::user();
        $emplacements = Emplacement::with(["tables", "beds"])
            ->where("ets_id", $user->ets_id)
            ->orderBy("libelle")->get();
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
                "numero"=>"required|string",
                "emplacement_id"=>"required|int|exists:emplacements,id",
                "prix"=>"nullable|numeric",
                "prix_devise"=>"nullable|string",
                "id"=>"nullable|int",
            ]);

            $user = Auth::user();
            if(isset($data["id"])){
                $cdts["id"] = $data["id"];
            }
            else{
                $cdts = [
                    "numero"=>$data["numero"],
                    "emplacement_id"=>$data["emplacement_id"]
                ];
            }
            RestaurantTable::updateOrCreate($cdts,[
                "numero"=>$data["numero"], 
                "emplacement_id"=>$data["emplacement_id"],
                "prix"=>$data["prix"] ?? null,
                "prix_devise"=>$data["prix_devise"] ?? null,
                "ets_id"=>$user->ets_id
            ]);
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
        } catch (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            return response()->json(['errors' => "Action non autorisée !"]);
        }
    }

    //GET ALL TABLES
    public function getAllTables(Request $request)
    {
        $placeId = $request->query("place") ?? null;
        $user = Auth::user();

        $query = RestaurantTable::with([
            "emplacement",
            "commandes" => function ($query) {
                $query->where("statut", "!=", "payée")
                    ->with(["details.produit"]);
            }
        ])->where("ets_id", $user->ets_id);

        if ($placeId) {
            $query->where("emplacement_id", $placeId);
        } 
        $tables = $query->orderByDesc("emplacement_id")->get();

        return response()->json([
            "status" => "success",
            "tables" => $tables
        ]);
    }
    public function getAllChambres(Request $request)
    {
        $user = Auth::user();

        $emplacement = Emplacement::with("beds")->where("id", $user->emplacement_id)
        ->whereHas("beds")->first();
        return response()->json([
            "status" => "success",
            "chambres" => $emplacement->beds ?? []
        ]);
    }


    public function reserver(Request $request)
    {

        try{
            $data = $request->validate([
                'client.nom'  => 'required|string',
                'client.telephone'  => 'nullable|string',
                'client.email'  => 'nullable|string',
                'client.identite'  => 'required|string',
                'client.identite_type'  => 'required|string',
                'chambre_id' => 'nullable|exists:chambres,id',
                'table_id'   => 'nullable|exists:restaurant_tables,id',
                'date_debut' => 'required|date|after_or_equal:today',
                'date_fin'   => 'required|date|after:date_debut',
            ]);

            $clientData = $data["client"];

            $client = Client::where("identite", $clientData["identite"])->first();

            if(!$client){
                $client = Client::create([
                    "nom"=>$clientData["nom"],
                    "telephone"=>$clientData["telephone"],
                    "email"=>$clientData["email"],
                    "identite"=>$clientData["identite"],
                    "identite_type"=>$clientData["identite_type"],
                ]);
            }

            if (!$data['chambre_id'] && !$data['table_id']) {
                return response()->json(['message' => 'Veuillez sélectionner une chambre ou une table.'], 422);
            }

            return DB::transaction(function () use ($data, $client) {
                // Vérifier la disponibilité
                $query = Reservation::where('statut', 'confirmée')
                    ->where(function ($q) use ($data) {
                        $q->whereBetween('date_debut', [$data['date_debut'], $data['date_fin']])
                        ->orWhereBetween('date_fin', [$data['date_debut'], $data['date_fin']])
                        ->orWhere(function ($q2) use ($data) {
                            $q2->where('date_debut', '<=', $data['date_debut'])
                                ->where('date_fin', '>=', $data['date_fin']);
                        });
                    });

                if ($data['chambre_id']) {
                    $query->where('chambre_id', $data['chambre_id']);
                } else {
                    $query->where('table_id', $data['table_id']);
                }

                if ($query->exists()) {
                    return response()->json(['message' => 'Cette ressource est déjà réservée sur cette période.'], 422);
                }
                // Créer la réservation
                $reservation = Reservation::create([
                    'chambre_id' => $data['chambre_id'] ?? null,
                    'table_id'   => $data['table_id'] ?? null,
                    'client_id'  => $client->id,
                    'date_debut' => $data['date_debut'],
                    'date_fin'   => $data['date_fin'],
                    'statut'     => 'confirmée',
                    'ets_id'     => auth()->user()->ets_id ?? null,
                ]);
                // Mettre à jour le statut de la ressource
                if ($data['chambre_id']) {
                    Chambre::where('id', $data['chambre_id'])->update(['statut' => 'réservée']);
                } else {
                    RestaurantTable::where('id', $data['table_id'])->update(['statut' => 'réservée']);
                }
                return response()->json([
                    'message' => 'Réservation créée avec succès.',
                    'reservation' => $reservation
                ], 201);
            });
        }
        catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            return response()->json(['errors' => $errors]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['errors' => $e->getMessage()]);
        }
        catch (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            return response()->json(['errors' => "Action non autorisée !"]);
        }
        
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

    public function createPayment(Request $request){
        try{
            // Validation
            $data = $request->validate([
                "facture_id"=>"required|int|exists:factures,id",
                "user_id"=>"nullable|int|exists:users,id",
                "mode"=>"nullable|string",
                "mode_ref"=>"nullable|string",
            ]);
            $user = Auth::user();

            $facture = Facture::find((int)$data["facture_id"]);

            if (Auth::user()->role === "serveur" && $facture->user_id !== Auth::id()) {
                return response()->json([
                    "errors" => "Vous ne pouvez pas servir cette commande !"
                ]);
            }
            $saleDay = SaleDay::whereNull("end_time")->where("ets_id", $user->ets_id)->latest()->first();
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
                    "sale_day_id"=>$saleDay->id,
                    "ets_id"=>$user->ets_id,
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
                            "user_id"=>$userId,
                            "ets_id"=>$user->ets_id,
                            "emplacement_id"=>$facture->emplacement_id,
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
        catch (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            return response()->json(['errors' => "Action non autorisée !"]);
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
        ])->where("ets_id", Auth::user()->ets_id)
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
