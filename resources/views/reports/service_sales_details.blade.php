@extends("layouts.admin")

@section("content")
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <div class="container-full">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="d-flex align-items-center">
                <div class="col-xl-12">
                    @include("components.menus.reports")
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="row">
                <div class="col-12">
                    <div class="box">
                        <div class="box-header with-border p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-center">
                                    <h4 class="box-title text-center">Détails de la journée de vente</h4>
                                    <p class="text-muted mb-0">
                                        {{ $saleday->sale_date->format('d/m/Y') }} - {{ $emplacement->libelle }}
                                        @if($saleday->end_time)
                                            <span class="badge badge-success ms-2">Terminée</span>
                                        @else
                                            <span class="badge badge-warning ms-2">En cours</span>
                                        @endif
                                    </p>
                                </div>
                                <a href="{{ route('reports.service_sales.emplacement', ['emplacement_id' => $emplacement->id]) }}" 
                                   class="btn btn-secondary">
                                    <i class="fa fa-arrow-left"></i> Retour
                                </a>
                            </div>
                        </div>

                        <div class="box-body">
                            @php
                                // Filtrer seulement les factures payées
                                $facturesPayees = $saleday->factures->where('statut', 'payée');
                                $facturesAvecRemises = $saleday->factures->where('remise', '>', 0);
                                $facturesAnnulees = $saleday->factures->where('statut', 'annulée');
                            @endphp

                            <!-- Statistiques principales -->
                            <div class="row mb-4">
                                <div class="col-xl-3 col-md-6">
                                    <div class="box bg-primary text-white">
                                        <div class="box-body text-center">
                                            <h2 class="mb-0">{{ $facturesPayees->count() }}</h2>
                                            <p class="mb-0">Factures Payées</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="box bg-success text-white">
                                        <div class="box-body text-center">
                                            @php
                                                $totalCA = $facturesPayees->sum('total_ttc');
                                                $deviseCA = $facturesPayees->first()->payments->first()->devise ?? 'FCFA';
                                            @endphp
                                            <h2 class="mb-0">{{ number_format($totalCA, 0, ',', ' ') }}</h2>
                                            <p class="mb-0">Chiffre d'Affaires ({{ $deviseCA }})</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="box bg-info text-white">
                                        <div class="box-body text-center">
                                            @php
                                                $panierMoyen = $facturesPayees->count() > 0 ? 
                                                    $facturesPayees->sum('total_ttc') / $facturesPayees->count() : 0;
                                                $devisePanier = $facturesPayees->first()->payments->first()->devise ?? 'FC';
                                            @endphp
                                            <h2 class="mb-0">{{ number_format($panierMoyen, 0, ',', ' ') }}</h2>
                                            <p class="mb-0">Panier Moyen ({{ $devisePanier }})</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="box bg-warning text-white">
                                        <div class="box-body text-center">
                                            @php
                                                $totalRemises = $facturesPayees->sum('remise');
                                                $deviseRemises = $facturesPayees->first()->payments->first()->devise ?? 'FC';
                                            @endphp
                                            <h2 class="mb-0">{{ number_format($totalRemises, 0, ',', ' ') }}</h2>
                                            <p class="mb-0">Total Remises ({{ $deviseRemises }})</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Ventes par serveur/caissier -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="box">
                                        <div class="box-header">
                                            <h4 class="box-title">Ventes par Serveur/Caissier (Factures Payées)</h4>
                                        </div>
                                        <div class="box-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Serveur/Caissier</th>
                                                            <th>Nombre de Factures</th>
                                                            <th>Chiffre d'Affaires</th>
                                                            <th>Panier Moyen</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $ventesParUtilisateur = $facturesPayees->groupBy('user_id');
                                                        @endphp
                                                        @foreach($ventesParUtilisateur as $userId => $factures)
                                                            @php
                                                                $user = $factures->first()->user;
                                                                $nbFactures = $factures->count();
                                                                $ca = $factures->sum('total_ttc');
                                                                $panierMoyenUser = $nbFactures > 0 ? $ca / $nbFactures : 0;
                                                                // Récupérer la devise depuis les paiements
                                                                $deviseUser = 'FC';
                                                                foreach($factures as $facture) {
                                                                    if($facture->payments->isNotEmpty()) {
                                                                        $deviseUser = $facture->payments->first()->devise;
                                                                        break;
                                                                    }
                                                                }
                                                            @endphp
                                                            <tr>
                                                                <td>
                                                                    <strong>{{ $user->name }}</strong>
                                                                    <br>
                                                                    <small class="text-muted">{{ $user->role }}</small>
                                                                </td>
                                                                <td class="text-center">{{ $nbFactures }}</td>
                                                                <td class="text-end">{{ number_format($ca, 0, ',', ' ') }} {{ $deviseUser }}</td>
                                                                <td class="text-end">{{ number_format($panierMoyenUser, 0, ',', ' ') }} {{ $deviseUser }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Classement des plats et boissons -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="box">
                                        <div class="box-header">
                                            <h4 class="box-title">Classement des Produits les Plus Vendus (Factures Payées)</h4>
                                        </div>
                                        <div class="box-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Produit</th>
                                                            <th>Catégorie</th>
                                                            <th>Quantité Vendue</th>
                                                            <th>Chiffre d'Affaires</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $produitsVendus = [];
                                                            $deviseProduits = 'FC';
                                                            foreach($facturesPayees as $facture) {
                                                                // Récupérer la devise pour les produits
                                                                if($facture->payments->isNotEmpty() && $deviseProduits === 'FC') {
                                                                    $deviseProduits = $facture->payments->first()->devise;
                                                                }
                                                                foreach($facture->details as $detail) {
                                                                    $produitId = $detail->produit_id;
                                                                    if (!isset($produitsVendus[$produitId])) {
                                                                        $produitsVendus[$produitId] = [
                                                                            'produit' => $detail->produit,
                                                                            'quantite' => 0,
                                                                            'ca' => 0
                                                                        ];
                                                                    }
                                                                    $produitsVendus[$produitId]['quantite'] += $detail->quantite;
                                                                    $produitsVendus[$produitId]['ca'] += $detail->total_ligne;
                                                                }
                                                            }
                                                            // Trier par quantité décroissante
                                                            usort($produitsVendus, function($a, $b) {
                                                                return $b['quantite'] - $a['quantite'];
                                                            });
                                                        @endphp
                                                        @if(count($produitsVendus) > 0)
                                                            @foreach(array_slice($produitsVendus, 0, 10) as $produitData)
                                                                <tr>
                                                                    <td>
                                                                        <strong>{{ $produitData['produit']->libelle }}</strong>
                                                                        <br>
                                                                        <small class="text-muted">{{ $produitData['produit']->reference }}</small>
                                                                    </td>
                                                                    <td>
                                                                        @if($produitData['produit']->categorie)
                                                                            {{ $produitData['produit']->categorie->libelle }}
                                                                        @else
                                                                            <span class="text-muted">-</span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <span class="badge badge-primary">{{ $produitData['quantite'] }}</span>
                                                                    </td>
                                                                    <td class="text-end">{{ number_format($produitData['ca'], 0, ',', ' ') }} {{ $deviseProduits }}</td>
                                                                </tr>
                                                            @endforeach
                                                        @else
                                                            <tr>
                                                                <td colspan="4" class="text-center text-muted">
                                                                    Aucun produit vendu dans les factures payées
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Historique des remises et annulations -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="box">
                                        <div class="box-header">
                                            <h4 class="box-title">Factures avec Remises (Toutes)</h4>
                                        </div>
                                        <div class="box-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>N° Facture</th>
                                                            <th>Serveur</th>
                                                            <th>Remise</th>
                                                            <th>Total TTC</th>
                                                            <th>Statut</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($facturesAvecRemises as $facture)
                                                            @php
                                                                $deviseFacture = $facture->payments->first()->devise ?? 'FCFA';
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $facture->numero_facture }}</td>
                                                                <td>{{ $facture->user->name }}</td>
                                                                <td class="text-end text-warning">-{{ number_format($facture->remise, 0, ',', ' ') }} {{ $deviseFacture }}</td>
                                                                <td class="text-end">{{ number_format($facture->total_ttc, 0, ',', ' ') }} {{ $deviseFacture }}</td>
                                                                <td>
                                                                    <span class="badge 
                                                                        @if($facture->statut == 'payée') badge-success
                                                                        @elseif($facture->statut == 'annulée') badge-danger
                                                                        @else badge-warning @endif">
                                                                        {{ $facture->statut }}
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="box">
                                        <div class="box-header">
                                            <h4 class="box-title">Factures Annulées</h4>
                                        </div>
                                        <div class="box-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>N° Facture</th>
                                                            <th>Serveur</th>
                                                            <th>Montant</th>
                                                            <th>Statut</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($facturesAnnulees as $facture)
                                                            @php
                                                                $deviseFacture = $facture->payments->first()->devise ?? 'FC';
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $facture->numero_facture }}</td>
                                                                <td>{{ $facture->user->name }}</td>
                                                                <td class="text-end">{{ number_format($facture->total_ttc, 0, ',', ' ') }} {{ $deviseFacture }}</td>
                                                                <td>
                                                                    <span class="badge badge-danger">{{ $facture->statut }}</span>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Détails complets des factures -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="box">
                                        <div class="box-header">
                                            <h4 class="box-title">Détails Complets des Factures</h4>
                                            <div class="box-tools">
                                                <span class="badge badge-success">Payée</span>
                                                <span class="badge badge-warning ms-1">En attente</span>
                                                <span class="badge badge-danger ms-1">Annulée</span>
                                            </div>
                                        </div>
                                        <div class="box-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>N° Facture</th>
                                                            <th>Serveur</th>
                                                            <th>Table/Chambre</th>
                                                            <th>Total HT</th>
                                                            <th>Remise</th>
                                                            <th>Total TTC</th>
                                                            <th>Devise</th>
                                                            <th>Statut</th>
                                                            <th>Date</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($saleday->factures as $facture)
                                                            @php
                                                                $deviseFacture = $facture->payments->first()->devise ?? 'FC';
                                                            @endphp
                                                            <tr>
                                                                <td>
                                                                    <strong>{{ $facture->numero_facture }}</strong>
                                                                </td>
                                                                <td>{{ $facture->user->name }}</td>
                                                                <td>
                                                                    @if($facture->table)
                                                                        Table {{ $facture->table->numero }}
                                                                    @elseif($facture->chambre)
                                                                        Chambre {{ $facture->chambre->numero }}
                                                                    @else
                                                                        <span class="text-muted">N/A</span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-end">{{ number_format($facture->total_ht, 0, ',', ' ') }} {{ $deviseFacture }}</td>
                                                                <td class="text-end text-warning">{{ number_format($facture->remise, 0, ',', ' ') }} {{ $deviseFacture }}</td>
                                                                <td class="text-end">
                                                                    <strong>{{ number_format($facture->total_ttc, 0, ',', ' ') }} {{ $deviseFacture }}</strong>
                                                                </td>
                                                                <td class="text-center">
                                                                    <span class="badge badge-info">{{ $deviseFacture }}</span>
                                                                </td>
                                                                <td>
                                                                    <span class="badge 
                                                                        @if($facture->statut == 'payée') badge-success
                                                                        @elseif($facture->statut == 'annulée') badge-danger
                                                                        @else badge-warning @endif">
                                                                        {{ $facture->statut }}
                                                                    </span>
                                                                </td>
                                                                <td>{{ $facture->date_facture}}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /.content -->
    </div>
</div>
<!-- /.content-wrapper -->
@endsection

@push('styles')
<style>
.box.bg-primary, .box.bg-success, .box.bg-info, .box.bg-warning {
    border-radius: 8px;
    border: none;
}

.box.bg-primary .box-body, 
.box.bg-success .box-body, 
.box.bg-info .box-body,
.box.bg-warning .box-body {
    padding: 20px;
}

.badge {
    font-size: 12px;
    padding: 6px 10px;
}

.table-responsive {
    border-radius: 8px;
    overflow: hidden;
}
</style>
@endpush