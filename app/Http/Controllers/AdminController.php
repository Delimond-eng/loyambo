<?php

namespace App\Http\Controllers;

use App\Models\AccessAllow;
use App\Models\CaisseReport;
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
use App\Models\UserLog;
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
                "sale_date"=>Carbon::now(tz: "Africa/Kinshasa")->toDateString(),
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

    public function closeDay()
    {
        try {
            $user = Auth::user();
            // 1. Vérifier qu'il y a une journée ouverte
            $saleDay = SaleDay::whereNull("end_time")
                ->where("ets_id", $user->ets_id)
                ->latest()
                ->first();

            if (!$saleDay) {
                return response()->json([
                    "errors" => "Aucune journée ouverte à clôturer."
                ]);
            }

            // 2. Récupérer tous les serveurs ayant fait des factures dans cette journée
            $serveursAvecFactures = Facture::where("sale_day_id", $saleDay->id)
                ->pluck("user_id")
                ->unique();

            // 3. Récupérer tous les serveurs ayant déjà un rapport de caisse
            $serveursAvecRapport = CaisseReport::where("sale_day_id", $saleDay->id)
                ->pluck("serveur_id")
                ->unique();

            // 4. Identifier les serveurs qui n'ont pas encore de rapport
            $serveursManquants = $serveursAvecFactures->diff($serveursAvecRapport);

            if ($serveursManquants->isNotEmpty()) {
                // Charger les infos des serveurs manquants
                $serveursInfos = User::with("emplacement")->whereIn("id", $serveursManquants)->get();
                return response()->json([
                    "status" => "failed",
                    "message" => "Impossible de clôturer, serveurs connectés !",
                    "serveurs" => $serveursInfos
                ]);
            }
            // 5. Tout est OK → clôturer la journée
            $saleDay->update([
                "end_time" => Carbon::now()->setTimezone("Africa/Kinshasa")
            ]);

            // 6. Mettre à jour les logs utilisateurs
            UserLog::where("sale_day_id", $saleDay->id)
                ->whereNull("logged_out_at")
                ->update([
                    "logged_out_at" => Carbon::now(),
                    "status" => "offline"
                ]);
            // 7. Bloquer l'accès
            $accessAllow = AccessAllow::where("ets_id", $user->ets_id)->latest()->first();
            if ($accessAllow) {
                $accessAllow->update(["allowed" => false]);
            }
            return response()->json([
                "status" => "success",
                "message" => "La journée a été clôturée avec succès.",
                "saleDay" => $saleDay
            ]);

        }catch (\Illuminate\Validation\ValidationException $e) {
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
            ->selectRaw("user_id, SUM(total_ttc) as total_encaisse, COUNT(id) as total_ticket")
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
        $emplacements = Emplacement::with(["tables", "chambres"])
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
                "type"=>"nullable|string",
                "capacite"=>"nullable|string",
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

            $emplacement = Emplacement::find($data["emplacement_id"]);


            if($emplacement->type === 'hôtel'){
                $result = Chambre::updateOrCreate(["numero"=>$data["numero"]],[
                    "numero"=>$data["numero"], 
                    "prix"=>$data["prix"],
                    "prix_devise"=>$data["prix_devise"],
                    "type"=>$data["type"],
                    "capacite"=>$data["capacite"],
                    "emplacement_id"=>$data["emplacement_id"],
                    "ets_id"=>$user->ets_id
                ]);
            }else{
                $result = RestaurantTable::updateOrCreate($cdts,[
                    "numero"=>$data["numero"], 
                    "emplacement_id"=>$data["emplacement_id"],
                    "ets_id"=>$user->ets_id
                ]);
            }


            
            return response()->json([
                'status'=>'success',
                'message' => 'Nouvelle table créée avec succès !',
                'result' => $result,
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
        $tables = $query->orderByDesc("id")->get();

        $query1 = Chambre::with("emplacement")->where("ets_id", $user->ets_id);
        if ($placeId) {
            $query1->where("emplacement_id", $placeId);
        } 
        $chambres = $query1->orderByDesc("id")->get();

        return response()->json([
            "status" => "success",
            "tables" => $tables,
            "chambres"=> $chambres,
        ]);
    }
    public function getAllChambres(Request $request)
    {
        $user = Auth::user();
        $chambres = Chambre::with(["emplacement", "reservations.client"])->where("emplacement_id", $user->emplacement_id)->get();

        return response()->json([
            "status" => "success",
            "chambres" => $chambres
        ]);
    }


    public function reserverChambreOrTable(Request $request)
    {
        try {
            $data = $request->validate([
                'client.nom'          => 'required|string',
                'client.telephone'    => 'nullable|string|max:20',
                'client.email'        => 'nullable|email',
                'client.identite'     => 'required|string',
                'client.identite_type'=> 'required|string',
                'chambre_id'          => 'nullable|exists:chambres,id',
                'date_debut'          => 'required|date|after_or_equal:today',
                'date_fin'            => 'required|date|after:date_debut',
            ]);

            $saleDay = SaleDay::whereNull("end_time")->where("ets_id", Auth::user()->ets_id)->latest()->first();

            if (!$data['chambre_id'] && !$data['table_id']) {
                return response()->json([
                    'errors' => 'Veuillez sélectionner une chambre ou une table.'
                ]);
            }
            $clientData = $data['client'];

            $client = Client::firstOrCreate(
                ['identite' => $clientData['identite']],
                [
                    'nom'           => $clientData['nom'],
                    'telephone'     => $clientData['telephone'] ?? null,
                    'email'         => $clientData['email'] ?? null,
                    'identite_type' => $clientData['identite_type'],
                ]
            );
            $reservation = DB::transaction(function () use ($data, $client, $saleDay) {

                $chambre = $data['chambre_id'] ? Chambre::lockForUpdate()->find($data['chambre_id']) : null;
               /*  $table   = $data['table_id']  ? RestaurantTable::lockForUpdate()->find($data['table_id']) : null; */

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

                if ($chambre) $query->where('chambre_id', $chambre->id);
                /* if ($table)   $query->where('table_id', $table->id); */

                if ($query->exists()) {
                    throw new \Exception('Cette ressource est déjà réservée sur cette période.');
                }
                // Créer la réservation
                $reservation = Reservation::create([
                    'chambre_id' => $chambre?->id ,
                    'client_id'  => $client->id,
                    'date_debut' => $data['date_debut'],
                    'date_fin'   => $data['date_fin'],
                    'sale_day_id'=> $saleDay->id,
                    'statut'     => 'confirmée',
                    'ets_id'     => auth()->user()->ets_id ?? null,
                ]);

                // Mettre à jour le statut de la ressource
                if ($chambre) $chambre->update(['statut' => 'réservée']);
                /* if ($table)   $table->update(['statut' => 'réservée']); */

                if($reservation){
                    Facture::create([
                        'numero_facture'=>'FAC-' . time(),
                        'user_id'=>Auth::id(),
                        'chambre_id'=> $reservation?->chambre_id,
                        'sale_day_id'=> $saleDay->id,
                        'total_ht'=>$chambre->prix ?? 0,
                        'remise'=>0,
                        'total_ttc'=>$chambre->prix ?? 0,
                        'devise'=>$chambre->prix_devise,
                        'date_facture'=>Carbon::today(tz:"Africa/Kinshasa"),
                        'ets_id'=>Auth::user()->ets_id,
                        'emplacement_id'=>Auth::user()->emplacement_id,
                    ]);
                }
                return $reservation;
            });
            return response()->json([
                'message' => 'Réservation créée avec succès.',
                'status'=>"success",
                'result' => $reservation
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->validator->errors()->all()
            ]);
        } catch (\Exception $e) {
            // Log interne pour debugging
            \Log::error('Erreur réservation : '.$e->getMessage());
            return response()->json([
                'errors' => $e->getMessage()
            ]);
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
    public function servirCommande(Request $request)
    {
        $commande = Facture::find((int)$request->id);
        if($commande){
            $commande->update(["statut_service"=>"servie"]);
        }
        return response()->json([
            "status"=>"success",
            "result" => "commande servie avec succès !"
        ]);
    }


    public function updateBedRoomStatus(Request $request)
    {
        $chambre = Chambre::find((int) $request->chambre_id);

        if (!$chambre) {
            return response()->json([
                'errors' => 'Chambre introuvable.',
            ]);
        }

        $today = Carbon::today();
        // Vérifier s’il existe des réservations futures ou en cours pour cette chambre
        $hasFutureReservations = Reservation::where('chambre_id', $chambre->id)
            ->where('statut', '!=', 'annulée')
            ->whereDate('date_fin', '>=', $today)
            ->exists();

        $newStatus = $chambre->statut;

        switch ($chambre->statut) {
            case 'occupée':
                // Si aucune réservation future → libre, sinon réservée
                $newStatus = $hasFutureReservations ? 'réservée' : 'libre';
                break;

            case 'réservée':
                // Si le client vient d’arriver → occupée, sinon libre si plus de résa
                if (!$hasFutureReservations) {
                    $newStatus = 'libre';
                } else {
                    $newStatus = 'occupée';
                }
                break;

            case 'libre':
                // Si on déclenche l’action et qu’il y a une résa future → réservée
                if ($hasFutureReservations) {
                    $newStatus = 'réservée';
                }
                break;
        }
        // Mise à jour uniquement si changement réel
        if ($newStatus !== $chambre->statut) {
            $chambre->update(['statut' => $newStatus]);
        }
        return response()->json([
            'status' => 'success',
            'result' => "Statut mis à jour : {$newStatus}",
            'message' => "Statut mis à jour : {$newStatus}",
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
                    "table_id"=>isset($facture->table_id) ? $facture->table_id : $facture->chambre_id ,
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


    public function showDaySaleFacturesByCaissier(Request $request)
    {
        $caissierId = $request->query("id");
        $factures = Facture::with(["payments", "saleDay.sales.user", "user"])
            ->whereHas("saleDay.sales", function($query) use ($caissierId) {
                $query->where("user_id", $caissierId);
            })->where("statut", "payée")
            ->orderByDesc("id")
            ->get();

        return response()->json([
            "status" => "success",
            "factures" => $factures
        ]);
    }



}
