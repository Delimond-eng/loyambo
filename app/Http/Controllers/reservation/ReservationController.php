<?php
namespace App\Http\Controllers\reservation;

use App\Http\Controllers\Controller;
use App\Models\Chambre;
use App\Models\Client;
use App\Models\Facture;
use App\Models\Payments;
use App\Models\Reservation;
use App\Models\SaleDay;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Log;

class ReservationController extends Controller
{
    private const STATUS_CONFIRMED = ['confirmée', 'confirmÃ©e', 'confirmÃƒÂ©e'];
    private const STATUS_OCCUPIED = ['occupée', 'occupÃ©e', 'occupÃƒÂ©e'];
    private const STATUS_RESERVED = ['réservée', 'rÃ©servÃ©e', 'rÃ©servÃƒÂ©e'];
    private const STATUS_CANCELLED = ['annulée', 'annulÃ©e', 'annulÃƒÂ©e'];
    private const STATUS_TERMINATED = ['terminée', 'terminÃ©e', 'terminÃƒÂ©e'];
    public function occupeChambre($chambreId)
    {
        try {
            // 1. Vérifier si la chambre existe
            $chambre = Chambre::findOrFail($chambreId);

            // 2. Trouver une réservation valide pour occuper la chambre
            $today = Carbon::today();

            $reservation = Reservation::where('chambre_id', $chambreId)
                ->whereIn('statut', self::STATUS_CONFIRMED)
                ->whereDate('date_debut', '<=', $today)  // peut être occupée dès aujourd’hui
                ->whereDate('date_fin', '>=', $today)    // la réservation doit toujours être active
                ->orderBy('date_debut', 'asc')
                ->first();

            if (!$reservation) {
                return response()->json([
                    'failed' => "Aucune réservation valide pour occuper cette chambre aujourd'hui."
                ]);
            }

            DB::transaction(function () use ($reservation, $chambre) {
                // 3. Mettre la chambre en statut occupée
                if (in_array($chambre->statut, self::STATUS_OCCUPIED, true)) {
                    $chambre->update([
                        'statut' => 'libre'
                    ]);
                }
                else{
                    $chambre->update([
                        'statut' => 'occupée'
                    ]);
                }
                
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Chambre occupée avec succès.',
                'reservation_id' => $reservation->id
            ]);

        } catch (\Exception $e) {
            Log::error("Erreur occupation chambre : " . $e->getMessage());

            return response()->json([
                'errors' => "Erreur : " . $e->getMessage(),
            ]);
        }
    }


    public function createReservationView()
    {
        $emplacement = auth()->user()->emplacement;

        $chambres = $emplacement ? $emplacement->chambres()->get() : Chambre::where("ets_id", auth()->user()->ets_id)->get();
        return view('reservation.create_reservation', compact('emplacement', 'chambres'));
    }

