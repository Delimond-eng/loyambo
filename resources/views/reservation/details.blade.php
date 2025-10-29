@extends('layouts.admin')

@section('content')
<div class="content-wrapper">
    <div class="container-full">
        <div class="content-header">
            <div class="d-flex align-items-center">
                <div class="mr-auto">
                    <h3 class="page-title">Détails de la Réservation #RES{{ str_pad($reservation->id, 6, '0', STR_PAD_LEFT) }}</h3>
                    <div class="d-inline-block align-items-center">
                        <nav>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#"><i class="mdi mdi-home-outline"></i></a></li>
                                <li class="breadcrumb-item"><a href="{{ route('Reservations') }}">Réservations</a></li>
                                <li class="breadcrumb-item active">Détails</li>
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
                        <div class="box-header with-border">
                            <h4 class="box-title">Informations Générales</h4>
                            <div class="box-controls pull-right">
                                <span class="badge badge-{{ match($reservation->statut) {
                                    'confirmée' => 'success',
                                    'en cours' => 'info',
                                    'terminée' => 'primary',
                                    'annulée' => 'danger',
                                    default => 'secondary'
                                } }}">{{ $reservation->statut }}</span>
                            </div>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <!-- Informations Réservation -->
                                <div class="col-md-6">
                                    <h5>Détails de la Réservation</h5>
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Référence:</th>
                                            <td>#RES{{ str_pad($reservation->id, 6, '0', STR_PAD_LEFT) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Date création:</th>
                                            <td>{{ $reservation->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Date d'arrivée:</th>
                                            <td>{{ \Carbon\Carbon::parse($reservation->date_debut)->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Date de départ:</th>
                                            <td>{{ \Carbon\Carbon::parse($reservation->date_fin)->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Durée du séjour:</th>
                                            <td><span class="badge badge-info">{{ $dureeAffichage }}</span></td>
                                        </tr>
                                        <tr>
                                            <th>Statut:</th>
                                            <td>
                                                <span class="badge badge-{{ match($reservation->statut) {
                                                    'confirmée' => 'success',
                                                    'en cours' => 'info',
                                                    'terminée' => 'primary',
                                                    'annulée' => 'danger',
                                                    default => 'secondary'
                                                } }}">{{ $reservation->statut }}</span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>

                                <!-- Informations Chambre -->
                                <div class="col-md-6">
                                    <h5>Informations Chambre</h5>
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Numéro:</th>
                                            <td>{{ $reservation->chambre->numero }}</td>
                                        </tr>
                                        <tr>
                                            <th>Type:</th>
                                            <td>{{ $reservation->chambre->type }}</td>
                                        </tr>
                                        <tr>
                                            <th>Capacité:</th>
                                            <td>{{ $reservation->chambre->capacite }} personne(s)</td>
                                        </tr>
                                        <tr>
                                            <th>Prix horaire:</th>
                                            <td>{{ number_format($reservation->chambre->prix, 0, ',', ' ') }} {{ $reservation->chambre->prix_devise }}/heure</td>
                                        </tr>
                                        <tr>
                                            <th>Prix journalier:</th>
                                            <td>{{ number_format($reservation->chambre->prix * 24, 0, ',', ' ') }} {{ $reservation->chambre->prix_devise }}/jour</td>
                                        </tr>
                                        <tr>
                                            <th>Statut chambre:</th>
                                            <td>
                                                <span class="badge badge-{{ match($reservation->chambre->statut) {
                                                    'libre' => 'success',
                                                    'occupée' => 'danger',
                                                    'réservée' => 'warning',
                                                    default => 'secondary'
                                                } }}">{{ $reservation->chambre->statut }}</span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <!-- Informations Client -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h5>Informations Client</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th>Nom:</th>
                                                <td>{{ $reservation->client->nom }}</td>
                                                <th>Téléphone:</th>
                                                <td>{{ $reservation->client->telephone ?? 'Non renseigné' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Email:</th>
                                                <td>{{ $reservation->client->email ?? 'Non renseigné' }}</td>
                                                <th>Identité:</th>
                                                <td>
                                                    @if($reservation->client->identite)
                                                        {{ $reservation->client->identite_type ?? 'CNI' }}: {{ $reservation->client->identite }}
                                                    @else
                                                        Non renseigné
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Informations Financières -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h5>Informations Financières</h5>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="info-box bg-light">
                                                <span class="info-box-icon bg-info"><i class="fa fa-calculator"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Montant Théorique</span>
                                                    <span class="info-box-number">{{ number_format($montantTheorique, 0, ',', ' ') }} {{ $reservation->chambre->prix_devise }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Facture et Paiements -->
                            @if($facture)
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h5>Facture et Paiements</h5>
                                    
                                    <!-- Détails Facture -->
                                    <div class="card mb-4">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="card-title mb-0">Facture #{{ $facture->numero_facture }}</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') }}</p>
                                                    <p><strong>Total TTC:</strong> {{ number_format($facture->total_ttc, 0, ',', ' ') }} {{ $facture->devise }}</p>
                                                    <p><strong>Statut:</strong> 
                                                        <span class="badge badge-{{ $facture->statut == 'payée' ? 'success' : 'warning' }}">
                                                            {{ $facture->statut }}
                                                        </span>
                                                    </p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>Remise:</strong> {{ number_format($facture->remise, 0, ',', ' ') }} {{ $facture->devise }}</p>
                                                    <p><strong>Total HT:</strong> {{ number_format($facture->total_ht, 0, ',', ' ') }} {{ $facture->devise }}</p>
                                                </div>
                                            </div>

                                            <!-- Détails de la facture -->
                                            @if($facture->details->count() > 0)
                                            <div class="mt-3">
                                                <h6>Détails de la facture:</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Description</th>
                                                                <th>Quantité</th>
                                                                <th>Prix Unitaire</th>
                                                                <th>Total</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($facture->details as $detail)
                                                            <tr>
                                                                <td>{{ $detail->description ?? 'Séjour chambre' }}</td>
                                                                <td>{{ $detail->quantite ?? 1 }}</td>
                                                                <td>{{ number_format($detail->prix_unitaire, 0, ',', ' ') }} {{ $facture->devise }}</td>
                                                                <td>{{ number_format($detail->montant_total, 0, ',', ' ') }} {{ $facture->devise }}</td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Paiements -->
                                    @if($facture->payments->count() > 0)
                                    <div class="card">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="card-title mb-0">Paiements</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Date</th>
                                                            <th>Mode</th>
                                                            <th>Référence</th>
                                                            <th>Montant</th>
                                                            <th>Statut</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($facture->payments as $payment)
                                                        <tr>
                                                            <td>{{ \Carbon\Carbon::parse($payment->pay_date)->format('d/m/Y H:i') }}</td>
                                                            <td>
                                                                <span class="badge badge-info">{{ $payment->mode }}</span>
                                                            </td>
                                                            <td>{{ $payment->mode_ref ?? 'N/A' }}</td>
                                                            <td>{{ number_format($payment->amount, 0, ',', ' ') }} {{ $payment->devise }}</td>
                                                            <td>
                                                                <span class="badge badge-success">Payé</span>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot>
                                                        <tr class="table-primary">
                                                            <td colspan="3" class="text-right"><strong>Total payé:</strong></td>
                                                            <td colspan="2">
                                                                <strong>{{ number_format($facture->payments->sum('amount'), 0, ',', ' ') }} {{ $facture->devise }}</strong>
                                                            </td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    @else
                                    <div class="alert alert-warning">
                                        <i class="fa fa-exclamation-triangle"></i> Aucun paiement enregistré pour cette facture.
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @else
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="fa fa-info-circle"></i> Aucune facture associée à cette réservation.
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="box-footer">
                            <a href="{{ route('Reservations') }}" class="btn btn-secondary">
                                <i class="fa fa-arrow-left"></i> Retour à la liste
                            </a>
                            @if($reservation->statut == 'en_attente')
                            <a href="{{ route('reservations.edit', $reservation->id) }}" class="btn btn-primary">
                                <i class="fa fa-edit"></i> Modifier
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('styles')
<style>
.info-box {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border-radius: 0.25rem;
}
.card-header {
    border-bottom: 1px solid rgba(0,0,0,.125);
}
</style>
@endpush