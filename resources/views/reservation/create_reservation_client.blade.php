@extends('layouts.admin')
@section('content')
@include('components.alert.sweet-alert-corner')
<div class="content-wrapper">
    <div class="container-full">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="d-flex align-items-center">
                <div class="mr-auto">
                    <h4 class="page-title">Nouvelle Réservation - Chambre {{ $chambres->numero }}</h4>
                    <div class="d-inline-block align-items-center">
                        <nav>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#"><i class="mdi mdi-home-outline"></i></a></li>
                                <li class="breadcrumb-item"><a href="{{ route('Reservations') }}">Réservations</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('reservation.created') }}">Choix Chambre</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Informations Client</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="row">
                <!-- Panneau Informations Chambre -->
                <div class="col-md-4">
                    <div class="box" style="height: 100%;">
                        <div class="box-header with-border text-center p-4">
                            <h4 class="box-title">Chambre Sélectionnée</h4>
                        </div>
                        <div class="box-body text-center">
                            <div class="chambre-preview">
                                <!-- Container image avec bandeau oblique -->
                                <div class="image-container" style="position: relative; display: inline-block; margin-bottom: 20px;">
                                    <img src="{{ asset('assets/images/bed-empt.jpeg') }}" 
                                         class="img-fluid rounded" 
                                         alt="Chambre {{ $chambres->numero }}"
                                         style="max-height: 200px; object-fit: cover; width: 100%;">
                                    
                                    <!-- Bandeau oblique sur l'image -->
                                    <div class="status-ribbon status-{{ $chambres->statut }}">
                                        {{ ucfirst($chambres->statut) }}
                                    </div>
                                </div>
                                
                                <h4 class="text-primary">Chambre {{ $chambres->numero }}</h4>
                                
                                @if($chambres->type)
                                    <p class="text-muted mb-2">
                                        <i class="fa fa-tag"></i> {{ $chambres->type }}
                                    </p>
                                @endif
                                
                                @if($chambres->prix)
                                    <p class="text-success font-weight-bold h4 mb-3">
                                        {{ number_format($chambres->prix, 0, ',', ' ') }} {{ $chambres->prix_devise }}/heure
                                        <br>
                                        <small class="text-muted">
                                            ({{ number_format($chambres->prix * 24, 0, ',', ' ') }} {{ $chambres->prix_devise }}/jour)
                                        </small>
                                    </p>
                                @endif
                                
                                <div class="chambre-details">
                                    @if($chambres->capacite)
                                        <p class="mb-1">
                                            <i class="fa fa-users"></i> 
                                            Capacité: {{ $chambres->capacite }} personne(s)
                                        </p>
                                    @endif
                                    
                                    @if($chambres->caracteristiques)
                                        <p class="mb-1">
                                            <i class="fa fa-star"></i> 
                                            {{ $chambres->caracteristiques }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panneau Formulaire -->
                <div class="col-md-8">
                    <div class="box" style="height: 100%;">
                        <div class="box-header with-border p-4 text-center">
                            <h4 class="box-title">Informations de Réservation</h4>
                        </div>
                        <div class="box-body">
                            <form id="reservationForm" method="POST" action="{{ route('reservation.store') }}">
                                @csrf
                                <input type="hidden" name="chambre_id" value="{{ $chambre_id }}">
                                <!-- CHAMP CACHÉ UNIQUE POUR LE TOTAL -->
                                <input type="hidden" name="total_reservation" id="total_reservation" value="0">
                                
                                <!-- Étape 1: Informations Client -->
                                <div id="stepClient" class="form-step">
                                    <h5 class="text-primary mb-4">
                                        <i class="fa fa-user"></i> Informations du Client
                                    </h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="nom">Nom complet *</label>
                                                <input type="text" class="form-control" id="nom" name="nom" required 
                                                       value="{{ old('nom') }}" placeholder="Entrez le nom complet">
                                                @error('nom')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="telephone">Téléphone *</label>
                                                <input type="text" class="form-control" id="telephone" name="telephone" required 
                                                       value="{{ old('telephone') }}" placeholder="Numéro de téléphone">
                                                @error('telephone')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="email">Email</label>
                                                <input type="email" class="form-control" id="email" name="email" 
                                                       value="{{ old('email') }}" placeholder="Adresse email">
                                                @error('email')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="identite_type">Type de pièce</label>
                                                <select class="form-control" id="identite_type" name="identite_type">
                                                    <option value="">Sélectionnez...</option>
                                                    <option value="Passeport" {{ old('identite_type') == 'Passeport' ? 'selected' : '' }}>Passeport</option>
                                                    <option value="Carte d'identité" {{ old('identite_type') == 'Carte d\'identité' ? 'selected' : '' }}>Carte d'identité</option>
                                                    <option value="Permis de conduire" {{ old('identite_type') == 'Permis de conduire' ? 'selected' : '' }}>Permis de conduire</option>
                                                    <option value="Autre" {{ old('identite_type') == 'Autre' ? 'selected' : '' }}>Autre</option>
                                                </select>
                                                @error('identite_type')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="identite">Numéro de pièce</label>
                                                <input type="text" class="form-control" id="identite" name="identite" 
                                                       value="{{ old('identite') }}" placeholder="Numéro de la pièce">
                                                @error('identite')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-4">
                                        <div class="col-md-12 text-right">
                                            <button type="button" class="btn btn-primary" id="btnNext" disabled>
                                                Suivant <i class="fa fa-arrow-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Étape 2: Informations Réservation (cachée par défaut) -->
                                <div id="stepReservation" class="form-step" style="display: none;">
                                    <h5 class="text-primary mb-4">
                                        <i class="fa fa-calendar"></i> Période de Réservation
                                    </h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="date_debut">Date et heure d'arrivée *</label>
                                                <input type="datetime-local" class="form-control" id="date_debut" name="date_debut" required
                                                       value="{{ old('date_debut') }}">
                                                @error('date_debut')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="date_fin">Date et heure de départ *</label>
                                                <input type="datetime-local" class="form-control" id="date_fin" name="date_fin" required
                                                       value="{{ old('date_fin') }}">
                                                @error('date_fin')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Calcul automatique -->
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div id="calcul-prix" class="bg-light p-3 rounded" style="display: none;">
                                                <div class="row text-center">
                                                    <div class="col-md-4 border-right">
                                                        <div class="text-muted small">Durée</div>
                                                        <div class="font-weight-bold"><span id="duree-heures">0</span> heure(s)</div>
                                                    </div>
                                                    <div class="col-md-4 border-right">
                                                        <div class="text-muted small">Prix/heure</div>
                                                        <div class="font-weight-bold">{{ number_format($chambres->prix, 0, ',', ' ') }} {{ $chambres->prix_devise }}</div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="text-muted small">Total</div>
                                                        <div class="text-success font-weight-bold h5 mb-0"><span id="prix-total">0</span> {{ $chambres->prix_devise }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <button type="button" class="btn btn-secondary" onclick="showClientStep()">
                                                <i class="fa fa-arrow-left"></i> Retour
                                            </button>
                                        </div>
                                        <div class="col-md-6 text-right">
                                            <button type="submit" class="btn btn-success">
                                                <i class="fa fa-check"></i> Confirmer la Réservation
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
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
// Variables globales
const prixParHeure = {{ $chambres->prix }};
const btnNext = document.getElementById('btnNext');

// Fonction pour vérifier si tous les champs obligatoires sont remplis
function checkFormValidity() {
    const nom = document.getElementById('nom').value.trim();
    const telephone = document.getElementById('telephone').value.trim();
    
    // Activer/désactiver le bouton Suivant
    btnNext.disabled = !(nom && telephone);
}

// Gestion des étapes
function showClientStep() {
    document.getElementById('stepClient').style.display = 'block';
    document.getElementById('stepReservation').style.display = 'none';
}

function showReservationStep() {
    // Validation de l'étape client
    const nom = document.getElementById('nom').value.trim();
    const telephone = document.getElementById('telephone').value.trim();
    
    if (!nom || !telephone) {
        alert('Veuillez remplir les informations obligatoires du client (nom et téléphone)');
        return;
    }

    document.getElementById('stepClient').style.display = 'none';
    document.getElementById('stepReservation').style.display = 'block';
    
    // Initialiser le calcul
    calculerPrix();
}

// Calcul automatique du prix
function calculerPrix() {
    const dateDebut = document.getElementById('date_debut').value;
    const dateFin = document.getElementById('date_fin').value;
    const calculDiv = document.getElementById('calcul-prix');
    const inputTotal = document.getElementById('total_reservation');
    
    if (dateDebut && dateFin) {
        const debut = new Date(dateDebut);
        const fin = new Date(dateFin);
        
        const differenceMs = fin - debut;
        const dureeHeures = Math.ceil(differenceMs / (1000 * 60 * 60));
        
        if (dureeHeures > 0) {
            const prixTotal = dureeHeures * prixParHeure;
            
            document.getElementById('duree-heures').textContent = dureeHeures;
            document.getElementById('prix-total').textContent = prixTotal.toLocaleString();
            calculDiv.style.display = 'block';

            // Mettre à jour l'input caché
            inputTotal.value = prixTotal;
            console.log('Total réservation:', prixTotal); // Pour débogage
        } else {
            calculDiv.style.display = 'none';
            inputTotal.value = 0;
        }
    } else {
        calculDiv.style.display = 'none';
        inputTotal.value = 0;
    }
}

// Écouteurs d'événements pour la validation des champs client
document.getElementById('nom').addEventListener('input', checkFormValidity);
document.getElementById('telephone').addEventListener('input', checkFormValidity);

// Écouteurs d'événements pour le calcul automatique
document.getElementById('date_debut').addEventListener('change', function() {
    const dateFin = document.getElementById('date_fin');
    
    // Si la date de fin n'est pas définie ou est avant la date de début
    if (this.value && (!dateFin.value || new Date(dateFin.value) <= new Date(this.value))) {
        // Définir la date de fin à 1 heure après par défaut
        const dateDebut = new Date(this.value);
        dateDebut.setHours(dateDebut.getHours() + 1);
        dateFin.value = dateDebut.toISOString().slice(0, 16);
    }
    
    calculerPrix();
});

document.getElementById('date_fin').addEventListener('change', calculerPrix);

// Validation du formulaire avant soumission
document.getElementById('reservationForm').addEventListener('submit', function(e) {
    const dateDebut = document.getElementById('date_debut').value;
    const dateFin = document.getElementById('date_fin').value;
    const totalReservation = document.getElementById('total_reservation').value;
    
    console.log('Validation - Total:', totalReservation); // Pour débogage
    
    if (!dateDebut || !dateFin) {
        e.preventDefault();
        alert('Veuillez remplir les dates de réservation');
        return;
    }
    
    const debut = new Date(dateDebut);
    const fin = new Date(dateFin);
    
    if (fin <= debut) {
        e.preventDefault();
        alert('La date de départ doit être après la date d\'arrivée');
        return;
    }
    
    if (totalReservation == 0) {
        e.preventDefault();
        alert('Le calcul du prix n\'est pas valide');
        return;
    }
});

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Pré-remplir la date de début avec l'heure actuelle
    const now = new Date();
    now.setMinutes(0, 0, 0); // Arrondir à l'heure
    document.getElementById('date_debut').value = now.toISOString().slice(0, 16);
    
    // Pré-remplir la date de fin avec 1 heure après
    const uneHeureApres = new Date(now);
    uneHeureApres.setHours(uneHeureApres.getHours() + 1);
    document.getElementById('date_fin').value = uneHeureApres.toISOString().slice(0, 16);
    
    // Vérifier l'état initial du formulaire
    checkFormValidity();
    
    // Ajouter l'écouteur d'événement pour le bouton Suivant
    btnNext.addEventListener('click', showReservationStep);
    
    // Initialiser le calcul au chargement
    setTimeout(calculerPrix, 100);
});
</script>

