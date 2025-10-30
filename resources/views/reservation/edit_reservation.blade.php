@extends('layouts.admin')

@section('content')
<div class="content-wrapper">
    <div class="container-full">
        <div class="content-header">
            <div class="d-flex align-items-center">
                <div class="mr-auto">
                    <h3 class="page-title">Modifier la Réservation #RES{{ str_pad($reservation->id, 6, '0', STR_PAD_LEFT) }}</h3>
                    <div class="d-inline-block align-items-center">
                        <nav>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#"><i class="mdi mdi-home-outline"></i></a></li>
                                <li class="breadcrumb-item"><a href="{{ route('Reservations') }}">Réservations</a></li>
                                <li class="breadcrumb-item active">Modifier</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="row">
                <div class="col-12">
                    <div class="box">
                        <div class="box-header with-border text-center p-5">
                            <h4 class="box-title p-5">Modifier les informations de réservation</h4>
                            <p class="text-muted">Veuillez mettre à jour les informations ci-dessous</p>
                        </div>
                        <form method="POST" action="{{ route('reservation.update', $reservation->id) }}">
                            @csrf
                            @method('PUT')
                            <div class="box-body">
                                
                                <!-- Informations Client -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="border-bottom pb-2 mb-3">Informations du Client</h6>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="client_nom">Nom du Client *</label>
                                            <input type="text" class="form-control" id="client_nom" name="client_nom" 
                                                   value="{{ old('client_nom', $reservation->client->nom) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="client_telephone">Téléphone</label>
                                            <input type="text" class="form-control" id="client_telephone" name="client_telephone"
                                                   value="{{ old('client_telephone', $reservation->client->telephone) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="client_email">Email</label>
                                            <input type="email" class="form-control" id="client_email" name="client_email"
                                                   value="{{ old('client_email', $reservation->client->email) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="client_identite">Numéro d'identité</label>
                                            <input type="text" class="form-control" id="client_identite" name="client_identite"
                                                   value="{{ old('client_identite', $reservation->client->identite) }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- Informations Réservation -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="border-bottom pb-2 mb-3">Informations de Réservation</h6>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="chambre_id">Chambre *</label>
                                            <select class="form-control" id="chambre_id" name="chambre_id" required>
                                                <option value="">Sélectionner une chambre</option>
                                                @foreach($chambres as $chambre)
                                                    <option value="{{ $chambre->id }}" 
                                                            data-prix="{{ $chambre->prix }}"
                                                            data-devise="{{ $chambre->prix_devise }}"
                                                            {{ old('chambre_id', $reservation->chambre_id) == $chambre->id ? 'selected' : '' }}>
                                                        Chambre {{ $chambre->numero }} - {{ $chambre->type }} 
                                                        ({{ number_format($chambre->prix * 24, 0, ',', ' ') }} {{ $chambre->prix_devise }}/jour)
                                                        - {{ $chambre->statut }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="date_debut">Date et heure d'arrivée *</label>
                                            <input type="datetime-local" class="form-control" id="date_debut" name="date_debut" 
                                                   value="{{ old('date_debut', \Carbon\Carbon::parse($reservation->date_debut)->format('Y-m-d\TH:i')) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="date_fin">Date et heure de départ *</label>
                                            <input type="datetime-local" class="form-control" id="date_fin" name="date_fin"
                                                   value="{{ old('date_fin', \Carbon\Carbon::parse($reservation->date_fin)->format('Y-m-d\TH:i')) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="observations">Observations</label>
                                            <textarea class="form-control" id="observations" name="observations" rows="3" 
                                                      placeholder="Notes supplémentaires...">{{ old('observations', $reservation->observations) }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informations de calcul -->
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <h6 class="border-bottom pb-2 mb-3">Calcul du séjour</h6>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Durée totale</label>
                                            <div class="form-control bg-light" id="duree_calcul">
                                                @php
                                                    $dateDebut = \Carbon\Carbon::parse($reservation->date_debut);
                                                    $dateFin = \Carbon\Carbon::parse($reservation->date_fin);
                                                    
                                                    // Calcul précis en jours, heures, minutes, secondes
                                                    $dureeSecondes = $dateDebut->diffInSeconds($dateFin);
                                                    $dureeMinutesTotal = floor($dureeSecondes / 60);
                                                    $dureeHeuresTotal = floor($dureeMinutesTotal / 60);
                                                    $dureeJours = floor($dureeHeuresTotal / 24);
                                                    
                                                    $heuresRestantes = $dureeHeuresTotal % 24;
                                                    $minutesRestantes = $dureeMinutesTotal % 60;
                                                    $secondesRestantes = $dureeSecondes % 60;
                                                    
                                                    // Formater la durée de manière précise
                                                    if ($dureeJours > 0) {
                                                        if ($heuresRestantes > 0) {
                                                            echo $dureeJours . ' jour(s) et ' . $heuresRestantes . ' heure(s)';
                                                        } else {
                                                            echo $dureeJours . ' jour(s)';
                                                        }
                                                    } else {
                                                        if ($dureeHeuresTotal > 0) {
                                                            if ($minutesRestantes > 0) {
                                                                echo $dureeHeuresTotal . 'h' . str_pad($minutesRestantes, 2, '0', STR_PAD_LEFT);
                                                            } else {
                                                                echo $dureeHeuresTotal . ' heure(s)';
                                                            }
                                                        } else {
                                                            echo $dureeMinutesTotal . ' minute(s)';
                                                        }
                                                    }
                                                @endphp
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Détail durée</label>
                                            <div class="form-control bg-light small" id="duree_detail">
                                                @php
                                                    echo $dureeJours . 'j ' . $heuresRestantes . 'h ' . $minutesRestantes . 'm ' . $secondesRestantes . 's';
                                                @endphp
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Tarif appliqué</label>
                                            <div class="form-control bg-light" id="tarif_applique">
                                                @php
                                                    if ($dureeJours > 0) {
                                                        echo number_format($reservation->chambre->prix * 24, 0, ',', ' ') . ' ' . $reservation->chambre->prix_devise . '/jour';
                                                    } else {
                                                        echo number_format($reservation->chambre->prix, 0, ',', ' ') . ' ' . $reservation->chambre->prix_devise . '/heure';
                                                    }
                                                @endphp
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Montant total</label>
                                            <div class="form-control bg-light fw-bold" id="montant_total">
                                                @php
                                                    // Calcul PRÉCIS du montant avec heures, minutes, secondes
                                                    $montantTotal = 0;
                                                    $prixHoraire = $reservation->chambre->prix;
                                                    $prixJournalier = $prixHoraire * 24;
                                                    $prixParMinute = $prixHoraire / 60;
                                                    $prixParSeconde = $prixParMinute / 60;
                                                    
                                                    if ($dureeJours > 0) {
                                                        // Jours complets + heures restantes + minutes + secondes
                                                        $montantJoursComplets = $dureeJours * $prixJournalier;
                                                        $montantHeuresRestantes = $heuresRestantes * $prixHoraire;
                                                        $montantMinutesRestantes = $minutesRestantes * $prixParMinute;
                                                        $montantSecondesRestantes = $secondesRestantes * $prixParSeconde;
                                                        $montantTotal = $montantJoursComplets + $montantHeuresRestantes + $montantMinutesRestantes + $montantSecondesRestantes;
                                                    } else {
                                                        // Calcul précis pour moins de 24h
                                                        $montantHeures = $dureeHeuresTotal * $prixHoraire;
                                                        $montantMinutes = $minutesRestantes * $prixParMinute;
                                                        $montantSecondes = $secondesRestantes * $prixParSeconde;
                                                        $montantTotal = $montantHeures + $montantMinutes + $montantSecondes;
                                                    }
                                                    echo number_format($montantTotal, 2, ',', ' ') . ' ' . $reservation->chambre->prix_devise;
                                                @endphp
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Champs cachés pour envoyer les données calculées -->
                                <input type="hidden" name="duree_secondes" id="duree_secondes" value="{{ $dureeSecondes }}">
                                <input type="hidden" name="duree_jours" id="duree_jours" value="{{ $dureeJours }}">
                                <input type="hidden" name="duree_heures" id="duree_heures" value="{{ $heuresRestantes }}">
                                <input type="hidden" name="duree_minutes" id="duree_minutes" value="{{ $minutesRestantes }}">
                                <input type="hidden" name="montant_total" id="montant_total_input" value="{{ $montantTotal }}">
                                <input type="hidden" name="devise" id="devise_input" value="{{ $reservation->chambre->prix_devise }}">
                            </div>
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Enregistrer les modifications
                                </button>
                                <a href="{{ route('Reservations') }}" class="btn btn-secondary">
                                    <i class="fa fa-times"></i> Annuler
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
function calculerDureeEtMontant(dateDebut, dateFin, prixUnitaire, devise) {
    const debut = new Date(dateDebut);
    const fin = new Date(dateFin);
    
    // Calcul en SECONDES pour une précision maximale
    const dureeSecondes = Math.max(0, Math.floor((fin - debut) / 1000));
    const dureeMinutesTotal = Math.floor(dureeSecondes / 60);
    const dureeHeuresTotal = Math.floor(dureeMinutesTotal / 60);
    const dureeJours = Math.floor(dureeHeuresTotal / 24);
    
    const heuresRestantes = dureeHeuresTotal % 24;
    const minutesRestantes = dureeMinutesTotal % 60;
    const secondesRestantes = dureeSecondes % 60;
    
    // Formatage de la durée
    let dureeText;
    let dureeDetail;
    let tarifApplique;
    
    if (dureeJours > 0) {
        if (heuresRestantes > 0) {
            dureeText = `${dureeJours} jour(s) et ${heuresRestantes} heure(s)`;
        } else {
            dureeText = `${dureeJours} jour(s)`;
        }
        tarifApplique = `${(prixUnitaire * 24).toLocaleString()} ${devise}/jour`;
    } else {
        if (dureeHeuresTotal > 0) {
            if (minutesRestantes > 0) {
                dureeText = `${dureeHeuresTotal}h${minutesRestantes.toString().padStart(2, '0')}`;
            } else {
                dureeText = `${dureeHeuresTotal} heure(s)`;
            }
        } else {
            dureeText = `${dureeMinutesTotal} minute(s)`;
        }
        tarifApplique = `${prixUnitaire.toLocaleString()} ${devise}/heure`;
    }
    
    dureeDetail = `${dureeJours}j ${heuresRestantes}h ${minutesRestantes}m ${secondesRestantes}s`;
    
    // Calcul PRÉCIS du montant avec toutes les unités de temps
    let montantTotal = 0;
    const prixParMinute = prixUnitaire / 60;
    const prixParSeconde = prixParMinute / 60;
    
    if (dureeJours > 0) {
        // Jours complets + heures restantes + minutes + secondes
        const prixJournalier = prixUnitaire * 24;
        const montantJoursComplets = dureeJours * prixJournalier;
        const montantHeuresRestantes = heuresRestantes * prixUnitaire;
        const montantMinutesRestantes = minutesRestantes * prixParMinute;
        const montantSecondesRestantes = secondesRestantes * prixParSeconde;
        montantTotal = montantJoursComplets + montantHeuresRestantes + montantMinutesRestantes + montantSecondesRestantes;
    } else {
        // Calcul précis pour moins de 24h
        const montantHeures = dureeHeuresTotal * prixUnitaire;
        const montantMinutes = minutesRestantes * prixParMinute;
        const montantSecondes = secondesRestantes * prixParSeconde;
        montantTotal = montantHeures + montantMinutes + montantSecondes;
    }
    
    return {
        dureeSecondes,
        dureeJours,
        dureeHeuresTotal,
        heuresRestantes,
        minutesRestantes,
        secondesRestantes,
        dureeText,
        dureeDetail,
        tarifApplique,
        montantTotal
    };
}

function mettreAJourCalcul() {
    const dateDebut = document.getElementById('date_debut').value;
    const dateFin = document.getElementById('date_fin').value;
    const chambreSelect = document.getElementById('chambre_id');
    const selectedOption = chambreSelect.selectedOptions[0];
    const prixUnitaire = parseFloat(selectedOption?.dataset.prix) || 0;
    const devise = selectedOption?.dataset.devise || 'CDF';
    
    if (dateDebut && dateFin && prixUnitaire > 0) {
        const calcul = calculerDureeEtMontant(dateDebut, dateFin, prixUnitaire, devise);
        
        // Mettre à jour l'affichage
        document.getElementById('duree_calcul').textContent = calcul.dureeText;
        document.getElementById('duree_detail').textContent = calcul.dureeDetail;
        document.getElementById('tarif_applique').textContent = calcul.tarifApplique;
        document.getElementById('montant_total').textContent = `${calcul.montantTotal.toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2})} ${devise}`;
        
        // Mettre à jour les champs cachés
        document.getElementById('duree_secondes').value = calcul.dureeSecondes;
        document.getElementById('duree_jours').value = calcul.dureeJours;
        document.getElementById('duree_heures').value = calcul.heuresRestantes;
        document.getElementById('duree_minutes').value = calcul.minutesRestantes;
        document.getElementById('montant_total_input').value = calcul.montantTotal;
        document.getElementById('devise_input').value = devise;
    } else {
        // Réinitialiser si données incomplètes
        document.getElementById('duree_calcul').textContent = '0 minute(s)';
        document.getElementById('duree_detail').textContent = '0j 0h 0m 0s';
        document.getElementById('tarif_applique').textContent = '0 CDF/heure';
        document.getElementById('montant_total').textContent = '0,00 CDF';
        document.getElementById('duree_secondes').value = 0;
        document.getElementById('duree_jours').value = 0;
        document.getElementById('duree_heures').value = 0;
        document.getElementById('duree_minutes').value = 0;
        document.getElementById('montant_total_input').value = 0;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Événements pour le calcul automatique
    document.getElementById('date_debut').addEventListener('change', mettreAJourCalcul);
    document.getElementById('date_fin').addEventListener('change', mettreAJourCalcul);
    document.getElementById('chambre_id').addEventListener('change', mettreAJourCalcul);
    
    // Calcul initial au chargement
    mettreAJourCalcul();
});
</script>

<style>
.form-control[readonly] {
    background-color: #f8f9fa;
    cursor: not-allowed;
}
</style>
@endpush