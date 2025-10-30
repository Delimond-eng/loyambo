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
                                    <a href="/reservation/create/{{$chambre->id}}" 
                                       class="chambre-card mb-4 chambre-item"
                                       data-numero="{{ $chambre->numero }}"
                                       data-type="{{ $chambre->type ?? '' }}"
                                       data-caracteristiques="{{ $chambre->caracteristiques ?? '' }}"
                                       data-prix="{{ $chambre->prix ?? '' }}">
                                        <div class="image-container">
                                            <img src="{{ asset('assets/images/bed-empt.jpeg') }}" 
                                                 class="img-fluid" 
                                                 alt="Chambre {{ $chambre->numero }}">
                                            <!-- Bandeau oblique statut -->
                                            <div class="status-ribbon status-{{ $chambre->statut }}">
                                                {{ ucfirst($chambre->statut) }}
                                            </div>
                                        </div>
                                        <div class="chambre-info mt-2">
                                            <span class="d-block font-weight-bold">Chambre {{ $chambre->numero }}</span>
                                            @if($chambre->type)
                                                <small class="d-block text-muted">{{ $chambre->type }}</small>
                                            @endif
                                            @if($chambre->prix)
                                                <small class="d-block text-success">
                                                    {{ number_format($chambre->prix, 0, ',', ' ') }} {{ $chambre->prix_devise }}
                                                </small>
                                            @endif
                                            @if($chambre->capacite)
                                                <small class="d-block text-muted">
                                                    <i class="fa fa-user"></i> {{ $chambre->capacite }} pers.
                                                </small>
                                            @endif
                                        </div>
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
                (prix && prix.includes(searchTerm));
            
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
.chambre-card {
    display: inline-block;
    width: 200px;
    margin: 15px;
    padding: 0;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.3s ease;
    vertical-align: top;
    background: white;
    overflow: hidden;
}

.chambre-card:hover {
    border-color: #007bff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    text-decoration: none;
}

/* Container image avec bandeau */
.image-container {
    position: relative;
    width: 100%;
    height: 120px;
    overflow: hidden;
}

.image-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px 8px 0 0;
}

/* Informations chambre */
.chambre-info {
    padding: 12px;
    text-align: center;
}

.chambre-info .d-block {
    margin-bottom: 4px;
}

/* Styles pour les bandeaux de statut obliques */
.status-ribbon {
    position: absolute;
    top: 10px;
    right: -25px;
    padding: 4px 25px;
    color: white;
    font-weight: bold;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transform: rotate(45deg);
    box-shadow: 0 2px 4px rgba(0,0,0,0.3);
    z-index: 10;
}

/* Couleurs pour les différents statuts */
.status-libre {
    background: linear-gradient(45deg, #28a745, #20c997);
}

.status-occupée {
    background: linear-gradient(45deg, #dc3545, #e83e8c);
}

.status-réservée {
    background: linear-gradient(45deg, #ffc107, #fd7e14);
}

.status-maintenance {
    background: linear-gradient(45deg, #6c757d, #495057);
}

.status-nettoyage {
    background: linear-gradient(45deg, #17a2b8, #138496);
}

/* Style pour la barre de recherche */
#searchChambre {
    border-radius: 25px;
    padding: 12px 20px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .chambre-card {
        width: 170px;
        margin: 10px;
    }
    
    .image-container {
        height: 100px;
    }
}

@media (max-width: 576px) {
    .chambre-card {
        width: 150px;
        margin: 8px;
    }
    
    .image-container {
        height: 90px;
    }
    
    .status-ribbon {
        font-size: 9px;
        right: -22px;
        padding: 3px 22px;
    }
}
</style>
@endpush