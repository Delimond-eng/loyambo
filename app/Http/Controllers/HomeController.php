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
                'chambre_id' => 'nullable|exists:restaurant_tables,id',
                'user_id' => 'nullable|exists:users,id',
                'remise' => 'nullable|numeric',
                'details' => 'required|array|min:1',
                'details.*.produit_id' => 'required|exists:produits,id',
                'details.*.quantite' => 'required|integer|min:1',
                'details.*.prix_unitaire' => 'required|numeric|min:0',
            ]);

            $factureId = null;
            $user = Auth::user();
            $serveur = User::find($data["user_id"]);
            $serveur = $serveur ?? $user;
            $saleDay = SaleDay::whereNull("end_time")->where("ets_id", $user->ets_id)->latest()->first();
            if(!$saleDay){
                return response()->json([
                    "errors"=>"La journée de vente non ouverte !"
                ]);
            }
            DB::transaction(function () use ($data, $saleDay, $serveur) {
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
                $facture->user_id = $serveur->id;
                if(isset($data["table_id"])){
                    $facture->table_id = $data['table_id'] ?? null;
                }
                if(isset($data["chambre_id"])){
                    $facture->chambre_id = $data['chambre_id'] ?? null;
                }
                $facture->sale_day_id = $saleDay->id;
                $facture->remise = $data['remise'] ?? 0;
                $facture->statut = "en_attente";
                $facture->emplacement_id = $serveur->emplacement_id;
                $facture->ets_id =$serveur->ets_id;
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

                if(isset($data["table_id"])){
                    $table = RestaurantTable::find($data["table_id"]);
                    $table->update([
                        "statut"=>"occupée"
                    ]);
                }
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
        catch (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            return response()->json(['errors' => $e->getMessage()]);
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
        $status = $request->query("status") ?? null;

        $req = Facture::with([
            "details.produit",
            "table.emplacement",
            "chambre.emplacement",
            "payments",
            "saleDay",
            "user"
        ])
        ->when($status, function ($query) use ($status) {
            $query->where("statut", "en_attente");
        })
        ->when($isServeur, function ($query) use ($user) {
            $query->where("user_id", $user->id);
        })->where("ets_id", $user->ets_id);

        if($user->role !== "admin" && $user->emplacement_id){
            $req->where("emplacement_id", $user->emplacement_id);
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
    /* public function getAllSells(Request $request)
    {
        $dateRange = $request->query("dateRange");
        $serveurId = $request->query("serveur");
        $saleDay = SaleDay::whereNull("end_time")->latest()->first();

        $req = MouvementStock::with("produit")
            ->selectRaw("produit_id, SUM(quantite) as total_vendu")
            ->where("type_mouvement", "vente");

        $req->when($dateRange, function ($q) use ($dateRange) {
            [$start, $end] = explode(";", $dateRange);
            $q->whereBetween("date_mouvement", [
                $start . " 00:00:00",
                $end . " 23:59:59"
            ]);
        });

        $req->when($serveurId, function ($q) use ($serveurId) {
            $q->where("user_id", $serveurId);
        });

        $req->when(!$dateRange, function ($q) use ($saleDay) {
            $q->where("sale_day_id", $saleDay->id);
        });

        $sells = $req->groupBy("produit_id")->get();

        return response()->json([
            "status" => "success",
            "ventes" => $sells
        ]);
    } */
    public function getAllSells(Request $request)
    {
        $dateRange = $request->query("dateRange");
        $serveurId = $request->query("serveur");
        $user = Auth::user();
        $saleDay = SaleDay::whereNull("end_time")->where("ets_id", $user->ets_id)->latest()->first();

        $req = MouvementStock::with(["produit", "facture.user"])
            ->selectRaw("produit_id, SUM(quantite) as total_vendu")
            ->where("type_mouvement", "vente");

        $req->when($dateRange, function ($q) use ($dateRange) {
            [$start, $end] = explode(";", $dateRange);
            $q->whereBetween("date_mouvement", [
                $start . " 00:00:00",
                $end . " 23:59:59"
            ]);
        });

        $req->when(!$dateRange, function ($q) use ($saleDay) {
            $q->where("sale_day_id", $saleDay->id);
        });

        $req->where("ets_id", $user->ets_id);

        // Restriction par emplacement pour non-admin
        if($user->role !== 'admin' && $user->emplacement_id){
            $req->where("emplacement_id", $user->emplacement_id);
        }

        $sells = $req->groupBy("produit_id")->get();

        $sells->map(function ($item) use ($dateRange, $saleDay, $serveurId, $user) {

            $query = MouvementStock::with("facture.user")
                ->selectRaw("numdoc, SUM(quantite) as quantite")
                ->where("type_mouvement", "vente")
                ->where("produit_id", $item->produit_id)
                ->whereNotNull("numdoc"); // s'assurer que c'est lié à une facture

            // Appliquer les mêmes filtres de date
            $query->when($dateRange, function ($q) use ($dateRange) {
                [$start, $end] = explode(";", $dateRange);
                $q->whereBetween("date_mouvement", [
                    $start . " 00:00:00",
                    $end . " 23:59:59"
                ]);
            });

            $query->when(!$dateRange, function ($q) use ($saleDay) {
                $q->where("sale_day_id", $saleDay->id);
            });

            if($user->role !== 'admin' && $user->emplacement_id){
                $query->where("emplacement_id", $user->emplacement_id);
            }

            // Filtrer par serveur si demandé
            if($serveurId){
                $query->whereHas("facture", function ($q) use ($serveurId) {
                    $q->where("user_id", $serveurId);
                });
            }

            $users = $query->groupBy("numdoc")->get();

            // Calculer montant par utilisateur/facture
            $item->byUsers = $users->map(function ($u) use ($item) {
                return [
                    "facture_id" => $u->numdoc,
                    "user_id" => $u->facture->user->id ?? null,
                    "nom" => $u->facture->user->name ?? "Inconnu",
                    "quantite" => $u->quantite,
                    "montant" => $u->quantite * $item->produit->prix_unitaire,
                ];
            });

            return $item;
        });

        return response()->json([
            "status" => "success",
            "ventes" => $sells
        ]);
    }



    public function dashboardCounter()
    {
        $user = Auth::user();
        $isServeur = $user->role === "serveur"; 
        $saleDay = SaleDay::whereNull("end_time")->where("ets_id", $user->ets_id)->latest()->first();
        $emplacementId = $user->emplacement_id ?? null;

        // Factures en attente
        $pendingInvoice = Facture::where("statut", "en_attente")
            ->when($isServeur, function ($query) use ($user) {
                $query->where("user_id", $user->id);
            })
            ->when($saleDay, function ($query) use ($saleDay) {
                $query->where("sale_day_id", $saleDay->id);
            })
            ->when($emplacementId, function ($query) use ($user) {
                $query->where("emplacement_id", $user->emplacement_id);
            })
            ->where("ets_id", $user->ets_id)
            ->count();

        // Toutes les factures du jour
        $allFactureOfDay = Facture::when($isServeur, function ($query) use ($user) {
                $query->where("user_id", $user->id);
            })
            ->when($saleDay, function ($query) use ($saleDay) {
                $query->where("sale_day_id", $saleDay->id);
            })
            ->when($emplacementId, function ($query) use ($user) {
                $query->where("emplacement_id", $user->emplacement_id);
            })
            ->where("ets_id", $user->ets_id)
            ->count();
        $cancelledFactures = Facture::when($isServeur, function ($query) use ($user) {
                $query->where("user_id", $user->id);
            })->when($saleDay, function ($query) use ($saleDay) {
                $query->where("sale_day_id", $saleDay->id);
            })->where("statut", "annulée")
            ->when($emplacementId, function ($query) use ($user) {
                $query->where("emplacement_id", $user->emplacement_id);
            })
            ->where("ets_id", $user->ets_id)
            ->count();
        $daySells = MouvementStock::when($isServeur, function ($query) use ($user) {
                $query->where("user_id", $user->id);
            })->when($saleDay, function ($query) use ($saleDay) {
                $query->where("sale_day_id", $saleDay->id);
            })->where("type_mouvement", "vente")
            ->when($emplacementId, function ($query) use ($user) {
                $query->where("emplacement_id", $user->emplacement_id);
            })
            ->where("ets_id", $user->ets_id)
            ->count();

        // Utilisateurs connectés
        $connectedUsers = User::whereHas('lastLog', function ($query) {
                $query->where('status', 'online');
            })
            ->when($emplacementId, function ($query) use ($user) {
                $query->where("emplacement_id", $user->emplacement_id);
            })
            ->where("ets_id", $user->ets_id)
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
        return $str;  /* str_pad((string)$str,2,  "0", STR_PAD_LEFT); */
    }


}
