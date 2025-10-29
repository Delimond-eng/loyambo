<?php

namespace App\Http\Controllers\reservation;

use Log;
use Exception;
use Carbon\Carbon;
use App\Models\Client;
use App\Models\Chambre;
use App\Models\Facture;
use App\Models\SaleDay;
use App\Models\Payments;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Pdf;

class ReservationController extends Controller
{
    public function editReservationView($reservation_id)
{
    $emplacement = auth()->user()->emplacement;

    $reservation = Reservation::with('client', 'chambre')->find($reservation_id);
    if (!$reservation) {
        return redirect()->route('Reservations')->with('error', 'Réservation non trouvée.');
    }

    // Vérifier que la réservation appartient à l'emplacement de l'utilisateur
    if ($reservation->chambre->emplacement_id !== $emplacement->id) {
        return redirect()->route('Reservations')->with('error', 'Vous n\'êtes pas autorisé à modifier cette réservation.');
    }

    // Récupérer les chambres disponibles + la chambre actuelle de la réservation
    $chambres = $emplacement->chambres()
                           ->where(function($query) use ($reservation) {
                               $query->where('statut', 'libre')
                                     ->orWhere('statut', 'réservée')
                                     ->orWhere('id', $reservation->chambre_id);
                           })
                           ->get();

    return view('reservation.edit_reservation', compact('emplacement', 'reservation', 'chambres'));
}
public function payReservationView($reservation_id,Request $request)
{
    $emplacement = auth()->user()->emplacement;

    $reservation = Reservation::with('client', 'chambre')->find($reservation_id);
    if (!$reservation) {
        return redirect()->route('Reservations')->with('error', 'Réservation non trouvée.');
    }

    // Vérifier que la réservation appartient à l'emplacement de l'utilisateur
    if ($reservation->chambre->emplacement_id !== $emplacement->id) {
        return redirect()->route('Reservations')->with('error', 'Vous n\'êtes pas autorisé à accéder à cette réservation.');
    }
    $mode = $request->query('mode');
    $reservation->statut = 'confirmée';
    $reservation->save();
    $facture= Facture::where('chambre_id', $reservation->chambre_id)
        ->where('client_id', $reservation->client_id)
        ->first();
    if($facture){
        $facture->statut='payée';
        $facture->save();
    }
    $paiement= Payments::create([
         "amount"=>$facture->total_ttc,
        "devise"=>$facture->devise,
        "mode"=>$mode,
        "mode_ref"=>$mode."-".time(),
        "pay_date"=>now(),
        "emplacement_id"=>$emplacement->id,
        "facture_id"=>$facture->id,
        "table_id"=>null,
        "chambre_id"=>$reservation->chambre_id,
        "sale_day_id"=>$facture->sale_day_id,
        "user_id"=>Auth::user()->id,
        "ets_id"=>$emplacement->ets_id,
    ]);
    return redirect()->back()->with("success","Paiement effectué avec succès");
}

public function updateReservation(Request $request, $reservation_id)
{
    $emplacement = auth()->user()->emplacement;
    $reservation = Reservation::with('client', 'chambre')->findOrFail($reservation_id);

    // Vérifier les autorisations
    if ($reservation->chambre->emplacement_id !== $emplacement->id) {
        return redirect()->route('Reservations')->with('error', 'Vous n\'êtes pas autorisé à modifier cette réservation.');
    }

    // Validation des données
    $request->validate([
        'client_nom' => 'required|string|max:255',
        'client_telephone' => 'nullable|string|max:20',
        'client_email' => 'nullable|email|max:255',
        'client_identite' => 'nullable|string|max:50',
        'chambre_id' => 'required|exists:chambres,id',
        'date_debut' => 'required|date',
        'date_fin' => 'required|date|after:date_debut',
        'observations' => 'nullable|string|max:500',
        'montant_total' => 'required|numeric|min:0',
        'devise' => 'required|string',
    ]);

    try {
        DB::beginTransaction();

        // Vérifier si la chambre est disponible (sauf si c'est la même chambre)
        if ($request->chambre_id != $reservation->chambre_id) {
            $nouvelleChambre = Chambre::find($request->chambre_id);
            
            if ($nouvelleChambre->statut != 'libre') {
                // Vérifier les conflits de réservation pour la nouvelle chambre
                $conflit = Reservation::where('chambre_id', $request->chambre_id)
                    ->where('id', '!=', $reservation_id)
                    ->where(function($query) use ($request) {
                        $query->where(function($q) use ($request) {
                            $q->where('date_debut', '<', $request->date_fin)
                              ->where('date_fin', '>', $request->date_debut);
                        });
                    })
                    ->where('statut', '!=', 'annulée')
                    ->exists();

                if ($conflit) {
                    return redirect()->back()
                        ->with('error', 'La chambre sélectionnée n\'est pas disponible pour les dates choisies.')
                        ->withInput();
                }
            }
        }

        // Vérifier les conflits de dates pour la chambre actuelle/nouvelle
        $conflitDates = Reservation::where('chambre_id', $request->chambre_id)
            ->where('id', '!=', $reservation_id)
            ->where(function($query) use ($request) {
                $query->where(function($q) use ($request) {
                    $q->where('date_debut', '<', $request->date_fin)
                      ->where('date_fin', '>', $request->date_debut);
                });
            })
            ->where('statut', '!=', 'annulée')
            ->exists();

        if ($conflitDates) {
            return redirect()->back()
                ->with('error', 'La chambre n\'est pas disponible pour les dates sélectionnées.')
                ->withInput();
        }

        // Mettre à jour le client
        $reservation->client->update([
            'nom' => $request->client_nom,
            'telephone' => $request->client_telephone,
            'email' => $request->client_email,
            'identite' => $request->client_identite,
        ]);


        // Ancienne chambre pour mise à jour du statut
        $ancienneChambreId = $reservation->chambre_id;

        // Mettre à jour la réservation
        $reservation->update([
            'chambre_id' => $request->chambre_id,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'observations' => $request->observations,
        ]);
        $facture= Facture::where('chambre_id', $ancienneChambreId)
            ->where('client_id', $reservation->client_id)
            ->first();
        
        if($facture){
            $facture->chambre_id=$request->chambre_id;
            $facture->total_ht=$request->montant_total;
            $facture->total_ttc=$request->montant_total;
            $facture->devise=$request->devise;
             $facture->save();
        }

        // Mettre à jour le statut des chambres
        if ($ancienneChambreId != $request->chambre_id) {
            // Libérer l'ancienne chambre
            Chambre::where('id', $ancienneChambreId)->update(['statut' => 'libre']);
            
            // Occuper la nouvelle chambre si la réservation est active
            if (in_array($request->statut, ['confirmée', 'en cours'])) {
               Chambre::where('id', $request->chambre_id)->update(['statut' => 'occupée']);
            }
        } else {
            // Même chambre, mettre à jour le statut selon le statut de réservation
            $statutChambre = in_array($request->statut, ['confirmée', 'en cours']) ? 'occupée' : 'libre';
            Chambre::where('id', $request->chambre_id)->update(['statut' => $statutChambre]);
        }

        DB::commit();
        $chambre=Chambre::where('id', $request->chambre_id)->first();
        $pdf = Pdf::loadView('pdf.facturereservation', [
            'reservation' => $reservation,
            'chambre' => $chambre,
            'facture'=>$facture
        ]);

        return $pdf->download('facture_reservation_'.$reservation->id.'.pdf');
        return redirect()->route('Reservations')
            ->with('success', 'Réservation modifiée avec succès!');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()
            ->with('error', 'Erreur lors de la modification: ' . $e->getMessage())
            ->withInput();
    }
}
private function mettreAJourStatutsReservations($emplacementId)
{
    try {
        // Récupérer les réservations dont la date_fin est passée et qui ne sont pas déjà terminées/annulées
        $reservationsATerminer = Reservation::whereHas('chambre', function($query) use ($emplacementId) {
            $query->where('emplacement_id', $emplacementId);
        })
        ->where('date_fin', '<', now())
        ->whereNotIn('statut', ['terminée', 'annulée'])
        ->get();

        foreach ($reservationsATerminer as $reservation) {
            // Mettre à jour le statut de la réservation
            $reservation->update([
                'statut' => 'terminée'
            ]);

            // Libérer la chambre associée
            $reservation->chambre()->update([
                'statut' => 'libre'
            ]);

            // Optionnel: logger l'action
            \Log::info("Réservation #{$reservation->id} automatiquement terminée - Date fin: {$reservation->date_fin}");
        }

        if ($reservationsATerminer->count() > 0) {
            \Log::info("{$reservationsATerminer->count()} réservation(s) mise(s) à jour automatiquement en statut 'terminée'");
        }

    } catch (\Exception $e) {
        \Log::error("Erreur lors de la mise à jour automatique des statuts: " . $e->getMessage());
    }
}
    public function viewReservations(Request $request)
{
    $emplacement = auth()->user()->emplacement;
    $this->mettreAJourStatutsReservations($emplacement->id);
    
    $chambres = $emplacement->chambres()->with('reservations.client')->get();

    // Récupérer les réservations avec filtres
    $reservations = Reservation::whereHas('chambre', function($query) use ($emplacement) {
        $query->where('emplacement_id', $emplacement->id);
    })
    ->with(['chambre', 'client', 'table'])
    ->when($request->filled('search'), function($query) use ($request) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            // Recherche par numéro de réservation
            $q->where('id', 'LIKE', "%{$search}%")
              // Recherche par nom du client
              ->orWhereHas('client', function($q2) use ($search) {
                  $q2->where('nom', 'LIKE', "%{$search}%")
                     ->orWhere('telephone', 'LIKE', "%{$search}%");
              })
              // Recherche par numéro de chambre
              ->orWhereHas('chambre', function($q2) use ($search) {
                  $q2->where('numero', 'LIKE', "%{$search}%");
              
             
              });
        });
    })
    ->when($request->filled('date_debut'), function($query) use ($request) {
        $query->whereDate('date_debut', '>=', $request->date_debut);
    })
    ->when($request->filled('date_fin'), function($query) use ($request) {
        $query->whereDate('date_fin', '<=', $request->date_fin);
    })
    ->when($request->filled('statut') && $request->statut !== 'tous', function($query) use ($request) {
        $query->where('statut', $request->statut);
    })
    ->orderBy('date_debut', 'desc')
    ->get();

    return view('reservation.reservations', compact('emplacement', 'chambres', 'reservations'));
}
public function occupeChambre($reservation_id)
{
    $emplacement = auth()->user()->emplacement;

    $reservation = Reservation::with('client', 'chambre')->find($reservation_id);
    if (!$reservation) {
        return redirect()->route('Reservations')->with('error', 'Réservation non trouvée.');
    }

    // Vérifier que la réservation appartient à l'emplacement de l'utilisateur
    if ($reservation->chambre->emplacement_id !== $emplacement->id) {
        return redirect()->route('Reservations')->with('error', 'Vous n\'êtes pas autorisé à accéder à cette réservation.');
    }
    if (Carbon::parse($reservation->date_debut)->isFuture()) {
    return redirect()->back()->with('error', 'Veuillez attendre la date de la réservation pour occuper la chambre.');
}
    $reservation->chambre->statut='occupée';
    $reservation->chambre->save();
    return redirect()->back()->with("success","Chambre occupée avec succès");
}
public function annuleReseervation($reservation_id){
    $emplacement = auth()->user()->emplacement;

    $reservation = Reservation::with('client', 'chambre')->find($reservation_id);
    if (!$reservation) {
        return redirect()->route('Reservations')->with('error', 'Réservation non trouvée.');
    }

    // Vérifier que la réservation appartient à l'emplacement de l'utilisateur
    if ($reservation->chambre->emplacement_id !== $emplacement->id) {
        return redirect()->route('Reservations')->with('error', 'Vous n\'êtes pas autorisé à accéder à cette réservation.');
    }
    $reservation->statut="annulée";
    $reservation->save();
     return redirect()->route('Reservations')->with('success', 'Réservation annulée avec succès');
}
public function reactiveReseervation($reservation_id){
    $emplacement = auth()->user()->emplacement;

    $reservation = Reservation::with('client', 'chambre')->find($reservation_id);
    if (!$reservation) {
        return redirect()->route('Reservations')->with('error', 'Réservation non trouvée.');
    }

    // Vérifier que la réservation appartient à l'emplacement de l'utilisateur
    if ($reservation->chambre->emplacement_id !== $emplacement->id) {
        return redirect()->route('Reservations')->with('error', 'Vous n\'êtes pas autorisé à accéder à cette réservation.');
    }
    $reservation->statut="en_attente";
    $reservation->save();
     return redirect()->route('Reservations')->with('success', 'Réservation réactivée avec succès');
}
public function createReservationView()
{
    $emplacement = auth()->user()->emplacement;

    //on reuperer les chambre libres

    $chambres = $emplacement->chambres()->get();
    return view('reservation.create_reservation', compact('emplacement', 'chambres'));

}
public function voirReseervation($reservation_id)
{
    $emplacement = auth()->user()->emplacement;

    $reservation = Reservation::with(['client', 'chambre', 'chambre.emplacement'])->find($reservation_id);
    if (!$reservation) {
        return redirect()->route('Reservations')->with('error', 'Réservation non trouvée.');
    }

    // Vérifier que la réservation appartient à l'emplacement de l'utilisateur
    if ($reservation->chambre->emplacement_id !== $emplacement->id) {
        return redirect()->route('Reservations')->with('error', 'Vous n\'êtes pas autorisé à accéder à cette réservation.');
    }

    // Récupérer la facture associée à cette réservation
    $facture = Facture::with(['details', 'payments', 'client'])
        ->where('chambre_id', $reservation->chambre_id)
        ->where('client_id', $reservation->client_id)
        ->whereDate('date_facture', '>=', \Carbon\Carbon::parse($reservation->date_debut)->format('Y-m-d'))
        ->first();

    // Calculer la durée du séjour
    $dateDebut = \Carbon\Carbon::parse($reservation->date_debut);
    $dateFin = \Carbon\Carbon::parse($reservation->date_fin);
    
    $dureeSecondes = $dateDebut->diffInSeconds($dateFin);
    $dureeMinutesTotal = floor($dureeSecondes / 60);
    $dureeHeuresTotal = floor($dureeMinutesTotal / 60);
    $dureeJours = floor($dureeHeuresTotal / 24);
    $heuresRestantes = $dureeHeuresTotal % 24;
    $minutesRestantes = $dureeMinutesTotal % 60;

    // Formater la durée
    if ($dureeJours > 0) {
        if ($heuresRestantes > 0) {
            $dureeAffichage = $dureeJours . ' jour(s) et ' . $heuresRestantes . ' heure(s)';
        } else {
            $dureeAffichage = $dureeJours . ' jour(s)';
        }
    } else {
        if ($dureeHeuresTotal > 0) {
            if ($minutesRestantes > 0) {
                $dureeAffichage = $dureeHeuresTotal . 'h' . str_pad($minutesRestantes, 2, '0', STR_PAD_LEFT);
            } else {
                $dureeAffichage = $dureeHeuresTotal . ' heure(s)';
            }
        } else {
            $dureeAffichage = $dureeMinutesTotal . ' minute(s)';
        }
    }

    // Calcul du montant théorique
    $montantTheorique = 0;
    $prixHoraire = $reservation->chambre->prix;
    $prixParMinute = $prixHoraire / 60;
    
    if ($dureeJours > 0) {
        $prixJournalier = $prixHoraire * 24;
        $montantJoursComplets = $dureeJours * $prixJournalier;
        $montantHeuresRestantes = $heuresRestantes * $prixHoraire;
        $montantMinutesRestantes = $minutesRestantes * $prixParMinute;
        $montantTheorique = $montantJoursComplets + $montantHeuresRestantes + $montantMinutesRestantes;
    } else {
        $montantHeures = $dureeHeuresTotal * $prixHoraire;
        $montantMinutes = $minutesRestantes * $prixParMinute;
        $montantTheorique = $montantHeures + $montantMinutes;
    }

    return view('reservation.details', compact(
        'reservation',
        'facture',
        'dureeAffichage',
        'montantTheorique',
        'emplacement'
    ));
}
public function storeReservation(Request $request)
{
    $validator = Validator::make($request->all(), [
            'chambre_id'      => 'required|exists:chambres,id',
            'nom'             => 'required|string|max:255',
            'telephone'       => 'required|string|max:20',
            'email'           => 'nullable|email|max:255',
            'identite_type'   => 'nullable|string|max:50',
            'identite'        => 'nullable|string|max:100',
            'date_debut'      => 'required|date',
            'date_fin'        => 'required|date|after:date_debut',
            'total_reservation' => 'required|numeric|min:0',
        ], [
            'chambre_id.required'   => 'Veuillez sélectionner une chambre.',
            'chambre_id.exists'     => 'La chambre sélectionnée n\'existe pas.',
            'nom.required'          => 'Le nom du client est obligatoire.',
            'telephone.required'    => 'Le numéro de téléphone est obligatoire.',
            'email.email'           => 'L\'email doit être une adresse valide.',
            'date_debut.required'   => 'La date d\'arrivée est obligatoire.',
            'date_fin.required'     => 'La date de départ est obligatoire.',
            'date_fin.after'        => 'La date de départ doit être après la date d\'arrivée.',
            'total_reservation.required' => 'Le calcul du prix est requis.',
        ]);
       

        if ($validator->fails()) {
            return redirect()->back()
                             ->withErrors($validator)
                             ->withInput();
        }

        try {
            DB::beginTransaction();
             $dateDebut = $this->convertDatetimeLocalToMySQL($request->date_debut);
             $dateFin = $this->convertDatetimeLocalToMySQL($request->date_fin);
             
            // 1️⃣ Vérifier que la chambre est toujours disponible
            $chambre = Chambre::find($request->chambre_id);
            if (!$chambre) {
                return redirect()->back()
                               ->with('error', 'La chambre sélectionnée n\'existe pas.')
                               ->withInput();
            }

            if ($chambre->statut != 'libre') {
    // Vérifier les réservations existantes pour cette chambre
    $reservationsExistantes = Reservation::where('chambre_id', $chambre->id)
        ->where(function($query) use ($dateDebut, $dateFin) {
            // Vérifier le chevauchement des créneaux horaires
            $query->where(function($q) use ($dateDebut, $dateFin) {
                // Chevauchement : la réservation existante commence avant et se termine après le début de la nouvelle
                $q->where('date_debut', '<=', $dateDebut)
                  ->where('date_fin', '>=', $dateDebut);
            })->orWhere(function($q) use ($dateDebut, $dateFin) {
                // Chevauchement : la réservation existante commence avant et se termine après la fin de la nouvelle
                $q->where('date_debut', '<=', $dateFin)
                  ->where('date_fin', '>=', $dateFin);
            })->orWhere(function($q) use ($dateDebut, $dateFin) {
                // Chevauchement : la réservation existante est complètement incluse dans la nouvelle
                $q->where('date_debut', '>=', $dateDebut)
                  ->where('date_fin', '<=', $dateFin);
            });
        })
        ->where('statut', '!=', 'annulee') // Exclure les réservations annulées
        ->exists();

    if ($reservationsExistantes) {
        return redirect()->back()
                       ->with('error', 'Cette chambre est déjà réservée pour le créneau horaire sélectionné.')
                       ->withInput();
    }
}

            // 2️⃣ Récupérer ou créer la journée de vente
            $saleDay = SaleDay::where('ets_id', auth()->user()->ets_id)
                              ->whereNull('end_time')
                              ->first();

            // Si pas de journée de vente active, en créer une
            if (!$saleDay) {
                $saleDay = SaleDay::create([
                    'sale_date' => now()->format('Y-m-d'),
                    'start_time' => now(),
                    'ets_id' => auth()->user()->ets_id,
                ]);
            }

            // 3️⃣ Créer ou récupérer le client
            $client = Client::create([
                ['telephone' => $request->telephone],
                
                    'nom' => $request->nom,
                    'email' => $request->email,
                    'identite_type' => $request->identite_type,
                    'identite' => $request->identite,
                ]
            );
            

            // 4️⃣ Créer la réservation
            $reservation = Reservation::create([
                'chambre_id' => $request->chambre_id,
                'client_id'  => $client->id,
                'date_debut' => $dateDebut,
                'date_fin'   => $dateFin,
                'statut'     => 'en_attente',
                'ets_id'     => auth()->user()->ets_id,
            ]);

            // 5️⃣ Créer la facture
            $facture = Facture::create([
                'numero_facture'   => 'FCT-' . time() . '-' . rand(1000, 9999),
                'user_id'          => auth()->id(),
                'chambre_id'       => $chambre->id,
                'sale_day_id'      => $saleDay->id,
                'total_ht'         => $request->total_reservation,
                'remise'           => 0,
                'total_ttc'        => $request->total_reservation,
                'devise'           => $chambre->prix_devise ?? 'CDF',
                'date_facture'     => now(),
                'statut'           => 'en_attente',
                'statut_service'   => 'en_attente',
                'ets_id'           => auth()->user()->ets_id,
                'emplacement_id'   => $chambre->emplacement_id,
                'client_id'        => $client->id,
            ]);

            // 6️⃣ Mettre à jour le statut de la chambre
            $chambre->update(['statut' => 'réservée']);

            DB::commit();
            $pdf = Pdf::loadView('pdf.facturereservation', [
            'reservation' => $reservation,
            'chambre' => $chambre,
            'facture'=>$facture
        ]);

        return $pdf->download('facture_reservation_'.$reservation->id.'.pdf');
            return redirect()->route('Reservations')
                             ->with('success', 'Réservation et facture créées avec succès !');

        } catch (Exception $e) {
            DB::rollBack();
            
            // Log l'erreur pour le débogage
            Log::error('Erreur création réservation: ' . $e->getMessage());
            \Log::error('Trace: ' . $e->getTraceAsString());
            
            return redirect()->back()
                             ->with('error', 'Une erreur est survenue lors de la création de la réservation: ' . $e->getMessage())
                             ->withInput();
        }

}
private function convertDatetimeLocalToMySQL($datetimeLocal)
{
    // Convertit du format "YYYY-MM-DDTHH:MM" vers "YYYY-MM-DD HH:MM:SS"
    return \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $datetimeLocal)->format('Y-m-d H:i:s');
}
public function createReservation($chambre_id)
{
    $emplacement = auth()->user()->emplacement;

    //on reuperer les chambre libres

    $chambres = $emplacement->chambres()->where('id', $chambre_id)->where('statut', 'libre')->orWhere('statut', 'réservée')->first();
    if(!$chambres){
        return redirect()->route('reservation.created')->with('error', 'La chambre sélectionnée n\'est pas disponible pour la réservation.');
    }
    return view('reservation.create_reservation_client', compact('emplacement', 'chambres', 'chambre_id'));
}

}