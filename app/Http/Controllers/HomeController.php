<?php

namespace App\Http\Controllers;

use App\Models\Currencie;
use App\Models\Facture;
use App\Models\FactureDetail;
use App\Models\Emplacement;
use App\Models\EmplacementProduit;
use App\Models\MouvementStock;
use App\Models\Payments;
use App\Models\Produit;
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
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function deleteFacture(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|exists:factures,id'
        ]);

        $facture = Facture::find($data['id']);
        if ($facture->statut !== 'en_attente') {
            return response()->json(['errors' => "Impossible de supprimer une facture déjà payée"], 422);
        }
        $facture->details()->delete();
        $facture->delete();

        return response()->json(['status' => 'success', 'message' => 'Facture supprimée']);
    }

    /**
     * Créer ou modifier une facture liée à l'emplacement sélectionné
     */
    public function saveFacture(Request $request)
    {
        try {
             $data = $request->validate([
                'facture_id' => 'nullable|exists:factures,id',
                'table_id' => 'nullable|exists:restaurant_tables,id',
                'chambre_id' => 'nullable|exists:chambres,id',
                'emplacement_id' => 'nullable|exists:emplacements,id',
                'user_id' => 'nullable|exists:users,id',
                'remise' => 'nullable|numeric|min:0',
                'details' => 'required|array|min:1',
                'details.*.produit_id' => 'required|exists:produits,id',
                'details.*.quantite' => 'required|integer|min:1',
                'details.*.prix_unitaire' => 'required|numeric|min:0',
            ]);

            $user = Auth::user();
            $serveur = User::find($data['user_id'] ?? null) ?? $user;

            $saleDay = SaleDay::whereNull('end_time')
                ->where('ets_id', $user->ets_id)
                ->latest()
                ->first();

            if (!$saleDay) {
                return response()->json(['errors' => 'La journée de vente n’est pas ouverte !'], 400);
            }

            $facture = DB::transaction(function () use ($data, $saleDay, $serveur) {
                if (!empty($data['facture_id'])) {
                    $facture = Facture::findOrFail($data['facture_id']);
                } else {
                    $facture = new Facture();
                    $facture->numero_facture = 'FAC-' . time();
                }

                $facture->user_id = $serveur->id;
                $facture->table_id = $data['table_id'] ?? null;
                $facture->chambre_id = $data['chambre_id'] ?? null;
                $facture->sale_day_id = $saleDay->id;
                $facture->remise = $data['remise'] ?? 0;
                $facture->statut = "en_attente";

                // Emplacement prioritaire : payload -> table -> chambre -> serveur
                $emplacementId = $data['emplacement_id'] ?? null;
                if (!$emplacementId && !empty($data['table_id'])) {
                    $emplacementId = RestaurantTable::where('id', $data['table_id'])->value('emplacement_id');
                }
                if (!$emplacementId && !empty($data['chambre_id'])) {
                    $emplacementId = \App\Models\Chambre::where('id', $data['chambre_id'])->value('emplacement_id');
                }
                $facture->emplacement_id = $emplacementId ?? $serveur->emplacement_id;

                $facture->ets_id = $serveur->ets_id;
                $facture->date_facture = Carbon::now('Africa/Kinshasa');
                // Valeurs par défaut pour passer les contraintes NOT NULL
                $facture->total_ht = 0;
                $facture->tva = 0;
                $facture->total_ttc = 0;
                // Sauvegarde initiale pour garantir un ID (utile lors d'une création)
                $facture->save();
                $facture->refresh();
                if (!$facture->id) {
                    throw new \RuntimeException("Impossible de créer la facture (ID manquant).");
                }

                // Calcul total HT et TVA avec prix par emplacement (fallback prix produit)
                $total_ht = 0;
                $tva = 0;
                $prixCache = [];

                $produitIds = [];
                foreach ($data['details'] as $detail) {
                    $produitId = (int) $detail['produit_id'];
                    if (!isset($prixCache[$produitId])) {
                        $prixCache[$produitId] = EmplacementProduit::where('produit_id', $produitId)
                            ->where('emplacement_id', $facture->emplacement_id)
                            ->value('prix');
                    }
                    $prix = $prixCache[$produitId] ?? (float) $detail['prix_unitaire'];
                    $ligneTotal = $prix * (int) $detail['quantite'];
                    $total_ht += $ligneTotal;

                    $product = Produit::find($produitId);
                    if ($product && $product->tva) {
                        $tva += $ligneTotal * 0.16;
                    }

                    $detailRecord = FactureDetail::updateOrCreate(
                        ['facture_id' => $facture->id, 'produit_id' => $produitId],
                        [
                            'quantite' => $detail['quantite'],
                            'prix_unitaire' => $prix,
                            'total_ligne' => $ligneTotal,
                        ]
                    );
                    $produitIds[] = $detailRecord->produit_id;
                }

                $facture->total_ht = $total_ht;
                $facture->tva = $tva;
                $facture->total_ttc = $total_ht - $facture->remise + $tva;
                $facture->save();

                FactureDetail::where('facture_id', $facture->id)->whereNotIn('produit_id', $produitIds)->delete();

                if (!empty($data['table_id'])) {
                    RestaurantTable::where('id', $data['table_id'])->update(['statut' => 'occupée']);
                }
                return $facture;
            });

            return response()->json([
                'status' => 'success',
                'result' => $facture->load('details.produit'),
                'message' => !empty($data['facture_id']) ? 'Facture modifiée avec succès !' : 'Facture créée avec succès !'
            ]);
        } catch (\Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 500);
        }
    }

    public function linkFactures(Request $request)
    {
        try {
            $data = $request->validate([
                'factures' => 'required|array|min:2',
                'factures.*' => 'required|exists:factures,id',
                'user_id' => 'nullable|exists:users,id'
            ]);

            return DB::transaction(function () use ($data) {
                $facturePrincipaleId = collect($data['factures'])->random();
                $facturePrincipale = Facture::with('details')->findOrFail($facturePrincipaleId);

                $facturesAFusionner = collect($data['factures'])->filter(fn($id) => $id != $facturePrincipaleId)->values()->all();
                $factures = Facture::with('details')->whereIn('id', $facturesAFusionner)->get();

                $total_ht = $facturePrincipale->total_ht;
                $tva = $facturePrincipale->tva;

                foreach ($factures as $f) {
                    foreach ($f->details as $d) {
                        $existingDetail = FactureDetail::where('facture_id', $facturePrincipale->id)->where('produit_id', $d->produit_id)->first();
                        if ($existingDetail) {
                            $existingDetail->update([
                                'quantite' => $existingDetail->quantite + $d->quantite,
                                'total_ligne' => ($existingDetail->quantite + $d->quantite) * $d->prix_unitaire,
                            ]);
                        } else {
                            FactureDetail::create([
                                'facture_id' => $facturePrincipale->id,
                                'produit_id' => $d->produit_id,
                                'quantite' => $d->quantite,
                                'prix_unitaire' => $d->prix_unitaire,
                                'total_ligne' => $d->quantite * $d->prix_unitaire,
                            ]);
                        }
                        $total_ht += ($d->quantite * $d->prix_unitaire);
                        $product = Produit::find($d->produit_id);
                        if ($product && $product->tva) $tva += (($d->quantite * $d->prix_unitaire) * 0.16);
                    }
                }

                $facturePrincipale->update(['total_ht' => $total_ht, 'tva' => $tva, 'total_ttc' => $total_ht - $facturePrincipale->remise + $tva]);
                FactureDetail::whereIn('facture_id', $facturesAFusionner)->delete();
                Facture::whereIn('id', $facturesAFusionner)->delete();

                return $facturePrincipale;
            });
        } catch (\Exception $e) { return response()->json(['errors' => $e->getMessage()], 500); }
    }

    public function getAllFacturesCmds(Request $request)
    {
        $user = Auth::user();
        $isServeur = $user->role === "serveur";
        $status = $request->query("status");
        $perPage = (int) ($request->query("per_page") ?? 10);

        $req = Facture::with(["details.produit", "table.emplacement", "chambre.emplacement", "payments", "saleDay", "user"])
            ->where("ets_id", $user->ets_id);

        if($status) $req->where("statut", "en_attente");
        if($isServeur) $req->where("user_id", $user->id);
        if($user->role !== "admin" && $user->emplacement_id) $req->where("emplacement_id", $user->emplacement_id);

        return response()->json(["status" => "success", "factures" => $req->orderByDesc("id")->paginate($perPage)]);
    }

    public function getAllSells(Request $request)
    {
        $dateRange = $request->query("dateRange");
        $serveurId = $request->query("serveur");
        $user = Auth::user();
        $saleDay = SaleDay::whereNull("end_time")->where("ets_id", $user->ets_id)->latest()->first();

        $req = MouvementStock::with(["produit", "facture.user"])
            ->selectRaw("produit_id, SUM(quantite) as total_vendu")
            ->where("type_mouvement", "vente")->where("ets_id", $user->ets_id);

        $req->when($dateRange, function ($q) use ($dateRange) {
            [$start, $end] = explode(";", $dateRange);
            $q->whereBetween("date_mouvement", [$start . " 00:00:00", $end . " 23:59:59"]);
        });

        $req->when($serveurId, fn($q) => $q->where("user_id", $serveurId));
        $req->when(!$dateRange, fn($q) => $q->where("sale_day_id", $saleDay->id ?? 0));
        if($user->role !== 'admin' && $user->emplacement_id) $req->where("emplacement_id", $user->emplacement_id);

        $sells = $req->groupBy("produit_id")->get();

        $sells->map(function ($item) use ($dateRange, $saleDay, $serveurId, $user) {
            $query = MouvementStock::with("facture.user")->selectRaw("numdoc, SUM(quantite) as quantite")
                ->where("type_mouvement", "vente")->where("produit_id", $item->produit_id)->whereNotNull("numdoc");

            $query->when($dateRange, function ($q) use ($dateRange) {
                [$start, $end] = explode(";", $dateRange);
                $q->whereBetween("date_mouvement", [$start . " 00:00:00", $end . " 23:59:59"]);
            });
            $query->when(!$dateRange, fn($q) => $q->where("sale_day_id", $saleDay->id ?? 0));
            if($user->role !== 'admin' && $user->emplacement_id) $query->where("emplacement_id", $user->emplacement_id);
            if($serveurId) $query->whereHas("facture", fn($q) => $q->where("user_id", $serveurId));

            $uData = $query->groupBy("numdoc")->get();
            $item->byUsers = $uData->map(fn($u) => [
                "facture_id" => $u->numdoc,
                "user_id" => $u->facture->user->id ?? null,
                "nom" => $u->facture->user->name ?? "Inconnu",
                "quantite" => $u->quantite,
                "montant" => $u->quantite * ($item->produit->prix_unitaire ?? 0),
            ]);
            return $item;
        });

        return response()->json(["status" => "success", "ventes" => $sells]);
    }

    public function dashboardCounter()
    {
        $user = Auth::user();
        $isServeur = $user->role === "serveur";
        $saleDay = SaleDay::whereNull("end_time")->where("ets_id", $user->ets_id)->latest()->first();
        $empId = $user->emplacement_id;

        $baseReq = Facture::where("ets_id", $user->ets_id)
            ->when($isServeur, fn($q) => $q->where("user_id", $user->id))
            ->when($saleDay, fn($q) => $q->where("sale_day_id", $saleDay->id))
            ->when($empId && !$isServeur, fn($q) => $q->where("emplacement_id", $empId));

        $pending = (clone $baseReq)->where("statut", "en_attente")->count();
        $all = (clone $baseReq)->count();
        $cancelled = (clone $baseReq)->where("statut", "annulée")->count();

        $sells = MouvementStock::where("ets_id", $user->ets_id)->where("type_mouvement", "vente")
            ->when($isServeur, fn($q) => $q->where("user_id", $user->id))
            ->when($saleDay, fn($q) => $q->where("sale_day_id", $saleDay->id))
            ->when($empId && !$isServeur, fn($q) => $q->where("emplacement_id", $empId))->count();

        $connected = User::whereHas('lastLog', fn($q) => $q->where('status', 'online'))
            ->where("ets_id", $user->ets_id)->when($empId && !$isServeur, fn($q) => $q->where("emplacement_id", $empId))->count();

        return response()->json(["status" => "success", "counts" => [
            "facs" => $all, "pendings" => $pending, "cancelled" => $cancelled, "users" => $connected, "sells" => $sells
        ]]);
    }

    public function dashboardStats(Request $request)
    {
        $user = Auth::user();
        $etsId = $user->ets_id;
        $now = Carbon::now('Africa/Kinshasa');
        $start = $now->copy()->subDays(6)->startOfDay();
        $end = $now->copy()->endOfDay();

        $baseQuery = Payments::where('ets_id', $etsId);
        if ($user->role === 'caissier') {
            $baseQuery->where('user_id', $user->id);
        }

        $summary = [
            'today' => (clone $baseQuery)->whereDate('pay_date', $now->toDateString())->sum('amount'),
            'week' => (clone $baseQuery)->whereBetween('pay_date', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])->sum('amount'),
            'month' => (clone $baseQuery)
                ->whereMonth('pay_date', $now->month)
                ->whereYear('pay_date', $now->year)
                ->sum('amount'),
        ];

        $dailyRaw = (clone $baseQuery)
            ->whereBetween('pay_date', [$start, $end])
            ->select(DB::raw('DATE(pay_date) as date'), DB::raw('SUM(amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $series = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i)->toDateString();
            $labels[] = Carbon::parse($day)->format('d/m');
            $series[] = (double) ($dailyRaw[$day]->total ?? 0);
        }

        $modesRaw = (clone $baseQuery)
            ->select('mode', DB::raw('SUM(amount) as total'))
            ->groupBy('mode')
            ->orderByDesc('total')
            ->get();

        $payload = [
            'status' => 'success',
            'role' => $user->role,
            'summary' => $summary,
            'daily' => [
                'labels' => $labels,
                'series' => $series,
            ],
            'modes' => [
                'labels' => $modesRaw->pluck('mode')->values(),
                'series' => $modesRaw->pluck('total')->map(fn($v) => (double) $v)->values(),
            ],
        ];

        if (in_array($user->role, ['admin', 'manager'])) {
            $servicesRaw = Payments::join('emplacements', 'payments.emplacement_id', '=', 'emplacements.id')
                ->where('payments.ets_id', $etsId)
                ->select('emplacements.type', DB::raw('SUM(payments.amount) as total'))
                ->groupBy('emplacements.type')
                ->orderByDesc('total')
                ->get();

            $emplacementsRaw = Payments::join('emplacements', 'payments.emplacement_id', '=', 'emplacements.id')
                ->where('payments.ets_id', $etsId)
                ->select('emplacements.libelle', DB::raw('SUM(payments.amount) as total'))
                ->groupBy('emplacements.libelle')
                ->orderByDesc('total')
                ->limit(5)
                ->get();

            $payload['services'] = [
                'labels' => $servicesRaw->pluck('type')->values(),
                'series' => $servicesRaw->pluck('total')->map(fn($v) => (double) $v)->values(),
            ];
            $payload['top_emplacements'] = [
                'labels' => $emplacementsRaw->pluck('libelle')->values(),
                'series' => $emplacementsRaw->pluck('total')->map(fn($v) => (double) $v)->values(),
            ];
        }

        return response()->json($payload);
    }
}
