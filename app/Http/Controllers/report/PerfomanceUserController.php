<?php

namespace App\Http\Controllers\report;

use App\Models\User;
use App\Models\Facture;
use App\Models\Emplacement;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PerfomanceUserController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');
        $emplacementId = $request->input('emplacement_id');
        $role = $request->input('role');

        // 🔹 Emplacements visibles selon le rôle
        if ($user->role === 'admin') {
            $emplacements = Emplacement::where('ets_id', $user->ets_id)
                ->orderBy('libelle')
                ->get();
        } elseif ($user->role === 'caissier') {
            $emplacements = Emplacement::where('ets_id', $user->ets_id)
                ->where('id', $user->emplacement_id)
                ->orderBy('libelle')
                ->get();
        } else {
            return redirect()->back()->with('error', 'Accès non autorisé.');
        }

        // 🔹 Requête employés
        $query = User::where('ets_id', $user->ets_id)
            ->with('emplacement');

        if ($user->role === 'caissier') {
            $query->where('emplacement_id', $user->emplacement_id);
        }

        if ($user->role === 'admin' && $emplacementId) {
            $query->where('emplacement_id', $emplacementId);
        }

        if ($role) {
            $query->where('role', $role);
        }

        $employes = $query->get();

        // 🔹 Calcul des performances séparées par devise
        $performances = [];
        $totalCommandesGlobal = [];
        $totalEncaissementGlobal = [];

        foreach ($employes as $employe) {
            $facturesQuery = Facture::where('user_id', $employe->id)
                ->where('statut', 'payée')
                ->with('payments');

            if ($dateDebut) {
                $facturesQuery->whereDate('date_facture', '>=', $dateDebut);
            }
            if ($dateFin) {
                $facturesQuery->whereDate('date_facture', '<=', $dateFin);
            }

            $factures = $facturesQuery->get();

            // 🔹 Séparer les factures par devise
            $facturesParDevise = $factures->groupBy(function ($facture) {
                return $facture->payments->first()->devise ?? 'CDF';
            });

            $perfParDevise = [];
            foreach ($facturesParDevise as $devise => $facturesDevise) {
                $nombreCommandes = $facturesDevise->count();
                $totalEncaissement = $facturesDevise->sum('total_ttc');
                $panierMoyen = $nombreCommandes > 0 ? $totalEncaissement / $nombreCommandes : 0;

                $perfParDevise[$devise] = [
                    'nombre_commandes' => $nombreCommandes,
                    'total_encaissement' => $totalEncaissement,
                    'panier_moyen' => $panierMoyen,
                ];

                // 🔹 Totaux globaux par devise
                $totalCommandesGlobal[$devise] = ($totalCommandesGlobal[$devise] ?? 0) + $nombreCommandes;
                $totalEncaissementGlobal[$devise] = ($totalEncaissementGlobal[$devise] ?? 0) + $totalEncaissement;
            }

            $performances[] = [
                'employe' => $employe,
                'par_devise' => $perfParDevise,
                'est_actif' => $employe->actif,
            ];
        }

        return view('reports.performance', [
            'performances' => $performances,
            'emplacements' => $emplacements,
            'totalCommandes' => $totalCommandesGlobal,
            'totalEncaissement' => $totalEncaissementGlobal,
        ]);
    }
}
