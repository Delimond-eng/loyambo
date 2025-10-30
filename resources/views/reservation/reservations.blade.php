@extends('layouts.admin')
@section('content')
   <div class="content-wrapper">
    <div class="container-full AppReport">
        <!-- Content Header (Page header) -->
        <div class="content-header">
        </div>
        <!-- Main content -->
        <section class="content">
            <div class="row d-flex justify-content-center align-items-center">
                <div class="col-xl-12">
                    @include('components.menus.reservations')
                </div>
                <div class="col-xl-12">
                    <div class="box">
                        <div class="d-flex justify-content-between align-items-center p-3">
                            <h4 class="mb-0">
                                Liste des toutes les réservations - {{ $emplacement->libelle }}
                                <small class="text-muted d-block">Gestion des réservations de chambres</small>
                            </h4>
                            <a href="{{ route('reservation.created') }}" class="btn btn-primary btn-rounded btn-sm">
                                Nouvelle Réservation
                            </a>
                        </div>

                        <!-- Filtres de recherche -->
                        <div class="box-body border-bottom">
                            <form method="GET" action="{{ route('Reservations') }}" class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Recherche</label>
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Client, Chambre, Facture..." 
                                           value="{{ request('search') }}">
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label">Date début</label>
                                    <input type="date" name="date_debut" class="form-control" 
                                           value="{{ request('date_debut') }}">
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label">Date fin</label>
                                    <input type="date" name="date_fin" class="form-control" 
                                           value="{{ request('date_fin') }}">
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label">Statut</label>
                                    <select name="statut" class="form-control">
                                        <option value="tous" {{ request('statut') == 'tous' ? 'selected' : '' }}>Tous les statuts</option>
                                        <option value="en_attente" {{ request('statut') == 'en_attente' ? 'selected' : '' }}>En attente</option>
                                        <option value="confirmée" {{ request('statut') == 'confirmée' ? 'selected' : '' }}>Confirmée</option>
                                        <option value="terminée" {{ request('statut') == 'terminée' ? 'selected' : '' }}>Terminée</option>
                                        <option value="annulée" {{ request('statut') == 'annulée' ? 'selected' : '' }}>Annulée</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-search"></i> Filtrer
                                    </button>
                                    <a href="{{ route('Reservations') }}" class="btn btn-secondary">
                                        <i class="fa fa-refresh"></i> Réinitialiser
                                    </a>
                                </div>
                            </form>
                            
                            @if(request()->anyFilled(['search', 'date_debut', 'date_fin', 'statut']))
                            <div class="mt-3">
                                <small class="text-muted">
                                    Filtres actifs : 
                                    @if(request('search'))
                                        <span class="badge bg-info">Recherche: "{{ request('search') }}"</span>
                                    @endif
                                    @if(request('date_debut'))
                                        <span class="badge bg-info">Début: {{ request('date_debut') }}</span>
                                    @endif
                                    @if(request('date_fin'))
                                        <span class="badge bg-info">Fin: {{ request('date_fin') }}</span>
                                    @endif
                                    @if(request('statut') && request('statut') !== 'tous')
                                        <span class="badge bg-info">Statut: {{ request('statut') }}</span>
                                    @endif
                                    <span class="badge bg-primary">{{ $reservations->count() }} résultat(s)</span>
                                </small>
                            </div>
                            @endif
                        </div>

                        <!-- Statistiques -->
                        <div class="box-body">
                           
                            <!-- Liste des réservations -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="bg-light">
                                        <tr>
                                            <th># Réservation</th>
                                            <th>Client</th>
                                            <th>Chambre</th>
                                            <th>Période</th>
                                            <th>Durée</th>
                                            <th>Montant</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($reservations as $reservation)
                                            @php
                                                $client = $reservation->client;
                                                $chambre = $reservation->chambre;
                                                
                                                // Calculer la durée du séjour en jours et heures
                                                $dateDebut = \Carbon\Carbon::parse($reservation->date_debut);
                                                $dateFin = \Carbon\Carbon::parse($reservation->date_fin);
                                                
                                                // Calcul précis en jours, heures, minutes
                                                $dureeJours = $dateDebut->diffInDays($dateFin);
                                                $dureeHeures = $dateDebut->diffInHours($dateFin);
                                                $dureeMinutes = $dateDebut->diffInMinutes($dateFin);
                                                
                                                // Calcul des heures restantes après les jours complets
                                                $heuresRestantes = $dureeHeures - ($dureeJours * 24);
                                                $minutesRestantes = $dureeMinutes - ($dureeHeures * 60);
                                                
                                                // Formater la durée de manière précise
                                                if ($dureeJours > 0) {
                                                    if ($heuresRestantes > 0) {
                                                        $dureeAffichage = $dureeJours . ' jour(s) et ' . $heuresRestantes . ' heure(s)';
                                                    } else {
                                                        $dureeAffichage = $dureeJours . ' jour(s)';
                                                    }
                                                } else {
                                                    if ($dureeHeures > 0) {
                                                        if ($minutesRestantes > 0) {
                                                            $dureeAffichage = $dureeHeures . 'h' . str_pad($minutesRestantes, 2, '0', STR_PAD_LEFT);
                                                        } else {
                                                            $dureeAffichage = $dureeHeures . ' heure(s)';
                                                        }
                                                    } else {
                                                        $dureeAffichage = $dureeMinutes . ' minute(s)';
                                                    }
                                                }
                                                
                                                // Calcul du montant
                                                $montantTotal = 0;
                                                if ($chambre) {
                                                    // Si durée < 24h, calcul au prorata
                                                    if ($dureeHeures < 24) {
                                                        // Calcul au prorata du prix journalier
                                                        $prixJournalier = $chambre->prix * 24; // Prix par jour
                                                        $montantTotal = ($prixJournalier / 24) * $dureeHeures;
                                                    } else {
                                                        // Calcul par jours complets + prorata pour les heures restantes
                                                        $prixJournalier = $chambre->prix * 24;
                                                        $montantJoursComplets = $dureeJours * $prixJournalier;
                                                        $montantHeuresRestantes = ($prixJournalier / 24) * $heuresRestantes;
                                                        $montantTotal = $montantJoursComplets + $montantHeuresRestantes;
                                                    }
                                                }
                                                
                                                $statutColor = match($reservation->statut) {
                                                    'confirmée' => 'success',
                                                    'en cours' => 'info',
                                                    'terminée' => 'primary',
                                                    'annulée' => 'danger',
                                                    default => 'secondary'
                                                };
                                            @endphp
                                            <tr>
                                                <td>
                                                    <strong>#RES{{ str_pad($reservation->id, 6, '0', STR_PAD_LEFT) }}</strong>
                                                </td>
                                                <td>
                                                    @if($client)
                                                        <div>
                                                            <strong>{{ $client->nom }}</strong>
                                                            @if($client->telephone)
                                                                <br><small class="text-muted">{{ $client->telephone }}</small>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <span class="text-muted">Client non spécifié</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($chambre)
                                                        <div>
                                                            <span class="badge bg-secondary">Chambre {{ $chambre->numero }}</span>
                                                            @if($chambre->prix)
                                                                <br><small class="text-success">{{ number_format($chambre->prix * 24, 0, ',', ' ') }} {{ $chambre->prix_devise ?? 'CDF' }}/jour</small>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="text-nowrap">
                                                        <strong>Arrivée:</strong> {{ $dateDebut->format('d/m/Y H:i') }}
                                                        <br>
                                                        <strong>Départ:</strong> {{ $dateFin->format('d/m/Y H:i') }}
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-info" title="{{ $dureeMinutes }} minutes au total">
                                                        {{ $dureeAffichage }}
                                                    </span>
                                                    <br>
                                                    <small class="text-muted">
                                                        ({{ $dureeJours }}j {{ $heuresRestantes }}h {{ $minutesRestantes }}m)
                                                    </small>
                                                    @if($dateDebut->isPast() && $dateFin->isFuture())
                                                        <br><small class="text-success">En séjour</small>
                                                    @elseif($dateFin->isPast())
                                                        <br><small class="text-muted">Séjour terminé</small>
                                                    @else
                                                        <br><small class="text-warning">À venir</small>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <strong class="text-success">
                                                        {{ number_format($montantTotal, 0, ',', ' ') }} {{ $chambre->prix_devise ?? 'CDF' }}
                                                    </strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        @if($dureeJours > 0)
                                                            {{ number_format($chambre->prix * 24, 0, ',', ' ') }}/jour
                                                        @else
                                                            {{ number_format($chambre->prix, 0, ',', ' ') }}/heure
                                                        @endif
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $statutColor }}">{{ $reservation->statut }}</span>
                                                    @if($reservation->statut == 'en cours' && $dateFin->isToday())
                                                        <br><small class="text-warning">Départ aujourd'hui</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="{{ route('reservations.see', $reservation->id) }}" class="btn btn-outline-info" 
                                                               title="Voir">
                                                               <i class="fa fa-eye"></i>
                                                           </a>
                                                       @if ($reservation->statut == 'en_attente')
                                                           <a href="{{ route('reservations.edit', $reservation->id) }}" class="btn btn-outline-info" 
                                                               title="Modifier">
                                                               <i class="fa fa-edit"></i>
                                                           </a>
                                                       @endif
                                                       
                                                       @if ($reservation->statut == 'en_attente')
                                                          <button class="btn btn-outline-success payment-btn" 
                                                                  title="Payer"
                                                                  data-reservation-id="{{ $reservation->id }}"
                                                                  data-bs-toggle="modal"
                                                                  data-bs-target="#paymentModal">
                                                              <i class="fa fa-dollar"></i>
                                                          </button>
                                                       @endif
                                            
                                                        @if ($reservation->statut == "confirmée")
                                                            <a href="{{ route('reservation.occupe.chambre',$reservation->id) }}" class="btn btn-outline-success" title="Occuper la chambre">
                                                                <i class="fa fa-bed"></i>
                                                            </a>
                                                            
                                                        @endif
                                                        
                                                        @if($reservation->statut == 'en_attente')
                                                            <a href="{{route('reservation.delete',$reservation->id)}}" class="btn btn-outline-danger cancel-btn" 
                                                                    title="Annuler"
                                                                    data-reservation-id="{{ $reservation->id }}">
                                                                <i class="fa fa-times"></i>
                                                            </a>

                                                        @endif
                                                         @if($reservation->statut == 'annulée')
                                                            <a href="{{route('reservation.autorise',$reservation->id)}}" class="btn btn-outline-success" 
                                                                    title="Réactiver"
                                                                   >
                                                                <i class="fa fa-check"></i>
                                                            </a>

                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="fa fa-bed fa-2x mb-2"></i>
                                                        <p>
                                                            @if(request()->anyFilled(['search', 'date_debut', 'date_fin', 'statut']))
                                                                Aucune réservation trouvée avec les filtres actuels
                                                            @else
                                                                Aucune réservation trouvée
                                                            @endif
                                                        </p>
                                                        @if(request()->anyFilled(['search', 'date_debut', 'date_fin', 'statut']))
                                                            <a href="{{ route('Reservations') }}" class="btn btn-secondary btn-sm">Réinitialiser les filtres</a>
                                                        @endif
                                                        <a href="{{ route('reservation.created') }}" class="btn btn-primary btn-sm">Créer une réservation</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- Modal de paiement -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="paymentModalLabel">Choisir le mode de paiement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <div class="row g-2 text-center">
            @php
                $paymentModes = [
                                    'cash' => ['icon' => 'fa fa-money', 'label' => 'Espèces'],
                                    'mobile' => ['icon' => 'fa fa-mobile', 'label' => 'Mobile'],
                                    'cheque' => ['icon' => 'fa fa-file-text', 'label' => 'Chèque'],
                                    'virement' => ['icon' => 'fa fa-university', 'label' => 'Virement'],
                                    'card' => ['icon' => 'fa fa-credit-card', 'label' => 'Carte']
                                ];

            @endphp

            @foreach($paymentModes as $mode => $data)
                <div class="col-4 mb-3">
                    <button class="btn btn-outline-primary payment-mode-btn w-100 h-100 py-3" 
                            data-mode="{{ $mode }}">
                        <i class="{{ $data['icon'] }} fa-2x mb-2"></i><br>
                        <span class="small">{{ $data['label'] }}</span>
                    </button>
                </div>
            @endforeach
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
let currentReservationId = null;

