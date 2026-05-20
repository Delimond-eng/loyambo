@extends("layouts.admin")

@section("content")
<div class="content-wrapper">
    <div class="container-full">
        <div class="content-header">
            <div class="d-flex align-items-center">
               <div class="col-xl-12">
                    @include("components.menus.reports")
                </div>
            </div>
        </div>

        <section class="content">
            <div class="row">
                <div class="col-12">
                    <div class="box">
                        <div class="box-header with-border p-5 text-center">
                            <h4 class="box-title">Rapport Financier - Recettes et Trésorerie</h4>
                            <p class="text-muted">Suivi des recettes par emplacement, mode de paiement et devise</p>
                        </div>

                        <!-- Filtres -->
                        <div class="box-body">
                            <form method="GET" action="{{ route('reports.finances') }}" id="filterForm">
                                <div class="row mb-4">
                                    <div class="col-md-3">
                                        <label for="emplacement_id">Emplacement</label>
                                        <select name="emplacement_id" id="emplacement_id" class="form-control">
                                            <option value="">Tous les emplacements</option>
                                            @foreach($emplacements as $emplacement)
                                                <option value="{{ $emplacement->id }}" {{ request('emplacement_id') == $emplacement->id ? 'selected' : '' }}>
                                                    {{ $emplacement->libelle }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="caissier_id">Caissier</label>
                                        <select name="caissier_id" id="caissier_id" class="form-control">
                                            <option value="">Tous les caissiers</option>
                                            @foreach($caissiers as $caissier)
                                                <option value="{{ $caissier->id }}" {{ request('caissier_id') == $caissier->id ? 'selected' : '' }}>
                                                    {{ $caissier->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="mode">Mode paiement</label>
                                        <select name="mode" id="mode" class="form-control">
                                            <option value="">Tous modes</option>
                                            <option value="cash" {{ request('mode') == 'cash' ? 'selected' : '' }}>Espèces</option>
                                            <option value="card" {{ request('mode') == 'card' ? 'selected' : '' }}>Carte</option>
                                            <option value="mobile" {{ request('mode') == 'mobile' ? 'selected' : '' }}>Mobile</option>
                                            <option value="virement" {{ request('mode') == 'virement' ? 'selected' : '' }}>Virement</option>
                                            <option value="cheque" {{ request('mode') == 'cheque' ? 'selected' : '' }}>Chèque</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="devise">Devise</label>
                                        <select name="devise" id="devise" class="form-control">
                                            <option value="">Toutes devises</option>
                                            @foreach($stats_devises as $devise)
                                                <option value="{{ $devise->devise }}" {{ request('devise') == $devise->devise ? 'selected' : '' }}>
                                                    {{ $devise->devise }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="date_debut">Période spécifique</label>
                                        <div class="input-group">
                                            <input type="date" name="date_debut" id="date_debut" class="form-control" 
                                                   value="{{ request('date_debut') }}" placeholder="Date début">
                                            <input type="date" name="date_fin" id="date_fin" class="form-control" 
                                                   value="{{ request('date_fin') }}" placeholder="Date fin">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 d-flex justify-content-center">
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fa fa-filter"></i> Appliquer les filtres
                                            </button>
                                            <a href="{{ route('reports.finances') }}" class="btn btn-secondary">
                                                <i class="fa fa-refresh"></i> Réinitialiser
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Statistiques principales DYNAMIQUES -->
                        <div class="box-body">
                            <div class="row mb-4">
                                <div class="col-xl-3 col-md-6 mb-3">
                                    <div class="box bg-primary text-white h-100">
                                        <div class="box-body text-center d-flex flex-column justify-content-between" style="min-height: 140px;">
                                            <div>
                                                <h2 class="mb-0">{{ number_format($stats['total_recettes'] ?? 0, 0, ',', ' ') }} {{ $stats_devise_principale ?? 'CDF' }}</h2>
                                                <p class="mb-0">Recettes Totales</p>
                                            </div>
                                            <small>{{ $stats['total_paiements'] ?? 0 }} paiements</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6 mb-3">
                                    <div class="box bg-success text-white h-100">
                                        <div class="box-body text-center d-flex flex-column justify-content-between" style="min-height: 140px;">
                                            <div>
                                                <h2 class="mb-0">{{ number_format($stats['recettes_aujourdhui'] ?? 0, 0, ',', ' ') }} {{ $stats_devise_principale ?? 'CDF' }}</h2>
                                                <p class="mb-0">Aujourd'hui</p>
                                            </div>
                                            <small>{{ now()->format('d/m/Y') }}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6 mb-3">
                                    <div class="box bg-info text-white h-100">
                                        <div class="box-body text-center d-flex flex-column justify-content-between" style="min-height: 140px;">
                                            <div>
                                                <h2 class="mb-0">{{ number_format($stats['recettes_semaine'] ?? 0, 0, ',', ' ') }} {{ $stats_devise_principale ?? 'CDF' }}</h2>
                                                <p class="mb-0">Cette Semaine</p>
                                            </div>
                                            <small>{{ now()->startOfWeek()->format('d/m') }} - {{ now()->endOfWeek()->format('d/m/Y') }}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6 mb-3">
                                    <div class="box bg-warning text-white h-100">
                                        <div class="box-body text-center d-flex flex-column justify-content-between" style="min-height: 140px;">
                                            <div>
                                                <h2 class="mb-0">{{ number_format($stats['recettes_mois'] ?? 0, 0, ',', ' ') }} {{ $stats_devise_principale ?? 'CDF' }}</h2>
                                                <p class="mb-0">Ce Mois</p>
                                            </div>
                                            <small>{{ now()->format('F Y') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Onglets -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="box">
                                        <div class="box-header">
                                            <ul class="nav nav-tabs" role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link active" data-bs-toggle="tab" href="#modes">
                                                        <i class="fa fa-credit-card"></i> Modes de Paiement
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-bs-toggle="tab" href="#emplacements">
                                                        <i class="fa fa-store"></i> Par Emplacement
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-bs-toggle="tab" href="#caissiers">
                                                        <i class="fa fa-user"></i> Performance Caissiers
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-bs-toggle="tab" href="#graphiques">
                                                        <i class="fa fa-chart-bar"></i> Graphiques
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-bs-toggle="tab" href="#details">
                                                        <i class="fa fa-list"></i> Détail des Paiements
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="box-body">
                                            <div class="tab-content">
                                                <!-- Tab Modes de Paiement -->
                                                <div class="tab-pane fade show active" id="modes" role="tabpanel">
                                                    <div class="row">
                                                        @foreach($stats_modes as $stat)
                                                        @php
                                                            $total_recettes = $stats['total_recettes'] ?? 0;
                                                            $pourcentage = $total_recettes > 0 ? ($stat->total / $total_recettes) * 100 : 0;
                                                            $devise_stat = $stat->devise ?? $stats_devise_principale ?? 'CDF';
                                                        @endphp
                                                        <div class="col-md-4 mb-3">
                                                            <div class="card border-0 shadow-sm ">
                                                                <div class="card-body text-center d-flex flex-column justify-content-between">
                                                                    <div>
                                                                        <h5 class="card-title text-uppercase text-primary">
                                                                            @if($stat->mode == 'cash') Espèces
                                                                            @elseif($stat->mode == 'card') Carte
                                                                            @elseif($stat->mode == 'mobile') Mobile
                                                                            @elseif($stat->mode == 'virement') Virement
                                                                            @elseif($stat->mode == 'cheque') Chèque
                                                                            @else {{ $stat->mode }}
                                                                            @endif
                                                                        </h5>
                                                                        <h3 class="text-success">{{ number_format($stat->total, 0, ',', ' ') }} {{ $devise_stat }}</h3>
                                                                        <p class="text-muted">{{ $stat->nombre }} transactions</p>
                                                                    </div>
                                                                    <div class="progress">
                                                                        <div class="progress-bar bg-success" style="width: {{ $pourcentage }}%">
                                                                            {{ number_format($pourcentage, 1) }}%
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                    @if($stats_modes->count() == 0)
                                                    <div class="alert alert-info text-center">
                                                        <i class="fa fa-info-circle"></i> Aucun paiement trouvé avec les critères sélectionnés.
                                                    </div>
                                                    @endif
                                                </div>

                                                <!-- Tab Par Emplacement -->
                                                <div class="tab-pane fade" id="emplacements" role="tabpanel">
                                                    @if($stats_emplacements->count() > 0)
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-hover">
                                                            <thead>
                                                                <tr class="bg-light">
                                                                    <th>Emplacement</th>
                                                                    <th class="text-center">Nombre de Paiements</th>
                                                                    <th class="text-end">Montant Total</th>
                                                                    <th class="text-center">Part du Total</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($stats_emplacements as $stat)
                                                                    @php
                                                                        $total_recettes = $stats['total_recettes'] ?? 0;
                                                                        $pourcentage = $total_recettes > 0 ? ($stat->total / $total_recettes) * 100 : 0;
                                                                        $devise_stat = $stat->devise ?? $stats_devise_principale ?? 'CDF';
                                                                    @endphp
                                                                    <tr>
                                                                        <td>
                                                                            <strong>{{ $stat->libelle }}</strong>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <span class="badge bg-primary">{{ $stat->nombre }}</span>
                                                                        </td>
                                                                        <td class="text-end">
                                                                            <strong class="text-success">{{ number_format($stat->total, 0, ',', ' ') }} {{ $devise_stat }}</strong>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <span class="badge bg-info">{{ number_format($pourcentage, 1) }}%</span>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    @else
                                                    <div class="alert alert-info text-center">
                                                        <i class="fa fa-info-circle"></i> Aucun emplacement avec des paiements trouvé.
                                                    </div>
                                                    @endif
                                                </div>

                                                <!-- Tab Performance Caissiers -->
                                                <div class="tab-pane fade" id="caissiers" role="tabpanel">
                                                    @if($stats_caissiers->count() > 0)
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-hover">
                                                            <thead>
                                                                <tr class="bg-light">
                                                                    <th>Caissier</th>
                                                                    <th class="text-center">Nombre de Paiements</th>
                                                                    <th class="text-end">Montant Total</th>
                                                                    <th class="text-center">Moyenne par Paiement</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($stats_caissiers as $stat)
                                                                    @php
                                                                        $moyenne = ($stat->nombre > 0 && $stat->total > 0) ? $stat->total / $stat->nombre : 0;
                                                                        $devise_stat = $stat->devise ?? $stats_devise_principale ?? 'CDF';
                                                                    @endphp
                                                                    <tr>
                                                                        <td>
                                                                            <strong>{{ $stat->name ?? 'N/A' }}</strong>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <span class="badge bg-primary">{{ $stat->nombre }}</span>
                                                                        </td>
                                                                        <td class="text-end">
                                                                            <strong class="text-success">{{ number_format($stat->total, 0, ',', ' ') }} {{ $devise_stat }}</strong>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <span class="badge bg-info">{{ number_format($moyenne, 0, ',', ' ') }} {{ $devise_stat }}</span>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    @else
                                                    <div class="alert alert-info text-center">
                                                        <i class="fa fa-info-circle"></i> Aucun caissier trouvé avec des paiements.
                                                    </div>
                                                    @endif
                                                </div>

                                                <!-- Tab Graphiques -->
                                                <div class="tab-pane fade" id="graphiques" role="tabpanel">
                                                    <div class="row">
                                                        <!-- Graphique 1: Répartition par mode de paiement -->
                                                        <div class="col-md-6 mb-4">
                                                            <div class="card ">
                                                                <div class="card-header bg-primary text-white">
                                                                    <h5 class="card-title mb-0">
                                                                        <i class="fa fa-pie-chart"></i> Modes de Paiement ({{ $stats_devise_principale ?? 'CDF' }})
                                                                    </h5>
                                                                </div>
                                                                <div class="card-body">
                                                                    <canvas id="chartModesPaiement" height="250"></canvas>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Graphique 2: Répartition par devise -->
                                                        <div class="col-md-6 mb-4">
                                                            <div class="card ">
                                                                <div class="card-header bg-success text-white">
                                                                    <h5 class="card-title mb-0">
                                                                        <i class="fa fa-money"></i> Répartition par Devise
                                                                    </h5>
                                                                </div>
                                                                <div class="card-body">
                                                                    <canvas id="chartDevises" height="250"></canvas>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Graphique 3: Performance par emplacement -->
                                                        <div class="col-md-6 mb-4">
                                                            <div class="card">
                                                                <div class="card-header bg-info text-white">
                                                                    <h5 class="card-title mb-0">
                                                                        <i class="fa fa-bar-chart"></i> Recettes par Emplacement ({{ $stats_devise_principale ?? 'CDF' }})
                                                                    </h5>
                                                                </div>
                                                                <div class="card-body">
                                                                    <canvas id="chartEmplacements" height="250"></canvas>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Graphique 4: Performance des caissiers -->
                                                        <div class="col-md-6 mb-4">
                                                            <div class="card">
                                                                <div class="card-header bg-warning text-white">
                                                                    <h5 class="card-title mb-0">
                                                                        <i class="fa fa-users"></i> Top Caissiers ({{ $stats_devise_principale ?? 'CDF' }})
                                                                    </h5>
                                                                </div>
                                                                <div class="card-body">
                                                                    <canvas id="chartCaissiers" height="250"></canvas>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Graphique 5: Évolution mensuelle -->
                                                        <div class="col-12 mb-4">
                                                            <div class="card">
                                                                <div class="card-header bg-danger text-white">
                                                                    <h5 class="card-title mb-0">
                                                                        <i class="fa fa-line-chart"></i> Évolution des Recettes ({{ $stats_devise_principale ?? 'CDF' }})
                                                                    </h5>
                                                                </div>
                                                                <div class="card-body">
                                                                    <div id="chartEvolutionContainer">
                                                                        <canvas id="chartEvolutionMensuelle" height="120"></canvas>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Tab Détail des Paiements -->
                                                <div class="tab-pane fade" id="details" role="tabpanel">
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <h5>Détail des transactions</h5>
                                                        <button class="btn btn-sm btn-info" onclick="exporterExcel()">
                                                            <i class="fa fa-download"></i> Exporter
                                                        </button>
                                                    </div>
                                                    @if($paiements->count() > 0)
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered table-hover" id="table-paiements">
                                                                <thead>
                                                                    <tr class="bg-light">
                                                                        <th>Date/Heure</th>
                                                                        <th>Facture</th>
                                                                        <th>Emplacement</th>
                                                                        <th>Mode</th>
                                                                        <th class="text-end">Montant</th>
                                                                        <th>Devise</th>
                                                                        <th>Caissier</th>
                                                                        <th>Actions</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($paiements as $paiement)
                                                                        @php
                                                                            $devise_paiement = $paiement->devise ?? $paiement->facture->devise ?? 'CDF';
                                                                        @endphp
                                                                        <tr class="payment-row" data-payment-id="{{ $paiement->id }}" style="cursor: pointer;">
                                                                            <td>
                                                                                {{ \Carbon\Carbon::parse($paiement->pay_date)->format('d/m/Y H:i') }}
                                                                            </td>
                                                                            <td>
                                                                                @if($paiement->facture)
                                                                                    <strong>{{ $paiement->facture->numero_facture ?? 'N/A' }}</strong>
                                                                                @else
                                                                                    <span class="text-muted">-</span>
                                                                                @endif
                                                                            </td>
                                                                            <td>
                                                                                <span class="badge bg-secondary">{{ $paiement->emplacement->libelle ?? 'N/A' }}</span>
                                                                            </td>
                                                                            <td>
                                                                                <span class="badge 
                                                                                    @if($paiement->mode == 'cash') bg-success
                                                                                    @elseif($paiement->mode == 'card') bg-primary
                                                                                    @elseif($paiement->mode == 'mobile') bg-info
                                                                                    @else bg-warning @endif">
                                                                                    @if($paiement->mode == 'cash') Espèces
                                                                                    @elseif($paiement->mode == 'card') Carte
                                                                                    @elseif($paiement->mode == 'mobile') Mobile
                                                                                    @elseif($paiement->mode == 'virement') Virement
                                                                                    @elseif($paiement->mode == 'cheque') Chèque
                                                                                    @else {{ $paiement->mode }}
                                                                                    @endif
                                                                                </span>
                                                                            </td>
                                                                            <td class="text-end">
                                                                                <strong class="text-success">{{ number_format($paiement->amount, 0, ',', ' ') }}</strong>
                                                                            </td>
                                                                            <td class="text-center">
                                                                                <span class="badge bg-dark text-white">{{ $devise_paiement }}</span>
                                                                            </td>
                                                                            <td>
                                                                                <small>{{ $paiement->user->name ?? 'N/A' }}</small>
                                                                            </td>
                                                                            <td class="text-center">
                                                                                <button class="btn btn-sm btn-outline-primary view-payment" data-payment-id="{{ $paiement->id }}">
                                                                                    <i class="fa fa-eye"></i>
                                                                                </button>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        
                                                    @else
                                                        <div class="alert alert-info text-center">
                                                            <i class="fa fa-info-circle"></i> Aucun paiement trouvé avec les critères sélectionnés.
                                                        </div>
                                                    @endif
                                                </div>
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
    </div>
</div>

<!-- Modal pour les détails du paiement -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="paymentModalLabel">Détails du Paiement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="paymentDetails">
                <!-- Les détails seront chargés ici -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Variables globales pour les graphiques
let chartModesPaiement, chartDevises, chartEmplacements, chartCaissiers, chartEvolutionMensuelle;

// Fonction pour exporter en Excel
function exporterExcel() {
    const table = document.getElementById('table-paiements');
    if (!table) {
        alert('Aucune donnée à exporter');
        return;
    }
    const html = table.outerHTML;
    const url = 'data:application/vnd.ms-excel;charset=utf-8,' + encodeURIComponent(html);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'rapport_finances_' + new Date().toISOString().split('T')[0] + '.xls';
    link.click();
}

// Fonction pour afficher les détails du paiement
function showPaymentDetails(paymentId) {
    fetch(`/reports/payment-details/${paymentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const payment = data.payment;
                const modalBody = document.getElementById('paymentDetails');
                
                // Récupérer la devise depuis le paiement ou la facture, avec CDF par défaut
                const devisePaiement = payment.devise || (payment.facture && payment.facture.devise) || 'CDF';
                
                let detailsHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Informations du Paiement</h6>
                            <table class="table table-bordered">
                                <tr>
                                    <td><strong>Date:</strong></td>
                                    <td>${new Date(payment.pay_date).toLocaleString('fr-FR')}</td>
                                </tr>
                                <tr>
                                    <td><strong>Montant:</strong></td>
                                    <td>${payment.amount.toLocaleString('fr-FR')} ${devisePaiement}</td>
                                </tr>
                                <tr>
                                    <td><strong>Mode:</strong></td>
                                    <td><span class="badge bg-primary">${payment.mode}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Référence:</strong></td>
                                    <td>${payment.mode_ref || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Caissier:</strong></td>
                                    <td>${payment.user?.name || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Emplacement:</strong></td>
                                    <td>${payment.emplacement?.libelle || 'N/A'}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Informations de la Facture</h6>
                `;

                if (payment.facture) {
                    // Récupérer la devise de la facture avec CDF par défaut
                    const deviseFacture = payment.facture.devise || 'CDF';
                    
                    detailsHTML += `
                        <table class="table table-bordered">
                            <tr>
                                <td><strong>N° Facture:</strong></td>
                                <td>${payment.facture.numero_facture}</td>
                            </tr>
                            <tr>
                                <td><strong>Date Facture:</strong></td>
                                <td>${new Date(payment.facture.date_facture).toLocaleDateString('fr-FR')}</td>
                            </tr>
                            <tr>
                                <td><strong>Total TTC:</strong></td>
                                <td>${payment.facture.total_ttc?.toLocaleString('fr-FR') || 'N/A'} ${deviseFacture}</td>
                            </tr>
                            <tr>
                                <td><strong>Statut:</strong></td>
                                <td><span class="badge ${payment.facture.statut === 'payée' ? 'bg-success' : 'bg-warning'}">${payment.facture.statut}</span></td>
                            </tr>
                        </table>
                    `;
                } else {
                    detailsHTML += `<p class="text-muted">Aucune facture associée</p>`;
                }

                detailsHTML += `
                        </div>
                    </div>
                `;

                // Détails des articles si la facture existe et a des détails
                if (payment.facture && payment.facture.details && payment.facture.details.length > 0) {
                    const deviseFacture = payment.facture.devise || 'CDF';
                    
                    detailsHTML += `
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6>Articles de la Facture</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr class="bg-light">
                                                <th>Article</th>
                                                <th class="text-end">Prix Unitaire</th>
                                                <th class="text-center">Quantité</th>
                                                <th class="text-end">Sous-Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                    `;

                    payment.facture.details.forEach(detail => {
                        // Utiliser le nom du produit depuis la relation produit
                        const nomProduit = detail.produit?.nom || detail.libelle || 'Article';
                        detailsHTML += `
                            <tr>
                                <td>${nomProduit}</td>
                                <td class="text-end">${detail.prix_unitaire?.toLocaleString('fr-FR') || '0'} ${deviseFacture}</td>
                                <td class="text-center">${detail.quantite || '0'}</td>
                                <td class="text-end">${detail.total?.toLocaleString('fr-FR') || '0'} ${deviseFacture}</td>
                            </tr>
                        `;
                    });

                    detailsHTML += `
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-light">
                                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                                <td class="text-end"><strong>${payment.facture.total_ttc?.toLocaleString('fr-FR') || '0'} ${deviseFacture}</strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    `;
                }

                modalBody.innerHTML = detailsHTML;
                const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
                modal.show();
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors du chargement des détails');
        });
}

// Initialisation des événements
document.addEventListener('DOMContentLoaded', function() {
    // Clic sur une ligne du tableau
    document.querySelectorAll('.payment-row').forEach(row => {
        row.addEventListener('click', function() {
            const paymentId = this.getAttribute('data-payment-id');
            showPaymentDetails(paymentId);
        });
    });

    // Clic sur le bouton voir
    document.querySelectorAll('.view-payment').forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            const paymentId = this.getAttribute('data-payment-id');
            showPaymentDetails(paymentId);
        });
    });

    // Auto-submit des filtres principaux
    const filtresAuto = ['emplacement_id', 'caissier_id', 'mode', 'devise'];
    
    filtresAuto.forEach(filtre => {
        const element = document.getElementById(filtre);
        if (element) {
            element.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        }
    });

    // Gestion des dates
    const dateDebut = document.getElementById('date_debut');
    const dateFin = document.getElementById('date_fin');
    
    if (dateDebut) {
        dateDebut.addEventListener('change', function() {
            if (this.value && dateFin.value && new Date(this.value) > new Date(dateFin.value)) {
                dateFin.value = this.value;
            }
        });
    }
    
    if (dateFin) {
        dateFin.addEventListener('change', function() {
            if (this.value && dateDebut.value && new Date(this.value) < new Date(dateDebut.value)) {
                dateDebut.value = this.value;
            }
        });
    }

    // Initialiser les graphiques si l'onglet graphiques est actif
    const graphiquesTab = document.querySelector('a[href="#graphiques"]');
    if (graphiquesTab) {
        graphiquesTab.addEventListener('shown.bs.tab', function() {
            setTimeout(initialiserGraphiques, 100);
        });
    }
    
    // Initialiser les graphiques immédiatement si l'onglet est actif
    if (document.getElementById('graphiques').classList.contains('active')) {
        setTimeout(initialiserGraphiques, 500);
    }
});

// Fonction d'initialisation des graphiques
function initialiserGraphiques() {
    const graphiqueModes = @json($graphique_modes ?? []);
    const graphiqueEmplacements = @json($graphique_emplacements ?? []);
    const graphiqueDevises = @json($graphique_devises ?? []);
    const evolutionMensuelle = @json($evolution_mensuelle ?? []);
    const statsCaissiers = @json($stats_caissiers ?? []);
    const devisePrincipale = @json($stats_devise_principale ?? 'CDF');

    console.log('Données évolution mensuelle:', evolutionMensuelle);

    // Palette de couleurs améliorée
    const paletteCouleurs = [
        '#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6',
        '#1abc9c', '#d35400', '#c0392b', '#16a085', '#8e44ad'
    ];

    // Fonction pour traduire les modes de paiement
    function traduireMode(mode) {
        const traductions = {
            'cash': 'Espèces',
            'card': 'Carte',
            'mobile': 'Mobile',
            'virement': 'Virement',
            'cheque': 'Chèque'
        };
        return traductions[mode] || mode;
    }

    // Graphique modes de paiement
    const ctxModes = document.getElementById('chartModesPaiement');
    if (ctxModes && graphiqueModes.length > 0) {
        if (chartModesPaiement) chartModesPaiement.destroy();
        
        chartModesPaiement = new Chart(ctxModes, {
            type: 'doughnut',
            data: {
                labels: graphiqueModes.map(item => traduireMode(item.mode)),
                datasets: [{
                    data: graphiqueModes.map(item => item.total),
                    backgroundColor: paletteCouleurs,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.raw / total) * 100).toFixed(1);
                                return `${context.label}: ${context.raw.toLocaleString()} ${devisePrincipale} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    } else {
        console.warn('Aucune donnée pour le graphique des modes de paiement');
    }

    // Graphique devises
    const ctxDevises = document.getElementById('chartDevises');
    if (ctxDevises && graphiqueDevises.length > 0) {
        if (chartDevises) chartDevises.destroy();
        
        chartDevises = new Chart(ctxDevises, {
            type: 'pie',
            data: {
                labels: graphiqueDevises.map(item => item.devise),
                datasets: [{
                    data: graphiqueDevises.map(item => item.total),
                    backgroundColor: paletteCouleurs.slice(2),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    } else {
        console.warn('Aucune donnée pour le graphique des devises');
    }

    // Graphique emplacements
    const ctxEmplacements = document.getElementById('chartEmplacements');
    if (ctxEmplacements && graphiqueEmplacements.length > 0) {
        if (chartEmplacements) chartEmplacements.destroy();
        
        chartEmplacements = new Chart(ctxEmplacements, {
            type: 'bar',
            data: {
                labels: graphiqueEmplacements.map(item => item.libelle),
                datasets: [{
                    label: `Recettes (${devisePrincipale})`,
                    data: graphiqueEmplacements.map(item => item.total),
                    backgroundColor: '#3498db',
                    borderColor: '#2980b9',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + ' ' + devisePrincipale;
                            }
                        }
                    }
                }
            }
        });
    } else {
        console.warn('Aucune donnée pour le graphique des emplacements');
    }

    // Graphique caissiers
    const ctxCaissiers = document.getElementById('chartCaissiers');
    if (ctxCaissiers && statsCaissiers.length > 0) {
        if (chartCaissiers) chartCaissiers.destroy();
        
        // Prendre les 10 premiers caissiers par montant total
        const topCaissiers = statsCaissiers.slice(0, 10);
        
        chartCaissiers = new Chart(ctxCaissiers, {
            type: 'bar',
            data: {
                labels: topCaissiers.map(item => item.name || 'N/A'),
                datasets: [{
                    label: `Recettes (${devisePrincipale})`,
                    data: topCaissiers.map(item => item.total),
                    backgroundColor: '#f39c12',
                    borderColor: '#e67e22',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + ' ' + devisePrincipale;
                            }
                        }
                    }
                }
            }
        });
    } else {
        console.warn('Aucune donnée pour le graphique des caissiers');
    }

    // Graphique évolution mensuelle
    const ctxEvolution = document.getElementById('chartEvolutionMensuelle');
    if (ctxEvolution && evolutionMensuelle && evolutionMensuelle.length > 0) {
        if (chartEvolutionMensuelle) chartEvolutionMensuelle.destroy();
        
        chartEvolutionMensuelle = new Chart(ctxEvolution, {
            type: 'line',
            data: {
                labels: evolutionMensuelle.map(item => item.mois_nom),
                datasets: [{
                    label: `Recettes (${devisePrincipale})`,
                    data: evolutionMensuelle.map(item => item.total),
                    borderColor: '#e74c3c',
                    backgroundColor: 'rgba(231, 76, 60, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + ' ' + devisePrincipale;
                            }
                        }
                    }
                }
            }
        });
    } else {
        console.warn('Aucune donnée pour le graphique d\'évolution mensuelle');
        // Afficher un message si pas de données
        if (ctxEvolution) {
            ctxEvolution.innerHTML = '<div class="text-center p-4 text-muted">Aucune donnée disponible pour l\'évolution mensuelle</div>';
        }
    }
}
</script>

<style>
.payment-row:hover {
    background-color: #f8f9fa !important;
    transform: translateY(-1px);
    transition: all 0.2s ease;
}
.card {
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.progress {
    height: 20px;
}
.progress-bar {
    font-size: 12px;
    line-height: 20px;
}
.nav-tabs .nav-link.active {
    font-weight: bold;
    border-bottom: 3px solid #3498db;
}
.table th {
    background-color: #f8f9fa !important;
    font-weight: 600;
}
</style>
@endpush