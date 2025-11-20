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
                                Liste des toutes les réservations 
                                <small class="text-muted d-block">Gestion des réservations de chambres</small>
                            </h4>
                            <a href="{{ route('reservation.created') }}" class="btn btn-primary">
                                + Nouvelle Réservation
                            </a>
                        </div>

                        <!-- Filtres de recherche -->
                        <div class="box-body border-bottom">
                            <div class="row g-2">
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
                                    <label class="form-label">Actions</label>
                                    <div class="d-flex">
                                        <button type="submit" class="btn btn-primary btn-sm me-2">
                                            <i class="fa fa-search"></i> Filtrer
                                        </button>
                                        <a href="#" class="btn btn-secondary btn-sm">
                                            <i class="fa fa-refresh"></i> Réinitialiser
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Statistiques -->
                        <div class="box-body">
                            <!-- Liste des réservations -->
                            <div class="table-responsive">
                                <table class="table table-striped no-border">
                                <thead>
                                    <tr class="bb-3 border-primary">
                                        <th>Date Reservation</th>
                                        <th>Client</th>
                                        <th>N° Chambre</th>
                                        <th>Période</th>
                                        <th>Durée</th>
                                        <th>Tarif jour</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                   <!-- <tr class="be-3 border-warning">
                                        <th scope="row">10/02/2025</th>
                                        <td>Hyppo Kayembe</td>
                                        <td>CH-002</td>
                                        <td>10/02/2025 - 11/02/2025</td>
                                        <td>01 j</td>
                                        <td>40$</td>
                                        <td><span class="badge badge-pill badge-warning-light">En attente</span></td>
                                        <td>
                                            <div class="d-flex">
                                                <button type="button" class="btn btn-success btn-xs me-1"><i class="mdi mdi-pencil"></i></button>
                                                <button type="button" class="btn btn-primary btn-xs me-1"><i class="mdi mdi-eye"></i></button>
                                                <button type="button" class="btn btn-danger-light btn-xs"><i class="mdi mdi-cancel"></i></button>
                                            </div>
                                        </td>
                                    </tr>-->
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fa fa-bed fa-2x mb-2"></i>
                                                <p>
                                                    Aucune réservation trouvée
                                                </p>
                                                <a href="{{ route('reservation.created') }}" class="btn btn-primary btn-xs">+ Créer une réservation</a>
                                            </div>
                                        </td>
                                    </tr>
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