// Gestion du paiement
document.querySelectorAll('.payment-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        currentReservationId = this.dataset.reservationId;
    });
});

document.querySelectorAll('.payment-mode-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const mode = this.dataset.mode;
        if(currentReservationId) {
            // Fermer le modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
            modal.hide();
            
            // Rediriger vers la page de paiement
            window.location.href = `/reservations.paie/${currentReservationId}?mode=${mode}`;

        }
    });
});

// Gestion du check-in
document.querySelectorAll('.checkin-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const reservationId = this.dataset.reservationId;
        if(confirm('Confirmer le check-in pour cette réservation ?')) {
            window.location.href = `/reservations/${reservationId}/checkin`;
        }
    });
});

// Gestion du check-out
document.querySelectorAll('.checkout-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const reservationId = this.dataset.reservationId;
        if(confirm('Confirmer le check-out pour cette réservation ?')) {
            window.location.href = `/reservations/${reservationId}/checkout`;
        }
    });
});

// Gestion de l'annulation
document.querySelectorAll('.cancel-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const reservationId = this.dataset.reservationId;
        if(confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')) {
            window.location.href = `/reservations/${reservationId}/annuler`;
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les tooltips Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<style>
.card {
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-2px);
}
.btn-group .btn {
    padding: 0.25rem 0.5rem;
}
.payment-mode-btn {
    min-height: 80px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
.badge[title]:hover::after {
    content: attr(title);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: #333;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    white-space: nowrap;
    z-index: 1000;
}
/* Styles pour les filtres */
.form-label {
    font-weight: 500;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
}
</style>
@endpush