    public function reserverChambre(Request $request)
    {
        try {
            $data = $request->validate([
                'client.nom'          => 'required|string',
                'client.telephone'    => 'nullable|string|max:20',
                'client.email'        => 'nullable|email',
                'client.identite'     => 'required|string',
                'client.identite_type'=> 'required|string',
                'paiement.amount'     => 'nullable|numeric',
                'paiement.mode_ref'   => 'nullable|string',
                'paiement.mode'       => 'nullable|string',
                'paiement.devise'     => 'nullable|string',
                'chambre_id'          => 'required|exists:chambres,id',
                'date_debut'          => 'required|date|after_or_equal:today',
                'date_fin'            => 'required_if:type_sejour,nuit|date|after_or_equal:date_debut',
                'type_sejour'         => 'required|in:nuit,passage',
                'prix_negocie'        => 'nullable|numeric|min:0',
            ]);

            if ($data['type_sejour'] === 'passage') {
                $data['date_fin'] = $data['date_debut'];
            }

            $saleDay = SaleDay::whereNull("end_time")->where("ets_id", Auth::user()->ets_id)->latest()->first();

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
            $factureUrl = null;

            $reservation = DB::transaction(function () use ($data, $client, $saleDay, &$factureUrl) {
                $chambre = Chambre::lockForUpdate()->find($data['chambre_id']);
                $tarif = $this->calculTarif($chambre, $data['type_sejour'], $data['date_debut'], $data['date_fin'], $data['prix_negocie'] ?? null);
                // Vérifier la disponibilité
                $activeStatuses = array_merge(self::STATUS_CONFIRMED, self::STATUS_OCCUPIED);
                $query = Reservation::whereIn('statut', $activeStatuses)
                    ->where(function ($q) use ($data) {
                        $q->whereBetween('date_debut', [$data['date_debut'], $data['date_fin']])
                        ->orWhereBetween('date_fin', [$data['date_debut'], $data['date_fin']])
                        ->orWhere(function ($q2) use ($data) {
                            $q2->where('date_debut', '<=', $data['date_debut'])
                                ->where('date_fin', '>=', $data['date_fin']);
                        });
                    });

                $query->where('chambre_id', $chambre->id);

                if ($query->exists()) {
                    throw new Exception('Cette chambre est déjà réservée sur cette période.');
                }
                // Créer la réservation
                $reservation = Reservation::create([
                    'chambre_id' => $chambre->id ,
                    'client_id'  => $client->id,
                    'date_debut' => $data['date_debut'] ?? Carbon::now(),
                    'date_fin'   => $data['date_fin'],
                    'type_sejour'=> $data['type_sejour'],
                    'prix_base'  => $tarif['prix_base'],
                    'prix_facture' => $tarif['prix_applique'],
                    'remise_appliquee' => $tarif['remise'],
                    'sale_day_id'=> $saleDay->id ?? null,
                    'statut'     => "en_attente",
                    'emplacement_id'     => auth()->user()->emplacement_id,
                    'ets_id'     => auth()->user()->ets_id ?? null,
                ]);
                // Mettre à jour le statut de la ressource
                if ($chambre) $chambre->update(['statut' => 'réservée']);
                /* if ($table)   $table->update(['statut' => 'réservée']); */
                $paiement = $data["paiement"];
                if($reservation && !empty($paiement["mode"])){
                    $amount = (float)($paiement["amount"] ?? 0);
                    $facture = Facture::create([
                        'numero_facture'=> 'FAC-' . time(),
                        'user_id'=>Auth::id(),
                        'chambre_id'=> $reservation->chambre_id,
                        'reservation_id' => $reservation->id,
                        'sale_day_id'=> $saleDay->id ?? null,
                        'total_ht'=>$tarif['prix_applique'],
                        'remise'=>$tarif['remise'],
                        'total_ttc'=>$tarif['prix_applique'],
                        'tva'=> 0,
                        'devise'=>$reservation->chambre->prix_devise,
                        'date_facture'=>Carbon::today(tz:"Africa/Kinshasa"),
                        'ets_id'=>Auth::user()->ets_id,
                        'emplacement_id'=>Auth::user()->emplacement_id,
                        'statut'=>$amount >= $tarif['prix_applique'] ? 'payée' : 'partiel',
                    ]);

                    // --- Paiement ---
                    if ($amount > 0) {
                        Payments::create([
                            "amount"=>$amount,
                            "devise"=>$reservation->chambre->prix_devise,
                            "mode"=>$paiement["mode"],
                            "mode_ref"=>$paiement["mode_ref"] ?? null,
                            "pay_date"=>Carbon::today(tz:"Africa/Kinshasa"),
                            'emplacement_id'=>Auth::user()->emplacement_id,
                            'facture_id'=>$facture->id,
                            'chambre_id'=>$reservation->chambre_id,
                            'sale_day_id'=> $saleDay->id ?? null,
                            'user_id'=> Auth::id(),
                            'caissier_id'=> Auth::id(),
                            'ets_id'=> Auth::user()->ets_id,
                        ]);
                    }
                    $reservation->statut = "confirmée";
                    $reservation->save();
                    $factureUrl = "/reservation.facture/{$facture->id}";
                }
                return $reservation;
            });

            $response = [
                'message' => 'Réservation créée avec succès.',
                'status'  => 'success',
                'result'  => $reservation,
            ];

            if ($factureUrl) {
                $response['facture_url'] = $factureUrl;
            }
            return response()->json($response);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->validator->errors()->all()
            ]);
        } catch (Exception $e) {
            // Log interne pour debugging
            Log::error('Erreur réservation : '.$e->getMessage());
            return response()->json([
                'reserved' => $e->getMessage()
            ]);
        }
    }

    public function getReservationFacture(int $id)
    {
        $facture = Facture::with(['chambre','user', 'payments'])->find($id);

        if (!$facture) {
            throw new NotFoundHttpException("Facture introuvable.");
        }

        $reservation = Reservation::where('chambre_id', $facture->chambre_id)
            ->where('sale_day_id', $facture->sale_day_id)
            ->with(['client','chambre'])
            ->latest()
            ->first();

        if (!$reservation) {
            throw new NotFoundHttpException("Réservation introuvable.");
        }

        $totalPaye = $facture->payments->sum('amount');
        $resteAPayer = $facture->total_ttc - $totalPaye;

        $data = [
            'facture'       => $facture,
            'reservation'   => $reservation,
            'total_paye'    => $totalPaye,
            'reste_a_payer' => $resteAPayer,
            'date_entree'   => $reservation->date_debut,
            'date_sortie'   => $reservation->date_fin,
            'ets_nom'       => optional(Auth::user()->emplacement)->libelle,
            'ets_adresse'   => optional(Auth::user()->etablissement)->adresse,
            'ets_tel'       => optional(Auth::user()->etablissement)->telephone,
        ];

        return Pdf::loadView('pdf.reservation_facture', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'            => 'dejavu sans',
                'isHtml5ParserEnabled'   => true,
                'isRemoteEnabled'        => true,
            ])
            ->stream("facture-{$facture->numero_facture}.pdf");
    }
    
    public function getReservationDetails(int $id)
    {
        $facture = Facture::with(['chambre','user', 'payments'])->find($id);

        if (!$facture) {
            throw new NotFoundHttpException("Facture introuvable.");
        }

        $reservation = Reservation::where('chambre_id', $facture->chambre_id)
            ->where('sale_day_id', $facture->sale_day_id)
            ->with(['client','chambre'])
            ->latest()
            ->first();

        if (!$reservation) {
            throw new NotFoundHttpException("Réservation introuvable.");
        }

        $totalPaye = $facture->payments->sum('amount');
        $resteAPayer = $facture->total_ttc - $totalPaye;

        $data = [
            'facture'       => $facture,
            'reservation'   => $reservation,
            'total_paye'    => $totalPaye,
            'reste_a_payer' => $resteAPayer,
            'date_entree'   => $reservation->date_debut,
            'date_sortie'   => $reservation->date_fin,
            'ets_nom'       => optional(Auth::user()->emplacement)->libelle,
            'ets_adresse'   => optional(Auth::user()->etablissement)->adresse,
            'ets_tel'       => optional(Auth::user()->etablissement)->telephone,
        ];

        return view("reservation.reservation_details", $data);
    }

    public function modifierReservation(Request $request)
    {
        try {
            $data = $request->validate([
                'reservation_id' => 'required|exists:reservations,id',
                'chambre_id' => 'nullable|exists:chambres,id',
                'date_debut' => 'required|date|after_or_equal:today',
                'date_fin'   => 'required|date|after_or_equal:date_debut',
                'type_sejour'=> 'nullable|in:nuit,passage',
                'prix_negocie' => 'nullable|numeric|min:0',
                'remove_payment' => 'nullable|boolean',
                'paiement.amount' => 'nullable|numeric',
                'paiement.mode'   => 'nullable|in:cash,mobile,cheque,virement,card',
                'paiement.mode_ref'=> 'nullable|string',
            ]);
            $factureUrl = null;

            $reservation = Reservation::with(['facture', 'chambre'])->findOrFail((int) $data['reservation_id']);

            $effectiveType = $data['type_sejour'] ?? $reservation->type_sejour ?? 'nuit';
            if ($effectiveType === 'passage') {
                $data['date_fin'] = $data['date_debut'];
            }

            DB::transaction(function () use ($reservation, $data, &$factureUrl) {

                /**
                 * 1. Vérification disponibilité
                 */
                $query = Reservation::where('id', '!=', $reservation->id)
                    ->whereIn('statut', array_merge(self::STATUS_CONFIRMED, self::STATUS_OCCUPIED))
                    ->where(function ($q) use ($data) {
                        $q->whereBetween('date_debut', [$data['date_debut'], $data['date_fin']])
                        ->orWhereBetween('date_fin', [$data['date_debut'], $data['date_fin']])
                        ->orWhere(function ($q2) use ($data) {
                            $q2->where('date_debut', '<=', $data['date_debut'])
                                ->where('date_fin', '>=', $data['date_fin']);
                        });
                    });

                if (isset($data['chambre_id'])) {
                    $query->where('chambre_id', $data['chambre_id']);
                }

                if ($query->exists()) {
                    throw new Exception("Chambre déjà réservée sur cette période.");
                }

                /**
                 * 2. Gestion changement de chambre
                 */
                if (isset($data['chambre_id']) && $reservation->chambre_id != $data['chambre_id']) {

                    // libérer l'ancienne chambre si elle n'est plus utilisée
                    if ($reservation->chambre_id) {
                        $autres = Reservation::where('chambre_id', $reservation->chambre_id)
                            ->where('id', '!=', $reservation->id)
                            ->whereIn('statut', array_merge(self::STATUS_CONFIRMED, self::STATUS_OCCUPIED))
                            ->exists();

                        if (!$autres) {
                            Chambre::where('id', $reservation->chambre_id)->update(['statut' => 'libre']);
                        }
                    }

                    // réserver la nouvelle chambre
                    Chambre::where('id', $data['chambre_id'])->update(['statut' => 'réservée']);
                }

                $pricing = $this->calculTarif(
                    $reservation->chambre,
                    $effectiveType,
                    $data['date_debut'],
                    $data['date_fin'],
                    $data['prix_negocie'] ?? null
                );

                $reservation->update([
                    'chambre_id' => $data['chambre_id'] ?? $reservation->chambre_id,
                    'date_debut' => $data['date_debut'],
                    'date_fin'   => $data['date_fin'],
                    'type_sejour'=> $effectiveType,
                    'prix_base'  => $pricing['prix_base'],
                    'prix_facture' => $pricing['prix_applique'],
                    'remise_appliquee' => $pricing['remise'],
                ]);

                $paiement = $data['paiement'] ?? null;
                $facture = $reservation->facture;
                $hasPaymentMode = $paiement && !empty($paiement['mode']);

                if ($hasPaymentMode) {
                    if ($facture) {
                        $facture->update([
                            'total_ht'  => $pricing['prix_applique'],
                            'total_ttc' => $pricing['prix_applique'],
                            'remise'    => $pricing['remise'],
                            'statut'    => ($paiement['amount'] ?? 0) >= $pricing['prix_applique'] ? 'payée' : 'partiel',
                        ]);

                        Payments::where('facture_id', $facture->id)->delete();

                        if (($paiement['amount'] ?? 0) > 0) {
                            Payments::create([
                                'amount'       => $paiement['amount'],
                                'devise'       => $reservation->chambre->prix_devise,
                                'mode'         => $paiement['mode'],
                                'mode_ref'     => $paiement['mode_ref'] ?? null,
                                'pay_date'     => Carbon::now('Africa/Kinshasa'),
                                'facture_id'   => $facture->id,
                                'chambre_id'   => $reservation->chambre_id,
                                'user_id'      => Auth::id(),
                                'ets_id'       => Auth::user()->ets_id,
                                'emplacement_id'=> Auth::user()->emplacement_id,
                            ]);
                        }
                    } else {
                        $facture = Facture::create([
                            'numero_facture'  => "FAC-MOD-" . time(),
                            'user_id'         => Auth::id(),
                            'chambre_id'      => $reservation->chambre_id,
                            'reservation_id'  => $reservation->id,
                            'total_ht'        => $pricing['prix_applique'],
                            'total_ttc'       => $pricing['prix_applique'],
                            'remise'          => $pricing['remise'],
                            'tva'             => 0,
                            'devise'          => $reservation->chambre->prix_devise,
                            'date_facture'    => Carbon::now('Africa/Kinshasa'),
                            'ets_id'          => Auth::user()->ets_id,
                            'emplacement_id'  => Auth::user()->emplacement_id,
                            'statut'          => ($paiement['amount'] ?? 0) >= $pricing['prix_applique'] ? 'payée' : 'partiel'
                        ]);

                        if (($paiement['amount'] ?? 0) > 0) {
                            Payments::create([
                                'amount'        => $paiement['amount'],
                                'devise'        => $reservation->chambre->prix_devise,
                                'mode'          => $paiement['mode'],
                                'mode_ref'      => $paiement['mode_ref'] ?? null,
                                'pay_date'      => Carbon::now('Africa/Kinshasa'),
                                'facture_id'    => $facture->id,
                                'chambre_id'    => $reservation->chambre_id,
                                'user_id'       => Auth::id(),
                                'ets_id'        => Auth::user()->ets_id,
                                'emplacement_id'=> Auth::user()->emplacement_id,
                            ]);
                        }
                    }
                    $factureUrl = "/reservation.facture/{$facture->id}";
                }

                /**
                 * 4B. Aucun mode de paiement → supprimer facture + paiements
                 */
                if (($data['remove_payment'] ?? false) && $facture) {
                    Payments::where('facture_id', $facture->id)->delete();
                    $facture->delete();
                    $reservation->update([
                        'statut'  => 'en_attente',
                    ]);
                }
            });


            $response = [
                'status'  => 'success',
                'message' => 'Réservation modifiée avec succès',
                'data'    => $reservation
            ];

            if ($factureUrl) {
                $response['facture_url'] = $factureUrl;
            }
            return response()->json($response);

        } catch (\Exception $e) {
            Log::error("Erreur modification réservation : ".$e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function extendReservationDay(Request $request)
    {
        try {
            $data = $request->validate([
                'new_date_fin' => 'required|date|after_or_equal:today',
                'paiement.amount' => 'nullable|numeric',
                'paiement.mode'   => 'nullable|string',
                'paiement.mode_ref' => 'nullable|string',
                'reservation_id' => 'required|int|exists:reservations,id',
            ]);

            $reservation = Reservation::with(['facture', 'chambre'])->findOrFail((int)$data["reservation_id"]);

            $saleDay = SaleDay::whereNull("end_time")
                ->where("ets_id", Auth::user()->ets_id)
                ->latest()
                ->first();

            $oldDateFin = Carbon::parse($reservation->date_fin);
            $newDateFin = Carbon::parse($data['new_date_fin']);
            $today = Carbon::today();

            // Si ancienne date < aujourd’hui → prolongation commence aujourd’hui
            $startExtend = $oldDateFin->lt($today) ? $today : $oldDateFin;

            if ($newDateFin->lte($startExtend)) {
                throw new Exception(
                    "La nouvelle date doit être supérieure à la date de prolongation effective : " 
                    . $startExtend->toDateString()
                );
            }

            // Vérifier disponibilité de la chambre (exclure la réservation courante)
            $check = Reservation::whereIn('statut', array_merge(self::STATUS_CONFIRMED, self::STATUS_OCCUPIED))
                ->where('chambre_id', $reservation->chambre_id)
                ->where('id', '!=', $reservation->id)                            // <- important
                ->where(function($q) use ($startExtend, $newDateFin) {
                    $q->whereBetween('date_debut', [$startExtend->toDateString(), $newDateFin->toDateString()])
                    ->orWhereBetween('date_fin', [$startExtend->toDateString(), $newDateFin->toDateString()])
                    ->orWhere(function ($q2) use ($startExtend, $newDateFin) {
                        $q2->where('date_debut', '<=', $startExtend->toDateString())
                            ->where('date_fin', '>=', $newDateFin->toDateString());
                    });
                })
                ->exists();

            if ($check) {
                throw new Exception('Impossible de prolonger : la chambre est déjà réservée dans cette période.');
            }

            // Nombre de jours ajoutés
            $daysAdded = $startExtend->diffInDays($newDateFin);
            if ($daysAdded <= 0) {
                throw new Exception('Aucun jour ajouté.');
            }

            // Prix
            $prixJour = $reservation->type_sejour === 'passage' ? ($reservation->chambre->prix_passage ?? $reservation->chambre->prix) : ($reservation->chambre->prix_nuit ?? $reservation->chambre->prix);
            $totalHT = (float)$prixJour * $daysAdded;

            $factureUrl = null;

            // transaction automatique (rollback si exception)
            DB::transaction(function () use ($reservation, $newDateFin, $saleDay, $data, $totalHT, $daysAdded, &$factureUrl) {

                // 1. Mise à jour de la date de fin
                $reservation->update([
                    'date_fin' => $newDateFin->toDateString(),
                ]);

                // 2. Gestion facture et paiement
                $factureExistante = $reservation->facture;
                $paiement = $data['paiement'] ?? null;
                $hasPaymentMode = isset($paiement['mode']) && !empty($paiement['mode']);

                if ($factureExistante) {
                    // → Modifier la facture EXISTANTE : additionner le montant
                    $newTotal = (float)$factureExistante->total_ht + $totalHT;

                    $factureExistante->update([
                        'total_ht'  => $newTotal,
                        'total_ttc' => $newTotal,
                        'statut'    => $factureExistante->statut,
                    ]);

                    // → Si mode paiement fourni → ajouter un paiement (ne supprime pas les anciens paiements)
                    if ($hasPaymentMode) {
                        Payments::create([
                            "amount" => $totalHT,
                            "devise" => $reservation->chambre->prix_devise,
                            "mode" => $paiement['mode'],
                            "mode_ref" => $paiement['mode_ref'] ?? null,
                            "pay_date" => Carbon::today("Africa/Kinshasa"),
                            'emplacement_id' => Auth::user()->emplacement_id,
                            'facture_id' => $factureExistante->id,
                            'chambre_id' => $reservation->chambre_id,
                            'sale_day_id' => $saleDay->id ?? null,
                            'user_id' => Auth::id(),
                            'caissier_id' => Auth::id(),
                            'ets_id' => Auth::user()->ets_id,
                        ]);
                    }
                    $totalPaye = Payments::where('facture_id', $factureExistante->id)->sum('amount');
                    $factureExistante->update([
                        'statut' => $totalPaye >= $factureExistante->total_ttc ? 'payée' : ($hasPaymentMode ? 'partiel' : $factureExistante->statut),
                    ]);
                } else {
                    // → Aucune facture n’existe encore → en créer une
                    $facture = Facture::create([
                        'numero_facture' => 'FAC-EXT-' . time(),
                        'user_id' => Auth::id(),
                        'chambre_id' => $reservation->chambre_id,
                        'sale_day_id' => $saleDay->id ?? null,
                        'total_ht' => $totalHT,
                        'remise' => 0,
                        'total_ttc' => $totalHT,
                        'tva' => 0,
                        'devise' => $reservation->chambre->prix_devise,
                        'date_facture' => Carbon::today("Africa/Kinshasa"),
                        'ets_id' => Auth::user()->ets_id,
                        'emplacement_id' => Auth::user()->emplacement_id,
                        'statut' => $hasPaymentMode ? (($paiement['amount'] ?? 0) >= $totalHT ? 'payée' : 'partiel') : 'en_attente',
                        'reservation_id' => $reservation->id,
                    ]);
                    $factureUrl = "/reservation.facture/{$facture->id}";
                    // Paiement si mode renseigné
                    if ($hasPaymentMode) {
                        Payments::create([
                            "amount" => $totalHT,
                            "devise" => $reservation->chambre->prix_devise,
                            "mode" => $paiement['mode'],
                            "mode_ref" => $paiement['mode_ref'] ?? null,
                            "pay_date" => Carbon::today("Africa/Kinshasa"),
                            'emplacement_id' => Auth::user()->emplacement_id,
                            'facture_id' => $facture->id,
                            'chambre_id' => $reservation->chambre_id,
                            'sale_day_id' => $saleDay->id ?? null,
                            'user_id' => Auth::id(),
                            'caissier_id' => Auth::id(),
                            'ets_id' => Auth::user()->ets_id,
                        ]);
                    }
                    if ($facture) {
                        $totalPaye = Payments::where('facture_id', $facture->id)->sum('amount');
                        $facture->update([
                            'statut' => $totalPaye >= $facture->total_ttc ? 'payée' : ($hasPaymentMode ? 'partiel' : $facture->statut),
                        ]);
                    }
                }
                // 3. Réactiver / confirmer la réservation
                $reservation->update(['statut' => "confirmée"]);
            });


            $response = [
                'status' => 'success',
                'message' => 'Prolongation effectuée avec succès.',
                'days_added' => $daysAdded,
                'new_date_fin' => $newDateFin->toDateString(),
            ];

            if ($factureUrl) {
                $response['facture_url'] = $factureUrl;
            }
            return response()->json($response);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->validator->errors()->all()
            ]);

        } catch (Exception $e) {
            Log::error('Erreur prolongation réservation : '.$e->getMessage());
            return response()->json([
                'failed' => $e->getMessage()
            ]);
        }
    }


    public function payerReservation(Request $request)
    {
        try {
            $data = $request->validate([
                'mode'           => 'required|string',
                'mode_ref'       => 'nullable|string',
                'amount'         => 'required|numeric|min:1',
                'reservation_id' => 'required|exists:reservations,id',
            ]);

            $reservation = Reservation::find((int)$data["reservation_id"]);

            if (!$reservation) {
                throw new Exception("Réservation introuvable !");
            }

            $saleDay = SaleDay::whereNull("end_time")
                ->where("ets_id", Auth::user()->ets_id)
                ->latest()
                ->first();

            $pricing = $this->calculTarif(
                $reservation->chambre,
                $reservation->type_sejour ?? 'nuit',
                $reservation->date_debut,
                $reservation->date_fin,
                $reservation->prix_facture ?? null
            );
            $amount = (float)$data["amount"];

            $facture = $reservation->facture;
            if (!$facture) {
                $facture = Facture::create([
                    'numero_facture'=> 'FAC-' . time(),
                    'user_id'=>Auth::id(),
                    'chambre_id'=> $reservation->chambre_id,
                    'reservation_id'=> $reservation->id,
                    'sale_day_id'=> $saleDay->id ?? null,
                    'total_ht'=>$pricing['prix_applique'],
                    'remise'=>$pricing['remise'],
                    'total_ttc'=>$pricing['prix_applique'],
                    'tva'=> 0,
                    'devise'=>$reservation->chambre->prix_devise,
                    'date_facture'=>Carbon::today(tz:"Africa/Kinshasa"),
                    'ets_id'=>Auth::user()->ets_id,
                    'emplacement_id'=>Auth::user()->emplacement_id,
                    'statut'=>'en_attente',
                ]);
            }

            $payment = Payments::create([
                "amount"=>$amount,
                "devise"=>$reservation->chambre->prix_devise,
                "mode"=>$data["mode"],
                "mode_ref"=>$data["mode_ref"],
                "pay_date"=>Carbon::today(tz:"Africa/Kinshasa"),
                'emplacement_id'=>Auth::user()->emplacement_id,
                'facture_id'=>$facture->id,
                'chambre_id'=>$reservation->chambre_id,
                'sale_day_id'=> $saleDay->id ?? null,
                'user_id'=> Auth::id(),
                'caissier_id'=> Auth::id(),
                'ets_id'=> Auth::user()->ets_id,
            ]);

            $totalPaye = Payments::where('facture_id', $facture->id)->sum('amount');
            $facture->update([
                'statut' => $totalPaye >= $facture->total_ttc ? 'payée' : 'partiel',
            ]);

            $reservation->statut = "confirmée";
            $reservation->save();

            $response = [
                'message' => 'Paiement effectué avec succès.',
                'status'=>"success",
                'result' => $payment,
                'facture_url' => "/reservation.facture/{$facture->id}",
            ];

            return response()->json($response);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->validator->errors()->all()
            ]);
        } catch (Exception $e) {
            Log::error('Erreur paiement : '.$e->getMessage());
            return response()->json([
                'errors' => $e->getMessage()
            ]);
        }
    }

    public function annulerReservation($id)
    {
        try {
            $reservation = Reservation::with(['facture', 'chambre'])->findOrFail($id);
            DB::transaction(function () use ($reservation) {
                // 1. Vérifier si la chambre est occupée par une autre réservation confirmée
                if ($reservation->chambre_id) {
                    $autresReservations = Reservation::where('chambre_id', $reservation->chambre_id)
                        ->whereIn('statut', array_merge(self::STATUS_CONFIRMED, self::STATUS_OCCUPIED))
                        ->where('id', '!=', $reservation->id)
                        ->where('date_fin', '>=', now())
                        ->exists();

                    if (!$autresReservations) {
                        Chambre::where('id', $reservation->chambre_id)
                            ->update(['statut' => 'libre']);
                    }
                }
                // 2. Supprimer les paiements + facture s'il y en a une
                if ($reservation->facture) {
                    Payments::where('facture_id', $reservation->facture->id)->delete();
                    $reservation->facture->delete();
                }
                // 3. Mettre la réservation à annulée
                $reservation->update(['statut' => 'annulée']);
            });
            return response()->json([
                'status' => "success",
                'message' => "Réservation annulée avec succès."
            ]);

        } catch (\Exception $e) {
            Log::error("Erreur annulation réservation : " . $e->getMessage());
            return response()->json([
                'errors' => $e->getMessage(),
            ]);
        }
    }

    public function viewAllReservations(Request $request){
        Artisan::call('reservations:update');
        $user = Auth::user();
        $reservations = Reservation::with(["chambre","facture.payments", "client"])
                        ->where("ets_id", $user->ets_id)
                        ->when($user->emplacement_id, fn($q)=>$q->where("emplacement_id", $user->emplacement_id))
                        ->orderByDesc("created_at")
                        ->paginate(5);

        return response()->json([
            "status"=>"success",
            "reservations"=>$reservations
        ]);
    }

    private function calculTarif(Chambre $chambre, string $typeSejour, $dateDebut, $dateFin, ?float $prixNegocie): array
    {
        $jours = Carbon::parse($dateDebut)->diffInDays(Carbon::parse($dateFin));
        $jours = max(1, $jours);
        $prixUnitaire = $typeSejour === 'passage' ? ($chambre->prix_passage ?? $chambre->prix) : ($chambre->prix_nuit ?? $chambre->prix);
        $prixBase = (float)$prixUnitaire * $jours;
        $prixApplique = $prixNegocie !== null ? $prixNegocie : $prixBase;
        $prixApplique = max(0, $prixApplique);
        $remise = max(0, $prixBase - $prixApplique);

        return [
            'prix_base' => $prixBase,
            'prix_applique' => $prixApplique,
            'remise' => $remise,
            'jours' => $jours,
        ];
    }
}
