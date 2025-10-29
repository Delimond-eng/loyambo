<!-- View: reservation/chambrelibre.blade.php -->
@extends('layouts.admin')

@section('content')
<div class="content-wrapper">
    <div class="container-full AppReport">
        
        <section class="content">
            <div class="row">
                <div class="col-xl-12">
                    @include('components.menus.reservations')
                </div>
                
                <div class="col-xl-12">
                    <div class="box">
                        <div class="box-head d-flex justify-content-between align-items-center p-3">
                            <h4 class="mb-0">
                                Chambres réservées
                                <small class="text-muted d-block">Liste des chambres réservées</small>
                            </h4>
                            <a href="{{ route('reservation.created') }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Nouvelle Réservation
                            </a>
                        </div>

                        <div class="box-body">
                            <div class="row justify-content-center">
                                @forelse($chambresreservees as $chambre)
                                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                                    <div class="card chambre-card" onclick="openReservationsModal({{ $chambre->id }})" style="cursor: pointer; position: relative; overflow: hidden;">
                                        <!-- Bandeau de statut corrigé -->
                                        <div class="status-ribbon status-ribbon-occupied">
                                            <span>Réservée</span>
                                        </div>
                                        <div class="card-body text-center">
                                            <h5>Chambre {{ $chambre->numero }}</h5>
                                            <img src="{{ asset('assets/images/bed-empt.jpeg') }}" alt="Chambre {{ $chambre->numero }}" class="img-fluid mb-2" style="height: 120px; object-fit: cover;">
                                            <p class="text-muted">{{ $chambre->type }}</p>
                                            <div class="mb-2">
                                                <span class="badge badge-info">
                                                    <i class="fa fa-users"></i> {{ $chambre->capacite }} pers.
                                                </span>
                                                <span class="badge badge-warning ml-1">
                                                    {{ number_format($chambre->prix * 24, 0, ',', ' ') }} {{ $chambre->prix_devise }}/Jour
                                                </span>
                                            </div>
                                            <a href="{{ route('reservation.create', ['chambre_id' => $chambre->id]) }}" class="btn btn-success btn-sm btn-block" onclick="event.stopPropagation()">
                                                <i class="fa fa-calendar-plus"></i> Réserver
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="col-12 text-center py-5">
                                    <i class="fa fa-bed fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Aucune chambre réservée</h5>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- Modals -->
@foreach($chambresreservees as $chambre)
<div class="modal fade" id="reservationsModal{{ $chambre->id }}" tabindex="-1" role="dialog" aria-labelledby="reservationsModalLabel{{ $chambre->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="reservationsModalLabel{{ $chambre->id }}">
                    <i class="fa fa-list"></i> Réservations - Chambre {{ $chambre->numero }}
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @if($chambre->reservations && $chambre->reservations->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>Référence</th>
                                <th>Client</th>
                                <th>Date Début</th>
                                <th>Date Fin</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($chambre->reservations as $reservation)
                            <tr>
                                <td>#RES{{ str_pad($reservation->id, 6, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $reservation->client->nom ?? 'N/A' }}</td>
                                <td>{{ \Carbon\Carbon::parse($reservation->date_debut)->format('d/m/Y H:i') }}</td>
                                <td>{{ \Carbon\Carbon::parse($reservation->date_fin)->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge badge-{{ match($reservation->statut) {
                                        'confirmée' => 'success',
                                        'en cours' => 'info',
                                        'terminée' => 'primary',
                                        'annulée' => 'danger',
                                        default => 'secondary'
                                    } }}">{{ $reservation->statut }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('reservations.see', $reservation->id) }}" class="btn btn-info btn-sm" target="_blank">
                                        <i class="fa fa-eye"></i> Détails
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fa fa-calendar-times fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucune réservation</h5>
                    <p class="text-muted">Cette chambre n'a aucune réservation enregistrée.</p>
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                <a href="{{ route('reservation.create', ['chambre_id' => $chambre->id]) }}" class="btn btn-success">
                    <i class="fa fa-plus"></i> Nouvelle Réservation
                </a>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection

@push('styles')
<style>
.chambre-card {
    position: relative;
    border: 1px solid #e1e5eb;
    border-radius: 8px;
    transition: all 0.3s ease;
    overflow: hidden;
    height: 100%;
}

.chambre-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    border-color: #007bff;
}

/* Bandeau de statut corrigé - ne dépasse plus */
.status-ribbon {
    position: absolute;
    top: 15px;
    right: -30px;
    background: skyblue;
    color: white;
    padding: 5px 30px;
    transform: rotate(45deg);
    font-size: 12px;
    font-weight: bold;
    z-index: 1;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

/* Variante pour différents statuts si besoin */
.status-ribbon-free {
    background: #28a745;
}

.status-ribbon-occupied {
    background: #dc3545;
}

.status-ribbon-reserved {
    background: #ffc107;
    color: #000;
}

.card-body {
    padding: 20px 15px;
    position: relative;
    z-index: 2;
}

/* Style pour l'image */
.chambre-card img {
    border-radius: 4px;
    transition: transform 0.3s ease;
}

.chambre-card:hover img {
    transform: scale(1.05);
}

/* Animation pour le modal */
.modal.fade .modal-dialog {
    transition: transform 0.3s ease-out;
}

.modal.show .modal-dialog {
    transform: none;
}
</style>
@endpush

@push('scripts')
<script>
// Fonction pour ouvrir le modal des réservations
function openReservationsModal(chambreId) {
    $('#reservationsModal' + chambreId).modal('show');
}

// Animation pour l'ouverture des modals
$(document).ready(function() {
    $('.modal').on('show.bs.modal', function () {
        $(this).find('.modal-dialog').addClass('animate__animated animate__fadeInDown');
    });
    
    $('.modal').on('hide.bs.modal', function () {
        $(this).find('.modal-dialog').removeClass('animate__animated animate__fadeInDown');
    });

    // Empêcher la propagation du clic sur les boutons dans la carte
    $('.chambre-card .btn').on('click', function(e) {
        e.stopPropagation();
    });
});
</script>
@endpush