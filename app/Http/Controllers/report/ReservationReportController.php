<?php

namespace App\Http\Controllers\report;

use App\Http\Controllers\Controller;
use App\Models\Chambre;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $etsId = $user->ets_id;
        $emplacementId = $user->emplacement_id;

        $query = Reservation::query()
            ->with([
                'client:id,nom,telephone,email,identite,identite_type',
                'chambre:id,numero,type,prix_devise,emplacement_id',
                'chambre.emplacement:id,libelle',
                'facture:id,reservation_id,numero_facture,total_ttc,devise,statut,date_facture',
                'facture.payments:id,facture_id,amount,devise,mode,mode_ref,pay_date,user_id',
                'facture.payments.user:id,name',
            ])
            ->where('ets_id', $etsId)
            ->whereNotNull('chambre_id')
            ->when($emplacementId, fn(Builder $q) => $q->where('emplacement_id', $emplacementId));

        $this->applyFilters($query, $request);

        $statsRows = (clone $query)->get();
        $stats = [
            'total' => $statsRows->count(),
            'confirmees' => $statsRows->filter(fn($r) => $this->startsWith((string) $r->statut, 'confirm'))->count(),
            'en_attente' => $statsRows->filter(fn($r) => (string) $r->statut === 'en_attente')->count(),
            'annulees' => $statsRows->filter(fn($r) => $this->startsWith((string) $r->statut, 'annul'))->count(),
            'montant_total' => $statsRows->sum(fn($r) => $this->reservationAmount($r)),
            'total_paye' => $statsRows->sum(fn($r) => (float) ($r->facture?->payments?->sum('amount') ?? 0)),
        ];

        $reservations = (clone $query)
            ->orderByDesc('created_at')
            ->paginate((int) $request->input('per_page', 15))
            ->withQueryString();

        $typesChambre = Chambre::query()
            ->where('ets_id', $etsId)
            ->when($emplacementId, fn(Builder $q) => $q->where('emplacement_id', $emplacementId))
            ->whereNotNull('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type')
            ->values();

        $modesPaiement = collect(['cash', 'mobile', 'card', 'virement', 'cheque']);

        return view('reports.reservations', compact(
            'reservations',
            'stats',
            'typesChambre',
            'modesPaiement'
        ));
    }

    public function show(int $id): JsonResponse
    {
        $user = Auth::user();

        $reservation = Reservation::query()
            ->with([
                'client:id,nom,telephone,email,identite,identite_type',
                'chambre:id,numero,type,prix_devise,emplacement_id',
                'chambre.emplacement:id,libelle',
                'facture:id,reservation_id,numero_facture,total_ttc,devise,statut,date_facture,user_id',
                'facture.user:id,name',
                'facture.payments:id,facture_id,amount,devise,mode,mode_ref,pay_date,user_id',
                'facture.payments.user:id,name',
            ])
            ->where('id', $id)
            ->where('ets_id', $user->ets_id)
            ->whereNotNull('chambre_id')
            ->when($user->emplacement_id, fn(Builder $q) => $q->where('emplacement_id', $user->emplacement_id))
            ->first();

        if (!$reservation) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation introuvable.',
            ], 404);
        }

        $montantReservation = $this->reservationAmount($reservation);
        $totalPaye = (float) ($reservation->facture?->payments?->sum('amount') ?? 0);
        $reste = max(0, $montantReservation - $totalPaye);

        return response()->json([
            'success' => true,
            'reservation' => [
                'id' => $reservation->id,
                'statut' => $reservation->statut,
                'type_sejour' => $reservation->type_sejour,
                'date_debut' => optional($reservation->date_debut)->format('Y-m-d'),
                'date_fin' => optional($reservation->date_fin)->format('Y-m-d'),
                'created_at' => optional($reservation->created_at)->format('Y-m-d H:i:s'),
                'prix_base' => (float) ($reservation->prix_base ?? 0),
                'prix_facture' => (float) ($reservation->prix_facture ?? 0),
            ],
            'client' => [
                'nom' => $reservation->client?->nom,
                'telephone' => $reservation->client?->telephone,
                'email' => $reservation->client?->email,
                'identite' => $reservation->client?->identite,
                'identite_type' => $reservation->client?->identite_type,
            ],
            'chambre' => [
                'numero' => $reservation->chambre?->numero,
                'type' => $reservation->chambre?->type,
                'devise' => $reservation->chambre?->prix_devise,
                'emplacement' => $reservation->chambre?->emplacement?->libelle,
            ],
            'facture' => $reservation->facture ? [
                'numero_facture' => $reservation->facture->numero_facture,
                'statut' => $reservation->facture->statut,
                'total_ttc' => (float) ($reservation->facture->total_ttc ?? 0),
                'devise' => $reservation->facture->devise,
                'date_facture' => optional($reservation->facture->date_facture)->format('Y-m-d H:i:s'),
                'caissier' => $reservation->facture->user?->name,
            ] : null,
            'payments' => $reservation->facture
                ? $reservation->facture->payments->map(function ($payment) {
                    return [
                        'amount' => (float) ($payment->amount ?? 0),
                        'devise' => $payment->devise,
                        'mode' => $payment->mode,
                        'mode_ref' => $payment->mode_ref,
                        'pay_date' => optional($payment->pay_date)->format('Y-m-d H:i:s'),
                        'caissier' => $payment->user?->name,
                    ];
                })->values()
                : [],
            'totaux' => [
                'montant_reservation' => $montantReservation,
                'total_paye' => $totalPaye,
                'reste' => $reste,
            ],
        ]);
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function (Builder $q) use ($search) {
                $q->whereHas('client', fn(Builder $sq) => $sq->where('nom', 'like', "%{$search}%"))
                    ->orWhereHas('chambre', fn(Builder $sq) => $sq->where('numero', 'like', "%{$search}%"))
                    ->orWhereHas('facture', fn(Builder $sq) => $sq->where('numero_facture', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('date_debut', '>=', $request->input('date_debut'));
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('date_fin', '<=', $request->input('date_fin'));
        }

        if ($request->filled('type_sejour')) {
            $query->where('type_sejour', $request->input('type_sejour'));
        }

        if ($request->filled('type_chambre')) {
            $typeChambre = $request->input('type_chambre');
            $query->whereHas('chambre', fn(Builder $sq) => $sq->where('type', $typeChambre));
        }

        if ($request->filled('statut_reservation')) {
            $status = $request->input('statut_reservation');
            if ($status === 'confirmee') {
                $query->where('statut', 'like', 'confirm%');
            } elseif ($status === 'terminee') {
                $query->where('statut', 'like', 'termin%');
            } elseif ($status === 'annulee') {
                $query->where('statut', 'like', 'annul%');
            } elseif ($status === 'en_attente') {
                $query->where('statut', 'en_attente');
            }
        }

        if ($request->filled('statut_paiement')) {
            $statusPaiement = $request->input('statut_paiement');
            if ($statusPaiement === 'non_facturee') {
                $query->whereDoesntHave('facture');
            } elseif ($statusPaiement === 'payee') {
                $query->whereHas('facture', fn(Builder $sq) => $sq->where('statut', 'like', 'pay%'));
            } elseif ($statusPaiement === 'partiel') {
                $query->whereHas('facture', fn(Builder $sq) => $sq->where('statut', 'like', 'part%'));
            } elseif ($statusPaiement === 'en_attente') {
                $query->whereHas('facture', fn(Builder $sq) => $sq->where('statut', 'en_attente'));
            }
        }

        if ($request->filled('mode_paiement')) {
            $mode = $request->input('mode_paiement');
            $query->whereHas('facture.payments', fn(Builder $sq) => $sq->where('mode', $mode));
        }
    }

    private function startsWith(string $value, string $prefix): bool
    {
        return str_starts_with(strtolower($value), strtolower($prefix));
    }

    private function reservationAmount(Reservation $reservation): float
    {
        return (float) ($reservation->prix_facture
            ?? $reservation->prix_base
            ?? $reservation->facture?->total_ttc
            ?? 0);
    }
}
