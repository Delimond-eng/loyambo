<?php

namespace App\Http\Controllers\report;

use App\Http\Controllers\Controller;
use App\Models\Emplacement;
use App\Models\Facture;
use App\Models\Payments;
use App\Models\User;
use App\Support\ReportExporter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PerfomanceUserController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->buildPerformanceData($request);

        return view('reports.performance', $data);
    }

    public function exportPerformancePdf(Request $request)
    {
        $data = $this->buildPerformanceData($request);

        $headers = [
            'Employé',
            'Rôle',
            'Emplacement',
            'Statut',
            'Opérations',
            'Total encaissé',
            'Panier moyen',
            'Performance',
        ];

        $rows = collect($data['performances'])->map(function ($performance) {
            $employe = $performance['employe'];
            $emplacement = $employe->emplacement?->libelle ?? '-';
            $statut = $performance['est_actif'] ? 'Actif' : 'Inactif';
            return [
                $employe->name,
                $employe->role,
                $emplacement,
                $statut,
                $performance['nombre_commandes'],
                number_format($performance['total_encaissement'], 0, ',', ' ') . ' ' . $performance['devise_principale'],
                number_format($performance['panier_moyen'], 0, ',', ' ') . ' ' . $performance['devise_principale'],
                number_format($performance['pourcentage_performance'], 1) . '%',
            ];
        })->toArray();

        $pdf = Pdf::loadView('pdf.report_table', [
            'title' => 'Rapport de performance du personnel',
            'subtitle' => 'Serveurs et caissiers',
            'filters' => $this->formatFilters($request),
            'headers' => $headers,
            'rows' => $rows,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('rapport_performance_' . date('Ymd_His') . '.pdf');
    }

    public function exportPerformanceExcel(Request $request)
    {
        $data = $this->buildPerformanceData($request);

        $headers = [
            'Employé',
            'Rôle',
            'Emplacement',
            'Statut',
            'Opérations',
            'Total encaissé',
            'Panier moyen',
            'Performance',
        ];

        $rows = collect($data['performances'])->map(function ($performance) {
            $employe = $performance['employe'];
            $emplacement = $employe->emplacement?->libelle ?? '-';
            $statut = $performance['est_actif'] ? 'Actif' : 'Inactif';
            return [
                $employe->name,
                $employe->role,
                $emplacement,
                $statut,
                $performance['nombre_commandes'],
                $performance['total_encaissement'],
                $performance['panier_moyen'],
                $performance['pourcentage_performance'],
            ];
        })->toArray();

        return ReportExporter::toExcel(
            'rapport_performance_' . date('Ymd_His') . '.xlsx',
            'Performance',
            $headers,
            $rows
        );
    }

    private function buildPerformanceData(Request $request): array
    {
        $etsId = auth()->user()->ets_id;
        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');
        $emplacementId = $request->input('emplacement_id');
        $role = $request->input('role');
        $serviceType = $request->input('service_type');

        $emplacementsQuery = Emplacement::where('ets_id', $etsId)->orderBy('libelle');
        if ($serviceType) {
            $emplacementsQuery->where('type', $serviceType);
        }
        $emplacements = $emplacementsQuery->get();
        $serviceTypes = Emplacement::getTypesForEts($etsId);

        $query = User::where('ets_id', $etsId)->with(['emplacement']);
        if ($role) {
            $query->where('role', $role);
        } else {
            $query->whereIn('role', ['serveur', 'caissier']);
        }

        if ($emplacementId) {
            $query->where('emplacement_id', $emplacementId);
        }

        if ($serviceType) {
            $query->whereHas('emplacement', fn($q) => $q->where('type', $serviceType));
        }

        $employes = $query->get();

        $performances = [];
        $totalCommandesGlobal = 0;
        $totalEncaissementGlobal = 0;

        foreach ($employes as $employe) {
            if ($employe->role === 'caissier') {
                $paymentsQuery = Payments::where('user_id', $employe->id)
                    ->where('ets_id', $etsId);

                if ($emplacementId) {
                    $paymentsQuery->where('emplacement_id', $emplacementId);
                }
                if ($serviceType) {
                    $paymentsQuery->whereHas('emplacement', fn($q) => $q->where('type', $serviceType));
                }
                if ($dateDebut) {
                    $paymentsQuery->whereDate('pay_date', '>=', $dateDebut);
                }
                if ($dateFin) {
                    $paymentsQuery->whereDate('pay_date', '<=', $dateFin);
                }

                $payments = $paymentsQuery->get();
                $nombreCommandes = $payments->count();
                $totalEncaissement = $payments->sum('amount');
                $devisePrincipale = $payments->first()->devise ?? 'CDF';
                $typeActivite = 'Encaissements';
            } else {
                $facturesQuery = Facture::where('user_id', $employe->id)
                    ->where('statut', 'payée')
                    ->with(['payments']);

                if ($emplacementId) {
                    $facturesQuery->where('emplacement_id', $emplacementId);
                }
                if ($serviceType) {
                    $facturesQuery->whereHas('emplacement', fn($q) => $q->where('type', $serviceType));
                }
                if ($dateDebut) {
                    $facturesQuery->whereDate('date_facture', '>=', $dateDebut);
                }
                if ($dateFin) {
                    $facturesQuery->whereDate('date_facture', '<=', $dateFin);
                }

                $factures = $facturesQuery->get();
                $nombreCommandes = $factures->count();
                $totalEncaissement = $factures->sum('total_ttc');
                $devisePrincipale = $factures->first()?->payments?->first()?->devise ?? 'CDF';
                $typeActivite = 'Ventes';
            }

            $panierMoyen = $nombreCommandes > 0 ? $totalEncaissement / $nombreCommandes : 0;
            $pourcentagePerformance = $panierMoyen > 0 ? min(100, ($panierMoyen / 50000) * 100) : 0;

            $performances[] = [
                'employe' => $employe,
                'nombre_commandes' => $nombreCommandes,
                'total_encaissement' => $totalEncaissement,
                'panier_moyen' => $panierMoyen,
                'devise_principale' => $devisePrincipale,
                'pourcentage_performance' => $pourcentagePerformance,
                'est_actif' => $employe->actif,
                'type_activite' => $typeActivite,
            ];

            $totalCommandesGlobal += $nombreCommandes;
            $totalEncaissementGlobal += $totalEncaissement;
        }

        usort($performances, function ($a, $b) {
            return $b['total_encaissement'] <=> $a['total_encaissement'];
        });

        return [
            'performances' => $performances,
            'emplacements' => $emplacements,
            'serviceTypes' => $serviceTypes,
            'totalEmployes' => $employes->count(),
            'totalCommandes' => $totalCommandesGlobal,
            'totalEncaissement' => $totalEncaissementGlobal,
        ];
    }

    private function formatFilters(Request $request): string
    {
        $parts = [];
        if ($request->filled('service_type')) {
            $parts[] = 'Service: ' . $request->service_type;
        }
        if ($request->filled('emplacement_id')) {
            $libelle = Emplacement::where('id', (int) $request->emplacement_id)->value('libelle');
            $parts[] = 'Emplacement: ' . ($libelle ?? $request->emplacement_id);
        }
        if ($request->filled('role')) {
            $parts[] = 'Rôle: ' . $request->role;
        }
        if ($request->filled('date_debut') || $request->filled('date_fin')) {
            $parts[] = 'Période: ' . ($request->date_debut ?? '-') . ' au ' . ($request->date_fin ?? '-');
        }

        return implode(' | ', $parts);
    }
}
