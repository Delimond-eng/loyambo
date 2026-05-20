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
use App\Support\ReportExporter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminController extends Controller
{
    /**
     * Fonction permettant de commencer une journÃƒÂ©e de ventes
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
                [
                    "currencie_date"=>Carbon::now()->toDateString(),
                    "ets_id"=>$user->ets_id
                ],
            [
                "currencie_date"=>Carbon::now()->toDateString(),
                "currencie_value"=>$data["currencie_value"],
                "ets_id"=>$user->ets_id
            ]);

            $saleDay = SaleDay::create(
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
            return response()->json(['errors' => "Action non autorisÃƒÂ©e !"]);
        }
    }

    public function closeDayReport(Request $request)
    {
        try {
            $data = $request->validate([
                "serveur_id" => "required|int|exists:users,id",
                "total_especes" => "required|numeric|min:0",
                "tickets_serveur" => "required|int|min:0",
                "tickets_emis" => "required|int|min:0",
                "valeur_theorique" => "required|numeric|min:0",
            ]);
            $user = Auth::user();

            $saleDay = $this->getOpenedSaleDay($user->ets_id);
            if (!$saleDay) {
                return response()->json([
                    "errors" => "Aucune journee ouverte a cloturer.",
                ]);
            }

            $emplacementFilter = ($user->role !== "admin" && $user->emplacement_id)
                ? (int) $user->emplacement_id
                : null;

            $serveurStats = $this->getServeursActivitiesStats($saleDay->id, $user->ets_id, $emplacementFilter)
                ->firstWhere("user_id", (int) $data["serveur_id"]);

            if (!$serveurStats) {
                return response()->json([
                    "status" => "failed",
                    "message" => "Ce serveur n'a aucune activite a cloturer pour cette journee.",
                ], 422);
            }

            $expectedTickets = (int) $serveurStats->total_ticket;
            $expectedTheorique = (double) $serveurStats->total_encaisse;

            if (
                (int) $data["tickets_emis"] !== $expectedTickets ||
                abs(((double) $data["valeur_theorique"]) - $expectedTheorique) > 0.01
            ) {
                return response()->json([
                    "status" => "failed",
                    "message" => "Les donnees systeme ont change. Actualisez la liste puis recommencez.",
                    "expected" => [
                        "tickets_emis" => $expectedTickets,
                        "valeur_theorique" => $expectedTheorique,
                    ],
                ], 409);
            }

            $taux = Currencie::where("ets_id", $user->ets_id)->latest("id")->value("currencie_value") ?? 0;

            $result = CaisseReport::updateOrCreate(
                [
                    "serveur_id" => (int) $data["serveur_id"],
                    "sale_day_id" => $saleDay->id,
                ],
                [
                    "caissier_id" => $user->id,
                    "rapport_date" => Carbon::now(tz: "Africa/Kinshasa")->toDateString(),
                    "taux" => (double) $taux,
                    "valeur_theorique" => $expectedTheorique,
                    "tickets_emis" => $expectedTickets,
                    "total_especes" => (double) $data["total_especes"],
                    "tickets_serveur" => (int) $data["tickets_serveur"],
                ]
            );

            return response()->json([
                "status" => "success",
                "message" => "Le rapport serveur a ete enregistre avec succes.",
                "result" => $result,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            return response()->json(['errors' => $errors]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['errors' => $e->getMessage()]);
        }
    }

    public function closeDay()
    {
        try {
            $user = Auth::user();
            $saleDay = $this->getOpenedSaleDay($user->ets_id);

            if (!$saleDay) {
                return response()->json([
                    "errors" => "Aucune journee ouverte a cloturer.",
                ]);
            }

            $serveursActifs = $this->getServeursActivitiesStats($saleDay->id, $user->ets_id);
            $serveursAvecActivite = $serveursActifs
                ->pluck("user_id")
                ->unique();

            $serveursAvecRapport = CaisseReport::where("sale_day_id", $saleDay->id)
                ->pluck("serveur_id")
                ->unique();

            $serveursManquants = $serveursAvecActivite->diff($serveursAvecRapport);

            if ($serveursManquants->isNotEmpty()) {
                $serveursInfos = $serveursActifs
                    ->whereIn("user_id", $serveursManquants)
                    ->sortByDesc("total_encaisse")
                    ->values();

                return response()->json([
                    "status" => "failed",
                    "message" => "Impossible de cloturer: certains serveurs actifs n'ont pas encore remis leur rapport.",
                    "serveurs" => $serveursInfos,
                    "missing_count" => $serveursInfos->count(),
                    "missing_total_encaisse" => (double) $serveursInfos->sum("total_encaisse"),
                ]);
            }

            $now = Carbon::now("Africa/Kinshasa");
            DB::transaction(function () use ($saleDay, $user, $now) {
                $saleDay->update([
                    "end_time" => $now,
                ]);

                UserLog::where("sale_day_id", $saleDay->id)
                    ->whereNull("logged_out_at")
                    ->update([
                        "logged_out_at" => $now,
                        "status" => "offline",
                    ]);

                $accessAllow = AccessAllow::where("ets_id", $user->ets_id)->latest()->first();
                if ($accessAllow) {
                    $accessAllow->update(["allowed" => false]);
                }
            });

            return response()->json([
                "status" => "success",
                "message" => "La journee a ete cloturee avec succes.",
                "saleDay" => $saleDay,
                "report_url" => "caisse.day.report/" . $saleDay->id,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            return response()->json(['errors' => $errors]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['errors' => $e->getMessage()]);
        }
        catch (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            return response()->json(['errors' => "Action non autorisÃƒÂ©e !"]);
        }
    }


    public function generatePDF($sale_day_id)
    {
        $saleDay = SaleDay::findOrFail($sale_day_id);
        // Groupement des rapports par caissier
        $groupedReports = CaisseReport::with(['caissier', 'serveur'])
            ->where('sale_day_id', $sale_day_id)
            ->get()
            ->groupBy('caissier_id');

        $pdf = Pdf::loadView('pdf.caisse_day_report', [
            'saleDay' => $saleDay,
            'groupedReports' => $groupedReports,
        ])->setPaper('a4', 'portrait');

        $filename = 'Rapport_Caisse_' . $saleDay->sale_date . '.pdf';
        return $pdf->download($filename);
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
        $users = $query->whereNot("status", "deleted")->get();
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
        $saleDay = $this->getOpenedSaleDay($user->ets_id);

        if (!$saleDay) {
            return response()->json([
                "status" => "error",
                "message" => "Aucune journÃƒÂ©e de vente active trouvÃƒÂ©e."
            ]);
        }

        $emplacementFilter = ($user->role !== "admin" && $user->emplacement_id)
            ? (int) $user->emplacement_id
            : null;

        $serveurs = $this->getServeursActivitiesStats($saleDay->id, $user->ets_id, $emplacementFilter);

        return response()->json([
            "status" => "success",
            "serveurs" => $serveurs
        ]);
    }

    private function getOpenedSaleDay(int $etsId): ?SaleDay
    {
        return SaleDay::whereNull("end_time")
            ->where("ets_id", $etsId)
            ->latest("id")
            ->first();
    }

    private function getServeursActivitiesStats(int $saleDayId, int $etsId, ?int $emplacementId = null): Collection
    {
        $query = Facture::with("user.lastLog", "user.emplacement")
            ->selectRaw("user_id, COUNT(id) as total_ticket")
            ->selectRaw("
                SUM(
                    CASE
                        WHEN EXISTS (
                            SELECT 1
                            FROM payments
                            WHERE payments.facture_id = factures.id
                        )
                        THEN total_ttc
                        ELSE 0
                    END
                ) as total_encaisse
            ")
            ->where("sale_day_id", $saleDayId)
            ->where("ets_id", $etsId)
            ->whereNotIn("statut", ["annulee", "annulée", "annulÃ©e", "annulÃƒÂ©e"])
            ->whereHas("user", function ($q) use ($etsId) {
                $q->where("role", "serveur")
                    ->where("ets_id", $etsId);
            });

        if (!is_null($emplacementId)) {
            $query->where("emplacement_id", $emplacementId);
        }

        $serveurs = $query
            ->groupBy("user_id")
            ->get();

        $reportsByServeur = CaisseReport::where("sale_day_id", $saleDayId)
            ->orderByDesc("id")
            ->get()
            ->unique("serveur_id")
            ->keyBy("serveur_id");

        return $serveurs
            ->map(function ($srv) use ($reportsByServeur) {
                $rapport = $reportsByServeur->get((int) $srv->user_id);
                $srv->serveur_id = (int) $srv->user_id;
                $srv->total_encaisse = (double) $srv->total_encaisse;
                $srv->total_ticket = (int) $srv->total_ticket;
                $srv->montant_vendu = (double) $srv->total_encaisse;
                $srv->factures_actives = (int) $srv->total_ticket;
                $srv->rapport_statut = $rapport ? "done" : "none";
                $srv->rapport_id = $rapport->id ?? null;
                $srv->rapport_caissier_id = $rapport->caissier_id ?? null;
                return $srv;
            })
            ->values();
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
            \Log::info('Permissions ÃƒÂ  synchroniser: ', $permissionNames);
            // Synchroniser les permissions
            $user->syncPermissions($permissionNames);
            return response()->json([
                'status'=>'success',
                'message' => 'Permissions mises ÃƒÂ  jour avec succÃƒÂ¨s',
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
            return response()->json(['errors' => "Action non autorisÃƒÂ©e !"]);
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
                'message' => 'Nouveau emplacement crÃƒÂ©ÃƒÂ© avec succÃƒÂ¨s !',
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
            return response()->json(['errors' => "Action non autorisÃƒÂ©e !"]);
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


            if($emplacement->type === 'hÃƒÂ´tel'){
                $check = Chambre::where("numero",$data["numero"])->whereNot("id", $data["id"] ?? '')->where("ets_id", Auth::user()->ets_id)->first();
                if($check && $check->emplacement_id === $data['emplacement_id']){
                    return response()->json(["errors"=> "NumÃƒÂ©ro de la table existe dÃƒÂ©jÃƒÂ ."]);
                }
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
                $check = RestaurantTable::where("numero",$data["numero"])->whereNot("id", $data["id"])->where("ets_id", Auth::user()->ets_id)->first();
                if($check && $check->emplacement_id === $data['emplacement_id']){
                    return response()->json(["errors"=> "NumÃƒÂ©ro de la table existe dÃƒÂ©jÃƒÂ ."]);
                }
                $result = RestaurantTable::updateOrCreate($cdts,[
                    "numero"=>$data["numero"], 
                    "emplacement_id"=>$data["emplacement_id"],
                    "ets_id"=>$user->ets_id
                ]);
            }
            return response()->json([
                'status'=>'success',
                'message' => 'Nouvelle table crÃƒÂ©ÃƒÂ©e avec succÃƒÂ¨s !',
                'result' => $result,
            ]);
        }
        catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            return response()->json(['errors' => $errors]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['errors' => $e->getMessage()]);
        } catch (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            return response()->json(['errors' => "Action non autorisÃƒÂ©e !"]);
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
                $query->where("statut", "!=", "payÃƒÂ©e")
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
        Artisan::call('reservations:update');
        $user = Auth::user();
        $chambres = Chambre::with(["emplacement", "reservations.client"])->where("emplacement_id", $user->emplacement_id)->get();

        return response()->json([
            "status" => "success",
            "chambres" => $chambres
        ]);
    }

    public function triggerTableOperation(Request $request)
    {
        $operation = $request->op;

        // Ã°Å¸â€Â¹ Transfert
        if ($operation === 'transfert') {
            $request->validate([
                'source_id' => 'required|integer|exists:restaurant_tables,id',
                'cible_id'  => 'required|integer|exists:restaurant_tables,id',
            ]);

            $tableSource = RestaurantTable::find($request->source_id);
            $tableCible  = RestaurantTable::find($request->cible_id);

            if ($tableSource->id === $tableCible->id) {
                return response()->json([
                    "errors" => "Impossible de transfÃƒÂ©rer vers la mÃƒÂªme table."
                ]);
            }

            DB::transaction(function () use ($tableSource, $tableCible) {
                $tableSource->update(["statut" => "libre"]);
                $tableCible->update(["statut" => "occupÃƒÂ©e"]);

                // DÃƒÂ©placer toutes les commandes de la source vers la cible
                $tableSource->commandes()->update([
                    "table_id" => $tableCible->id
                ]);
            });

            return response()->json([
                "status" => "success",
                "result" => "Transfert effectuÃƒÂ© avec succÃƒÂ¨s !"
            ]);
        }

        // Ã°Å¸â€Â¹ Combinaison
        if ($operation === 'combiner') {
            $request->validate([
                'table1_id' => 'required|integer|exists:restaurant_tables,id',
                'table2_id' => 'required|integer|exists:restaurant_tables,id',
            ]);

            $table1 = RestaurantTable::find($request->table1_id);
            $table2 = RestaurantTable::find($request->table2_id);

            if ($table1->id === $table2->id) {
                return response()->json([
                    "errors" => "Impossible de combiner une table avec elle-mÃƒÂªme."
                ]);
            }

            DB::transaction(function () use ($table1, $table2) {
                // DÃƒÂ©placer toutes les commandes de la table1 vers la table2
                $table1->commandes()->update([
                    "table_id" => $table2->id
                ]);

                // LibÃƒÂ©rer la table1
                $table1->update(["statut" => "libre"]);

                // Table2 reste occupÃƒÂ©e
                $table2->update(["statut" => "occupÃƒÂ©e"]);
            });

            return response()->json([
                "status" => "success",
                "result" => "Tables combinÃƒÂ©es avec succÃƒÂ¨s !"
            ]);
        }

        return response()->json([
            "errors" => "OpÃƒÂ©ration inconnue ou non supportÃƒÂ©e."
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
            "result" => "Table liberÃƒÂ©e avec succÃƒÂ¨s !"
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
            "result" => "commande servie avec succÃƒÂ¨s !"
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
        // VÃƒÂ©rifier sÃ¢â‚¬â„¢il existe des rÃƒÂ©servations futures ou en cours pour cette chambre
        $hasFutureReservations = Reservation::where('chambre_id', $chambre->id)
            ->where('statut', '!=', 'annulÃƒÂ©e')
            ->whereDate('date_fin', '>=', $today)
            ->exists();

        $newStatus = $chambre->statut;

        switch ($chambre->statut) {
            case 'occupÃƒÂ©e':
                // Si aucune rÃƒÂ©servation future Ã¢â€ â€™ libre, sinon rÃƒÂ©servÃƒÂ©e
                $newStatus = $hasFutureReservations ? 'rÃƒÂ©servÃƒÂ©e' : 'libre';
                break;

            case 'rÃƒÂ©servÃƒÂ©e':
                // Si le client vient dÃ¢â‚¬â„¢arriver Ã¢â€ â€™ occupÃƒÂ©e, sinon libre si plus de rÃƒÂ©sa
                if (!$hasFutureReservations) {
                    $newStatus = 'libre';
                } else {
                    $newStatus = 'occupÃƒÂ©e';
                }
                break;

            case 'libre':
                // Si on dÃƒÂ©clenche lÃ¢â‚¬â„¢action et quÃ¢â‚¬â„¢il y a une rÃƒÂ©sa future Ã¢â€ â€™ rÃƒÂ©servÃƒÂ©e
                if ($hasFutureReservations) {
                    $newStatus = 'rÃƒÂ©servÃƒÂ©e';
                }
                break;
        }
        // Mise ÃƒÂ  jour uniquement si changement rÃƒÂ©el
        if ($newStatus !== $chambre->statut) {
            $chambre->update(['statut' => $newStatus]);
        }
        return response()->json([
            'status' => 'success',
            'result' => "Statut mis ÃƒÂ  jour : {$newStatus}",
            'message' => "Statut mis ÃƒÂ  jour : {$newStatus}",
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
                    $facture->update(["statut"=>"payÃƒÂ©e"]);
                }
            }

            return response()->json([
                'status'=>'success',
                'message' => 'Nouvelle table crÃƒÂ©ÃƒÂ©e avec succÃƒÂ¨s !',
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
            return response()->json(['errors' => "Action non autorisÃƒÂ©e !"]);
        }
    }
    public function deleteTable(Request $request){
        $data = $request->validate([
            'id' => 'required|exists:restaurant_tables,id'
        ]);

        $table = RestaurantTable::where('id', $data['id'])->where('ets_id', Auth::user()->ets_id)->first();
        if (!$table) {
            return response()->json(["errors" => "Introuvable."], 404);
        }

        $table->delete();
        return response()->json(["status" => "success", "message" => "Table supprimÃ©e"]);
    }

    public function deleteChambre(Request $request){
        $data = $request->validate([
            'id' => 'required|exists:chambres,id'
        ]);

        $chambre = Chambre::where('id', $data['id'])->where('ets_id', Auth::user()->ets_id)->first();
        if (!$chambre) {
            return response()->json(["errors" => "Introuvable."], 404);
        }

        $chambre->delete();
        return response()->json(["status" => "success", "message" => "Chambre supprimÃ©e"]);
    }
    public function globalReportsView()
    {
        $etsId = Auth::user()->ets_id;
        $emplacements = Emplacement::where("ets_id", $etsId)->orderBy("libelle")->get();
        $serviceTypes = Emplacement::getTypesForEts($etsId);
        $caissiers = User::where("ets_id", $etsId)
            ->whereNotIn("role", ["serveur", "cuisinier"])
            ->orderBy("name")
            ->get();

        return view("reports_global", compact("emplacements", "serviceTypes", "caissiers"));
    }

    private function buildGlobalReports(Request $request)
    {
        $etsId = Auth::user()->ets_id;
        $serviceType = $request->query("service_type");
        $emplacementId = $request->query("emplacement_id");
        $caissierId = $request->query("caissier_id");
        $dateDebut = $request->query("date_debut");
        $dateFin = $request->query("date_fin");

        $rows = Payments::query()
            ->select([
                'sale_day_id',
                'user_id',
                DB::raw('SUM(amount) as total_factures'),
                DB::raw('COUNT(*) as total_paiements'),
                DB::raw('COUNT(DISTINCT facture_id) as total_factures_count'),
            ])
            ->where('ets_id', $etsId)
            ->whereNotNull('sale_day_id')
            ->when($emplacementId, fn($q) => $q->where('emplacement_id', (int) $emplacementId))
            ->when($caissierId, fn($q) => $q->where('user_id', (int) $caissierId))
            ->when($dateDebut, fn($q) => $q->whereDate('pay_date', '>=', $dateDebut))
            ->when($dateFin, fn($q) => $q->whereDate('pay_date', '<=', $dateFin))
            ->when($serviceType, function ($q) use ($serviceType) {
                $q->whereHas('emplacement', fn($e) => $e->where('type', $serviceType));
            })
            ->groupBy('sale_day_id', 'user_id')
            ->orderByDesc(DB::raw('MAX(sale_day_id)'))
            ->get();

        $saleDayIds = $rows->pluck('sale_day_id')->unique()->values();
        $userIds = $rows->pluck('user_id')->unique()->values();

        $saleDays = SaleDay::where('ets_id', $etsId)->whereIn('id', $saleDayIds)->get()->keyBy('id');
        $users = User::where('ets_id', $etsId)
            ->whereIn('id', $userIds)
            ->whereNotIn('role', ['serveur', 'cuisinier'])
            ->get()
            ->keyBy('id');

        return $rows
            ->filter(fn($row) => isset($saleDays[$row->sale_day_id]) && isset($users[$row->user_id]))
            ->map(fn($row) => [
                'sale_day' => $saleDays[$row->sale_day_id],
                'user' => $users[$row->user_id],
                'total_factures' => (double) $row->total_factures,
                'total_paiements' => (int) $row->total_paiements,
                'total_factures_count' => (int) $row->total_factures_count,
            ])
            ->values();
    }

    private function formatGlobalReportFilters(Request $request): string
    {
        $parts = [];
        if ($request->filled("service_type")) {
            $parts[] = "Service: " . $request->service_type;
        }
        if ($request->filled("emplacement_id")) {
            $emp = Emplacement::where("id", (int) $request->emplacement_id)->value("libelle");
            $parts[] = "Emplacement: " . ($emp ?? $request->emplacement_id);
        }
        if ($request->filled("caissier_id")) {
            $caissier = User::where("id", (int) $request->caissier_id)->value("name");
            $parts[] = "Caissier: " . ($caissier ?? $request->caissier_id);
        }
        if ($request->filled("date_debut") || $request->filled("date_fin")) {
            $parts[] = "PÃƒÂ©riode: " . ($request->date_debut ?? "-") . " au " . ($request->date_fin ?? "-");
        }

        return implode(" | ", $parts);
    }

    //GLOBAL REPORT GROUPED BY USER
    public function viewGlobalReports(Request $request)
    {
        $reports = $this->buildGlobalReports($request);

        return response()->json([
            'status' => 'success',
            'reports' => $reports,
        ]);
    }

    public function exportGlobalReportsPdf(Request $request)
    {
        $reports = $this->buildGlobalReports($request);
        $headers = ["JournÃƒÂ©e", "Caissier", "Total encaissÃƒÂ©", "Factures", "Paiements"];
        $rows = $reports->map(function ($row) {
            $saleDay = $row["sale_day"] ?? null;
            $user = $row["user"] ?? null;
            $date = $saleDay ? $saleDay->sale_date : "-";
            $caissier = $user ? $user->name : "-";
            return [
                $date,
                $caissier,
                number_format($row["total_factures"], 0, ",", " "),
                $row["total_factures_count"],
                $row["total_paiements"],
            ];
        })->toArray();

        $filters = $this->formatGlobalReportFilters($request);

        $pdf = Pdf::loadView("pdf.report_table", [
            "title" => "Rapport des journÃƒÂ©es de vente",
            "subtitle" => "SynthÃƒÂ¨se des encaisses par journÃƒÂ©e et caissier",
            "filters" => $filters,
            "headers" => $headers,
            "rows" => $rows,
        ])->setPaper("a4", "landscape");

        return $pdf->download("rapport_journees_vente_" . date("Ymd_His") . ".pdf");
    }

    public function exportGlobalReportsExcel(Request $request)
    {
        $reports = $this->buildGlobalReports($request);
        $headers = ["JournÃƒÂ©e", "Caissier", "Total encaissÃƒÂ©", "Factures", "Paiements"];
        $rows = $reports->map(function ($row) {
            $saleDay = $row["sale_day"] ?? null;
            $user = $row["user"] ?? null;
            $date = $saleDay ? $saleDay->sale_date : "-";
            $caissier = $user ? $user->name : "-";
            return [
                $date,
                $caissier,
                $row["total_factures"],
                $row["total_factures_count"],
                $row["total_paiements"],
            ];
        })->toArray();

        return ReportExporter::toExcel(
            "rapport_journees_vente_" . date("Ymd_His") . ".xlsx",
            "Ventes journaliÃƒÂ¨res",
            $headers,
            $rows
        );
    }


    public function showDaySaleFacturesByCaissier(Request $request)
    {
        $etsId = Auth::user()->ets_id;
        $caissierId = (int) $request->query('id');
        $saleDayId = $request->query('sale_day_id');
        $serviceType = $request->query("service_type");
        $emplacementId = $request->query("emplacement_id");
        $dateDebut = $request->query("date_debut");
        $dateFin = $request->query("date_fin");

        $factures = Facture::query()
            ->with(['payments', 'saleDay', 'user'])
            ->where('ets_id', $etsId)
            ->where('statut', 'payÃƒÂ©e')
            ->when($saleDayId, fn($q) => $q->where('sale_day_id', (int) $saleDayId))
            ->when($emplacementId, fn($q) => $q->where('emplacement_id', (int) $emplacementId))
            ->when($dateDebut, fn($q) => $q->whereDate('date_facture', '>=', $dateDebut))
            ->when($dateFin, fn($q) => $q->whereDate('date_facture', '<=', $dateFin))
            ->when($serviceType, fn($q) => $q->whereHas('emplacement', fn($e) => $e->where('type', $serviceType)))
            ->when($caissierId, function ($q) use ($caissierId) {
                $q->whereHas('payments', fn($p) => $p->where('user_id', $caissierId));
            })
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status' => 'success',
            'factures' => $factures,
        ]);
    }
}