<style>
.chambre-preview {
    padding: 15px;
}

.chambre-details p {
    margin-bottom: 5px;
    font-size: 0.9rem;
}

.form-step {
    transition: all 0.3s ease;
}

.btn {
    border-radius: 5px;
}

.form-control {
    border-radius: 5px;
}

/* Assurer que les deux panneaux ont la même hauteur */
.box {
    display: flex;
    flex-direction: column;
}

.box-body {
    flex: 1;
}

/* Style pour le bouton désactivé */
.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Style pour la section de calcul */
.bg-light {
    background-color: #f8f9fa !important;
}

.border-right {
    border-right: 1px solid #dee2e6;
}

/* Styles pour les bandeaux de statut obliques */
.status-ribbon {
    position: absolute;
    top: 15px;
    right: -30px;
    padding: 5px 30px;
    color: white;
    font-weight: bold;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 1px;
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

/* Container image */
.image-container {
    position: relative;
    display: inline-block;
    overflow: hidden;
    border-radius: 8px;
}

/* Effet de superposition pour l'image */
.image-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.1);
    z-index: 1;
    border-radius: 8px;
}

.image-container img {
    position: relative;
    z-index: 0;
}

/* Ajustement pour le texte sous l'image */
.chambre-preview h4 {
    margin-top: 15px;
    margin-bottom: 10px;
}
</style>
@endpush