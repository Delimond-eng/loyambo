<?php

namespace App\Http\Controllers\report;

use App\Models\Facture;
use App\Models\Emplacement;
use Illuminate\Http\Request;
use App\Models\FactureDetail;
use App\Http\Controllers\Controller;
use App\Support\ReportExporter;
use Barryvdh\DomPDF\Facade\Pdf;

class ProduitController extends Controller
{
    public function index(Request $request)
    {
        $etsId = auth()->user()->ets_id;
        $serviceType = $request->query('service_type');
        $emplacementsQuery = Emplacement::where('ets_id', $etsId)->orderBy('libelle');
        if ($serviceType) {
            $emplacementsQuery->where('type', $serviceType);
        }
        $emplacements = $emplacementsQuery->get();
        $serviceTypes = Emplacement::getTypesForEts($etsId);

        return view('reports.produitsPlusVendus', compact('emplacements', 'serviceTypes', 'serviceType'));
    }
   public function showProduitsPlusVendus(Request $request, $emplacement_id)
    {
        $dateDebut = $request->query('date_debut');
        $dateFin = $request->query('date_fin');
        $serviceType = $request->query('service_type');

        $emplacement = Emplacement::where('ets_id', auth()->user()->ets_id)
            ->where('id', $emplacement_id)
            ->first();
            
        if (!$emplacement) {
            return redirect()->route('reports.produits')->with('error', 'Cet emplacement n\'existe pas.');
        }
        if ($serviceType && $emplacement->type !== $serviceType) {
            return redirect()->route('reports.produits', ['service_type' => $serviceType])
                ->with('error', 'Cet emplacement ne correspond pas au service sélectionné.');
        }

        // Récupérer les produits les plus vendus pour cet emplacement
        $produitsVendus = $this->getProduitsPlusVendusParEmplacement($emplacement, $dateDebut, $dateFin);

        return view('reports.produitPlusVenduDetails', compact('emplacement', 'produitsVendus', 'dateDebut', 'dateFin', 'serviceType'));
    }

    private function getProduitsPlusVendusParEmplacement($emplacement, $dateDebut = null, $dateFin = null)
    {
        // Récupérer toutes les factures payées pour cet emplacement
        // Utilisation directe du champ emplacement_id dans Facture
        $factures = Facture::where('statut', 'payée')
            ->where('emplacement_id', $emplacement->id)
            ->where('ets_id', auth()->user()->ets_id)
            ->with(['details.produit.categorie', 'payments'])
            ->when($dateDebut, fn($query) => $query->whereDate('date_facture', '>=', $dateDebut))
            ->when($dateFin, fn($query) => $query->whereDate('date_facture', '<=', $dateFin))
            ->get();

        // Compiler les statistiques des produits
        $produitsStats = [];
        $devisePrincipale = 'CDF';

        foreach ($factures as $facture) {
            // Récupérer la devise
            if ($facture->payments->isNotEmpty() && $devisePrincipale === 'CDF') {
                $devisePrincipale = $facture->payments->first()->devise ?? 'CDF';
            }

            foreach ($facture->details as $detail) {
                $produitId = $detail->produit_id;
                
                if (!isset($produitsStats[$produitId])) {
                    $produitsStats[$produitId] = [
                        'produit' => $detail->produit,
                        'quantite_vendue' => 0,
                        'chiffre_affaires' => 0,
                        'nombre_factures' => 0,
                        'factures_ids' => []
                    ];
                }

                $produitsStats[$produitId]['quantite_vendue'] += $detail->quantite;
                $produitsStats[$produitId]['chiffre_affaires'] += $detail->total_ligne;
                
                // Compter le nombre de factures uniques pour ce produit
                if (!in_array($facture->id, $produitsStats[$produitId]['factures_ids'])) {
                    $produitsStats[$produitId]['factures_ids'][] = $facture->id;
                    $produitsStats[$produitId]['nombre_factures']++;
                }
            }
        }

        // Trier par quantité vendue (décroissant)
        usort($produitsStats, function($a, $b) {
            return $b['quantite_vendue'] - $a['quantite_vendue'];
        });

        return [
            'produits' => $produitsStats,
            'devise' => $devisePrincipale,
            'total_factures' => $factures->count(),
            'total_produits_vendus' => array_sum(array_column($produitsStats, 'quantite_vendue')),
            'chiffre_affaires_total' => $factures->sum('total_ttc')
        ];
    }

