<?php

namespace App\Http\Controllers;

use App\Models\Currencie;
use App\Models\Facture;
use App\Models\FactureDetail;
use App\Models\MouvementStock;
use App\Models\RestaurantTable;
use App\Models\SaleDay;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    /** 
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }


    /**
     * Créer une facture pour la commande
     * @return mixed
    */
    public function saveFacture(Request $request)
    {
        try {
            $data = $request->validate([
                'facture_id' => 'nullable|exists:factures,id',
                'table_id' => 'nullable|exists:restaurant_tables,id',
                'user_id' => 'nullable|exists:users,id',
                'remise' => 'nullable|numeric',
                'details' => 'required|array|min:1',
                'details.*.produit_id' => 'required|exists:produits,id',
                'details.*.quantite' => 'required|integer|min:1',
                'details.*.prix_unitaire' => 'required|numeric|min:0',
            ]);

            $factureId = null;
            $saleDay = SaleDay::whereNull("end_time")->latest()->first();
            if(!$saleDay){
                return response()->json([
                    "errors"=>"La journée de vente non ouverte !"
                ]);
            }
            DB::transaction(function () use ($data, $saleDay) {
                if (isset($data['facture_id'])) {
                    // Modification
                    $facture = Facture::findOrFail($data['facture_id']);
                    // Supprimer les anciens détails
                    $facture->details()->delete();
                } else {
                    // Création
                    $facture = new Facture();
                    $facture->numero_facture = 'FAC-' . time(); 
                }
                
                // Mise à jour des infos
                $facture->user_id = $data["user_id"] ?? Auth::id();
                $facture->table_id = $data['table_id'] ?? null;
                $facture->sale_day_id = $saleDay->id;
                $facture->remise = $data['remise'] ?? 0;
                $facture->statut = "en_attente";
                // Calcul total HT
                $total_ht = 0;
                foreach ($data['details'] as $detail) {
                    $total_ht += (int)$detail['quantite'] * (float)$detail['prix_unitaire'];
                }
                $facture->total_ht = $total_ht;
                $facture->date_facture = Carbon::now(tz:"Africa/Kinshasa");
                // Calcul total TTC
                $facture->total_ttc = $total_ht - $facture->remise;
                $facture->save();

                // Création des détails
                foreach ($data['details'] as $detail) {
                    FactureDetail::create([
                        'facture_id' => $facture->id,
                        'produit_id' => $detail['produit_id'],
                        'quantite' => $detail['quantite'],
                        'prix_unitaire' => $detail['prix_unitaire'],
                        'total_ligne' => $detail['quantite'] * $detail['prix_unitaire'],
                    ]);
                }

                $factureId = $facture->id;

                $table = RestaurantTable::find($data["table_id"]);

                $table->update([
                    "statut"=>"occupée"
                ]);
            });
            $facture = Facture::with('details')->find($factureId);
            return response()->json([
                'status' => 'success',
                'result' => $facture
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()->all()], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['errors' => $e->getMessage()], 500);
        }
    }

    /**
     * Affiche toutes les factures et commande
     * @return mixed
    */
    public function getAllFacturesCmds(Request $request)
    {
        $user = Auth::user();
        $isServeur = $user->role === "serveur";
        $status = $request->query("status");

        Log::info("status $status");

        $req = Facture::with([
            "details.produit",
            "table.emplacement",
            "payments",
            "saleDay",
            "user"
        ])
        ->when($status, function ($query) use ($status) {
            $query->where("statut", "en_attente");
        })
        ->when($isServeur, function ($query) use ($user) {
            $query->where("user_id", $user->id);
        });

        if($status){
            $req->where("statut", "en_attente");
        }

        $factures = $req->orderByDesc("id")->get();

        return response()->json([
            "status" => "success",
            "factures" => $factures
        ]);
    }


    /**
     * Voir toutes les ventes d'une journée ouvrable
     * @return mixed
    */
    public function getAllSells(Request $request)
    {
        $saleDay = SaleDay::whereNull("end_time")->latest()->first();

        $sells = MouvementStock::with("produit")
            ->selectRaw("produit_id, SUM(quantite) as total_vendu")
            ->where("type_mouvement", "vente")
            ->where("sale_day_id", $saleDay->id)
            ->groupBy("produit_id")
            ->get();

        return response()->json([
            "status" => "success",
            "ventes" => $sells
        ]);
    }


    public function dashboardCounter()
    {
        $user = Auth::user();
        $isServeur = $user->role === "serveur"; 
        $saleDay = SaleDay::whereNull("end_time")->latest()->first();

        // Factures en attente
        $pendingInvoice = Facture::where("statut", "en_attente")
            ->where("sale_day_id", $saleDay->id)
            ->when($isServeur, function ($query) use ($user) {
                $query->where("user_id", $user->id);
            })
            ->count();

        // Toutes les factures du jour
        $allFactureOfDay = Facture::where("sale_day_id", $saleDay->id)
            ->when($isServeur, function ($query) use ($user) {
                $query->where("user_id", $user->id);
            })
            ->count();
        $cancelledFactures = Facture::where("sale_day_id", $saleDay->id)
            ->when($isServeur, function ($query) use ($user) {
                $query->where("user_id", $user->id);
            })->where("statut", "annulée")
            ->count();
        $daySells = MouvementStock::where("sale_day_id", $saleDay->id)
            ->when($isServeur, function ($query) use ($user) {
                $query->where("user_id", $user->id);
            })->where("type_mouvement", "vente")
            ->count();

        // Utilisateurs connectés
        $connectedUsers = User::whereHas('lastLog', function ($query) {
                $query->where('status', 'online');
            })
            ->count();

        return response()->json([
            "status" => "success",
            "counts" => [
                "facs" => $this->padLeft($allFactureOfDay),
                "pendings" => $this->padLeft($pendingInvoice),
                "cancelled" => $this->padLeft($cancelledFactures),
                "users" => $this->padLeft($connectedUsers),
                "sells"=> $this->padLeft($daySells)
            ]
        ]);
    }


    private function padLeft($str){
        return  str_pad((string)$str,2,  "0", STR_PAD_LEFT);
    }


}
