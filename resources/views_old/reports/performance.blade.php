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
                        <div class="box-header with-border p-5 text-center">
                            <h4 class="box-title">Performance des ventes des employés</h4>
                            <p class="text-muted">Voir les performances de vente de tous les employés</p>
                        </div>

                        <!-- Filtres -->
                        <div class="box-body">
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <label for="date_debut">Date début</label>
                                    <input type="date" id="date_debut" class="form-control" value="{{ request('date_debut') }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="date_fin">Date fin</label>
                                    <input type="date" id="date_fin" class="form-control" value="{{ request('date_fin') }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="emplacement_id">Emplacement</label>
                                    <select id="emplacement_id" class="form-control">
                                        <option value="">Tous les emplacements</option>
                                        @foreach($emplacements as $emp)
                                            <option value="{{ $emp->id }}" {{ request('emplacement_id') == $emp->id ? 'selected' : '' }}>
                                                {{ $emp->libelle }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="role">Rôle</label>
                                    <select id="role" class="form-control">
                                        <option value="">Tous les rôles</option>
                                        <option value="serveur" {{ request('role') == 'serveur' ? 'selected' : '' }}>Serveur</option>
                                        <option value="caissier" {{ request('role') == 'caissier' ? 'selected' : '' }}>Caissier</option>
                                        <option value="manager" {{ request('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-12 text-center">
                                    <button class="btn btn-primary" id="appliquerFiltres">
                                        <i class="fa fa-filter"></i> Appliquer les filtres
                                    </button>
                                    <button class="btn btn-secondary" id="resetFiltres">
                                        <i class="fa fa-refresh"></i> Réinitialiser
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Statistiques globales -->
                        <div class="box-body">
                            <div class="row mb-4">
                                <div class="col-xl-3 col-md-6">
                                    <div class="box bg-primary text-white">
                                        <div class="box-body text-center">
                                            <h2 class="mb-0">{{ $totalEmployes }}</h2>
                                            <p class="mb-0">Total Employés</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="box bg-success text-white">
                                        <div class="box-body text-center">
                                            <h2 class="mb-0">{{ $totalCommandes }}</h2>
                                            <p class="mb-0">Commandes Total</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="box bg-info text-white">
                                        <div class="box-body text-center">
                                            <h2 class="mb-0">{{ number_format($totalEncaissement, 0, ',', ' ') }}</h2>
                                            <p class="mb-0">Total Encaissé (CDF)</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="box bg-warning text-white">
                                        <div class="box-body text-center">
                                            @php
                                                $panierMoyenGlobal = $totalCommandes > 0 ? $totalEncaissement / $totalCommandes : 0;
                                            @endphp
                                            <h2 class="mb-0">{{ number_format($panierMoyenGlobal, 0, ',', ' ') }}</h2>
                                            <p class="mb-0">Panier Moyen (CDF)</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tableau des performances -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="box">
                                        <div class="box-header">
                                            <h4 class="box-title">Performance détaillée par employé</h4>
                                            <div class="box-tools">
                                                <span class="badge badge-success">Actif</span>
                                                <span class="badge badge-secondary ms-1">Inactif</span>
                                            </div>
                                        </div>
                                        <div class="box-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Employé</th>
                                                            <th>Rôle</th>
                                                            <th>Emplacement</th>
                                                            <th>Statut</th>
                                                            <th>Commandes Servies</th>
                                                            <th>Total Encaissé</th>
                                                            <th>Panier Moyen</th>
                                                            <th>Performance</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($performances as $performance)
                                                            @php
                                                                $pourcentagePerformance = $performance['pourcentage_performance'];
                                                                $classPerformance = '';
                                                                if ($pourcentagePerformance >= 80) {
                                                                    $classPerformance = 'success';
                                                                } elseif ($pourcentagePerformance >= 50) {
                                                                    $classPerformance = 'warning';
                                                                } else {
                                                                    $classPerformance = 'danger';
                                                                }
                                                            @endphp
                                                            <tr>
                                                                <td>
                                                                    <strong>{{ $performance['employe']->name }}</strong>
                                                                    <br>
                                                                    <small class="text-muted">{{ $performance['employe']->email }}</small>
                                                                </td>
                                                                <td>
                                                                    <span class="badge badge-info">{{ $performance['employe']->role }}</span>
                                                                </td>
                                                                <td>
                                                                    @if($performance['employe']->emplacement)
                                                                        {{ $performance['employe']->emplacement->libelle }}
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-center">
                                                                    @if($performance['est_actif'])
                                                                        <span class="badge badge-success">Actif</span>
                                                                    @else
                                                                        <span class="badge badge-secondary">Inactif</span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-center">
                                                                    <span class="badge badge-primary" style="font-size: 14px;">
                                                                        {{ $performance['nombre_commandes'] }}
                                                                    </span>
                                                                </td>
                                                                <td class="text-end">
                                                                    <strong class="text-success">
                                                                        {{ number_format($performance['total_encaissement'], 0, ',', ' ') }} 
                                                                        <span class="text-muted" style="font-size: 12px;">{{ $performance['devise_principale'] }}</span>
                                                                    </strong>
                                                                </td>
                                                                <td class="text-end">
                                                                    {{ number_format($performance['panier_moyen'], 0, ',', ' ') }} {{ $performance['devise_principale'] }}
                                                                </td>
                                                                <td class="text-center">
                                                                    <div class="progress" style="height: 20px;">
                                                                        <div class="progress-bar bg-{{ $classPerformance }}" 
                                                                             role="progressbar" 
                                                                             style="width: {{ $pourcentagePerformance }}%"
                                                                             aria-valuenow="{{ $pourcentagePerformance }}" 
                                                                             aria-valuemin="0" 
                                                                             aria-valuemax="100">
                                                                            {{ number_format($pourcentagePerformance, 1) }}%
                                                                        </div>
                                                                    </div>
                                                                    <small class="text-muted">
                                                                        Basé sur le CA moyen
                                                                    </small>
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

                            <!-- Classement des meilleurs performeurs -->
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="box">
                                        <div class="box-header">
                                            <h4 class="box-title">Top 5 - Meilleurs Serveurs</h4>
                                        </div>
                                        <div class="box-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Serveur</th>
                                                            <th>Statut</th>
                                                            <th>Commandes</th>
                                                            <th>CA (CDF)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $serveurs = collect($performances)
                                                                ->filter(function($performance) {
                                                                    return $performance['employe']->role === 'serveur';
                                                                })
                                                                ->sortByDesc('total_encaissement')
                                                                ->take(5);
                                                        @endphp
                                                        @foreach($serveurs as $index => $performance)
                                                            <tr>
                                                                <td class="text-center">
                                                                    <span class="badge badge-primary">{{ $index + 1 }}</span>
                                                                </td>
                                                                <td>{{ $performance['employe']->name }}</td>
                                                                <td class="text-center">
                                                                    @if($performance['est_actif'])
                                                                        <span class="badge badge-success">Actif</span>
                                                                    @else
                                                                        <span class="badge badge-secondary">Inactif</span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-center">{{ $performance['nombre_commandes'] }}</td>
                                                                <td class="text-end text-success">
                                                                    {{ number_format($performance['total_encaissement'], 0, ',', ' ') }} CDF
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
                                            <h4 class="box-title">Top 5 - Meilleurs Caissiers</h4>
                                        </div>
                                        <div class="box-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Caissier</th>
                                                            <th>Statut</th>
                                                            <th>Transactions</th>
                                                            <th>CA (CDF)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $caissiers = collect($performances)
                                                                ->filter(function($performance) {
                                                                    return $performance['employe']->role === 'caissier';
                                                                })
                                                                ->sortByDesc('total_encaissement')
                                                                ->take(5);
                                                        @endphp
                                                        @foreach($caissiers as $index => $performance)
                                                            <tr>
                                                                <td class="text-center">
                                                                    <span class="badge badge-success">{{ $index + 1 }}</span>
                                                                </td>
                                                                <td>{{ $performance['employe']->name }}</td>
                                                                <td class="text-center">
                                                                    @if($performance['est_actif'])
                                                                        <span class="badge badge-success">Actif</span>
                                                                    @else
                                                                        <span class="badge badge-secondary">Inactif</span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-center">{{ $performance['nombre_commandes'] }}</td>
                                                                <td class="text-end text-success">
                                                                    {{ number_format($performance['total_encaissement'], 0, ',', ' ') }} CDF
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateDebut = document.getElementById('date_debut');
    const dateFin = document.getElementById('date_fin');
    const emplacementSelect = document.getElementById('emplacement_id');
    const roleSelect = document.getElementById('role');
    const appliquerFiltres = document.getElementById('appliquerFiltres');
    const resetFiltres = document.getElementById('resetFiltres');

    // Appliquer les filtres
    appliquerFiltres.addEventListener('click', function() {
        appliquerFiltresPerformance();
    });

    // Réinitialiser les filtres
    resetFiltres.addEventListener('click', function() {
        dateDebut.value = '';
        dateFin.value = '';
        emplacementSelect.value = '';
        roleSelect.value = '';
        appliquerFiltresPerformance();
    });

    function appliquerFiltresPerformance() {
        const params = new URLSearchParams();
        
        if (dateDebut.value) params.append('date_debut', dateDebut.value);
        if (dateFin.value) params.append('date_fin', dateFin.value);
        if (emplacementSelect.value) params.append('emplacement_id', emplacementSelect.value);
        if (roleSelect.value) params.append('role', roleSelect.value);
        
        window.location.href = '{{ route("reports.performance") }}?' + params.toString();
    }
});
</script>

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

.progress {
    border-radius: 10px;
}
</style>
@endpush