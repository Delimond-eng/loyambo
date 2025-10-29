@extends('layouts.admin')
@section('content')
<div class="content-wrapper">
    <div class="container-full">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="d-flex align-items-center">
                <div class="mr-auto">
                    <h4 class="page-title">Nouvelle Réservation</h4>
                    <div class="d-inline-block align-items-center">
                        <nav>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#"><i class="mdi mdi-home-outline"></i></a></li>
                                <li class="breadcrumb-item"><a href="{{ route('Reservations') }}">Réservations</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Nouvelle Réservation</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="row">
                <div class="col-12">
                    <div class="box">
                        <!-- En-tête -->
                        <div class="box-header with-border text-center">
                            <h4 class="box-title mb-2">Choix de la Chambre</h4>
                            <p class="text-muted mb-0">Veuillez sélectionner une chambre disponible pour la réservation</p>
                        </div>

                        <!-- Barre de recherche -->
                        <div class="box-body border-bottom">
                            <div class="row justify-content-center">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="searchChambre" 
                                                   placeholder="Rechercher une chambre par numéro, type ou caractéristiques...">
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="box-body">
                            <div class="text-center" id="chambres-container">
                                @forelse ($chambres as $chambre)
                                    <a href="{{ route('reservation.create',$chambre->id) }}" 
                                       class="btn btn-outline-primary mb-2 chambre-item"
                                       data-numero="{{ $chambre->numero }}"
                                       data-type="{{ $chambre->type ?? '' }}"
                                       data-caracteristiques="{{ $chambre->caracteristiques ?? '' }}"
                                       data-prix="{{ $chambre->prix ?? '' }}">
                                        <img src="{{ asset('assets/images/bed-empt.jpeg') }}" 
                                             class="img-fluid mb-2" 
                                             alt="Chambre {{ $chambre->numero }}"
                                             style="max-height: 80px; object-fit: cover;">
                                        <br>
                                        <span class="d-block font-weight-bold">Chambre {{ $chambre->numero }}</span>
                                        @if($chambre->type)
                                            <small class="d-block text-muted">{{ $chambre->type }}</small>
                                        @endif
                                        @if($chambre->prix)
                                            <small class="d-block text-success">
                                                {{ number_format($chambre->prix, 0, ',', ' ') }} {{ $chambre->prix_devise }}/heure
                                            </small>
                                        @endif
                                        @if($chambre->capacite)
                                            <small class="d-block text-muted">
                                                <i class="fa fa-user"></i> {{ $chambre->capacite }} pers.
                                            </small>
                                        @endif
                                    </a>
                                @empty
                                    <div class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fa fa-bed fa-3x mb-3"></i>
                                            <h5>Aucune chambre disponible</h5>
                                            <p class="mb-0">Toutes les chambres sont actuellement occupées</p>
                                        </div>
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchChambre');
    const chambreItems = document.querySelectorAll('.chambre-item');
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        
        chambreItems.forEach(item => {
            const numero = item.getAttribute('data-numero').toLowerCase();
            const type = item.getAttribute('data-type').toLowerCase();
            const caracteristiques = item.getAttribute('data-caracteristiques').toLowerCase();
            const prix = item.getAttribute('data-prix');
            
            const matchesSearch = 
                numero.includes(searchTerm) ||
                type.includes(searchTerm) ||
                caracteristiques.includes(searchTerm) ||
                prix.includes(searchTerm);
            
            if (matchesSearch || searchTerm === '') {
                item.style.display = 'inline-block';
            } else {
                item.style.display = 'none';
            }
        });
    });
});
</script>
@endpush

@push('styles')
<style>
.chambre-item {
    display: inline-block;
    width: 180px;
    margin: 10px;
    padding: 15px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.3s ease;
    vertical-align: top;
}

.chambre-item:hover {
    border-color: #007bff;
    background-color: #f8f9fa;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    text-decoration: none;
}

.chambre-item img {
    border-radius: 5px;
}

.stat-item {
    padding: 15px 0;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .chambre-item {
        width: 150px;
        margin: 8px;
        padding: 12px;
    }
}

@media (max-width: 576px) {
    .chambre-item {
        width: 140px;
        margin: 5px;
        padding: 10px;
    }
    
    .chambre-item img {
        max-height: 60px !important;
    }
}

/* Style pour la barre de recherche */
#searchChambre {
    border-radius: 25px;
    padding: 12px 20px;
}

.input-group-append .input-group-text {
    border-radius: 0 25px 25px 0;
    background-color: #fff;
}
</style>
@endpush