    // Méthode alternative avec requête plus optimisée
    private function getProduitsPlusVendusOptimise($emplacement)
    {
        // Version optimisée avec requête directe
        $produitsStats = FactureDetail::join('factures', 'facture_details.facture_id', '=', 'factures.id')
            ->join('produits', 'facture_details.produit_id', '=', 'produits.id')
            ->leftJoin('categories', 'produits.categorie_id', '=', 'categories.id')
            ->where('factures.statut', 'payée')
            ->where('factures.emplacement_id', $emplacement->id)
            ->where('factures.ets_id', auth()->user()->ets_id)
            ->selectRaw('
                produits.id,
                produits.libelle as produit_libelle,
                produits.reference as produit_reference,
                produits.code_barre as produit_code_barre,
                categories.libelle as categorie_libelle,
                SUM(facture_details.quantite) as quantite_vendue,
                SUM(facture_details.total_ligne) as chiffre_affaires,
                COUNT(DISTINCT factures.id) as nombre_factures
            ')
            ->groupBy('produits.id', 'produits.libelle', 'produits.reference', 'produits.code_barre', 'categories.libelle')
            ->orderByDesc('quantite_vendue')
            ->get();

        // Récupérer la devise principale
        $factureExemple = Facture::where('emplacement_id', $emplacement->id)
            ->where('statut', 'payée')
            ->where('ets_id', auth()->user()->ets_id)
            ->with('payments')
            ->first();
        
        $devisePrincipale = $factureExemple && $factureExemple->payments->isNotEmpty() 
            ? $factureExemple->payments->first()->devise 
            : 'CDF';

        // Statistiques globales
        $totalFactures = Facture::where('emplacement_id', $emplacement->id)
            ->where('statut', 'payée')
            ->where('ets_id', auth()->user()->ets_id)
            ->count();

        $chiffreAffairesTotal = Facture::where('emplacement_id', $emplacement->id)
            ->where('statut', 'payée')
            ->where('ets_id', auth()->user()->ets_id)
            ->sum('total_ttc');

        $totalProduitsVendus = $produitsStats->sum('quantite_vendue');

        return [
            'produits' => $produitsStats,
            'devise' => $devisePrincipale,
            'total_factures' => $totalFactures,
            'total_produits_vendus' => $totalProduitsVendus,
            'chiffre_affaires_total' => $chiffreAffairesTotal
        ];
    }

    public function exportProduitsPlusVendusPdf(Request $request, $emplacement_id)
    {
        $dateDebut = $request->query('date_debut');
        $dateFin = $request->query('date_fin');
        $serviceType = $request->query('service_type');

        $emplacement = Emplacement::where('ets_id', auth()->user()->ets_id)
            ->where('id', $emplacement_id)
            ->first();

        if (!$emplacement) {
            return redirect()->route('reports.produits')->with('error', 'Cet emplacement n\'existe pas.');
        }
        if ($serviceType && $emplacement->type !== $serviceType) {
            return redirect()->route('reports.produits', ['service_type' => $serviceType])
                ->with('error', 'Cet emplacement ne correspond pas au service sÃ©lectionnÃ©.');
        }

        $produitsVendus = $this->getProduitsPlusVendusParEmplacement($emplacement, $dateDebut, $dateFin);
        $devise = $produitsVendus['devise'] ?? 'CDF';

        $headers = ["Produit", "CatÃ©gorie", "QuantitÃ© vendue", "Chiffre d'affaires", "Factures", "Prix moyen"];
        $rows = [];
        foreach ($produitsVendus['produits'] as $produitData) {
            if (isset($produitData->quantite_vendue)) {
                $produitLibelle = $produitData->produit_libelle ?? '-';
                $categorie = $produitData->categorie_libelle ?? '-';
                $quantite = $produitData->quantite_vendue ?? 0;
                $chiffreAffaires = $produitData->chiffre_affaires ?? 0;
                $nombreFactures = $produitData->nombre_factures ?? 0;
            } else {
                $produitLibelle = $produitData['produit']->libelle ?? '-';
                $categorie = $produitData['produit']->categorie->libelle ?? '-';
                $quantite = $produitData['quantite_vendue'] ?? 0;
                $chiffreAffaires = $produitData['chiffre_affaires'] ?? 0;
                $nombreFactures = $produitData['nombre_factures'] ?? 0;
            }
            $prixMoyen = $quantite > 0 ? $chiffreAffaires / $quantite : 0;
            $rows[] = [
                $produitLibelle,
                $categorie,
                $quantite,
                number_format($chiffreAffaires, 0, ',', ' ') . ' ' . $devise,
                $nombreFactures,
                number_format($prixMoyen, 0, ',', ' ') . ' ' . $devise,
            ];
        }

        $pdf = Pdf::loadView('pdf.report_table', [
            'title' => 'Produits les plus vendus',
            'subtitle' => $emplacement->libelle,
            'filters' => $this->formatFilters($request),
            'headers' => $headers,
            'rows' => $rows,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('produits_plus_vendus_' . date('Ymd_His') . '.pdf');
    }

    public function exportProduitsPlusVendusExcel(Request $request, $emplacement_id)
    {
        $dateDebut = $request->query('date_debut');
        $dateFin = $request->query('date_fin');
        $serviceType = $request->query('service_type');

        $emplacement = Emplacement::where('ets_id', auth()->user()->ets_id)
            ->where('id', $emplacement_id)
            ->first();

        if (!$emplacement) {
            return redirect()->route('reports.produits')->with('error', 'Cet emplacement n\'existe pas.');
        }
        if ($serviceType && $emplacement->type !== $serviceType) {
            return redirect()->route('reports.produits', ['service_type' => $serviceType])
                ->with('error', 'Cet emplacement ne correspond pas au service sÃ©lectionnÃ©.');
        }

        $produitsVendus = $this->getProduitsPlusVendusParEmplacement($emplacement, $dateDebut, $dateFin);
        $devise = $produitsVendus['devise'] ?? 'CDF';

        $headers = ["Produit", "CatÃ©gorie", "QuantitÃ© vendue", "Chiffre d'affaires", "Factures", "Prix moyen"];
        $rows = [];
        foreach ($produitsVendus['produits'] as $produitData) {
            if (isset($produitData->quantite_vendue)) {
                $produitLibelle = $produitData->produit_libelle ?? '-';
                $categorie = $produitData->categorie_libelle ?? '-';
                $quantite = $produitData->quantite_vendue ?? 0;
                $chiffreAffaires = $produitData->chiffre_affaires ?? 0;
                $nombreFactures = $produitData->nombre_factures ?? 0;
            } else {
                $produitLibelle = $produitData['produit']->libelle ?? '-';
                $categorie = $produitData['produit']->categorie->libelle ?? '-';
                $quantite = $produitData['quantite_vendue'] ?? 0;
                $chiffreAffaires = $produitData['chiffre_affaires'] ?? 0;
                $nombreFactures = $produitData['nombre_factures'] ?? 0;
            }
            $prixMoyen = $quantite > 0 ? $chiffreAffaires / $quantite : 0;
            $rows[] = [
                $produitLibelle,
                $categorie,
                $quantite,
                $chiffreAffaires,
                $nombreFactures,
                $prixMoyen,
            ];
        }

        return ReportExporter::toExcel(
            'produits_plus_vendus_' . date('Ymd_His') . '.xlsx',
            'Produits vendus',
            $headers,
            $rows
        );
    }

    private function formatFilters(Request $request): string
    {
        $parts = [];
        if ($request->filled('service_type')) {
            $parts[] = 'Service: ' . $request->service_type;
        }
        if ($request->filled('date_debut') || $request->filled('date_fin')) {
            $parts[] = 'PÃ©riode: ' . ($request->date_debut ?? '-') . ' au ' . ($request->date_fin ?? '-');
        }
        return implode(' | ', $parts);
    }
}




