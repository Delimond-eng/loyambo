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
                            <h4 class="box-title">Performance du personnel</h4>
                            <p class="text-muted mb-0">Suivi des serveurs et caissiers par periode, service et emplacement.</p>
                        </div>

                        <div class="box-body">
<div class="row mb-4">
                                <div class="col-md-3">
                                    <label for="date_debut">Date debut</label>
                                    <input type="date" id="date_debut" class="form-control" value="{{ request('date_debut') }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="date_fin">Date fin</label>
                                    <input type="date" id="date_fin" class="form-control" value="{{ request('date_fin') }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="service_type">Service</label>
                                    <select id="service_type" class="form-control">
                                        <option value="">Tous les services</option>
                                        @foreach($serviceTypes as $type)
                                            <option value="{{ $type }}" {{ request('service_type') == $type ? 'selected' : '' }}>
                                                {{ $type === 'restaurant & lounge' ? 'Restaurant & Lounge' : ucfirst($type) }}
                                            </option>
                                        @endforeach
                                    </select>
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
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <label for="role">Role</label>
                                    <select id="role" class="form-control">
                                        <option value="">Tous les roles</option>
                                        <option value="serveur" {{ request('role') == 'serveur' ? 'selected' : '' }}>Serveur</option>
                                        <option value="caissier" {{ request('role') == 'caissier' ? 'selected' : '' }}>Caissier</option>
                                        <option value="manager" {{ request('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                </div>
                                <div class="col-md-9 d-flex align-items-end justify-content-center">
                                    <button class="btn btn-primary me-2" id="appliquerFiltres">
                                        <i class="fa fa-filter"></i> Appliquer les filtres
                                    </button>
                                    <button class="btn btn-secondary me-2" id="resetFiltres">
                                        <i class="fa fa-refresh"></i> Reinitialiser
                                    </button>
                                    <a href="{{ route('reports.performance.export.pdf', request()->query()) }}" class="btn btn-outline-danger me-2">
                                        <i class="fa fa-file-pdf"></i> Export PDF
                                    </a>
                                    <a href="{{ route('reports.performance.export.excel', request()->query()) }}" class="btn btn-outline-success">
                                        <i class="fa fa-file-excel"></i> Export Excel
                                    </a>
                                </div>
                            </div>

                            @php
                                $panierMoyenGlobal = $totalCommandes > 0 ? $totalEncaissement / $totalCommandes : 0;
                            @endphp
                            <div class="row mb-4">
                                <div class="col-xl-3 col-md-6">
                                    <div class="box bg-primary text-white">
                                        <div class="box-body text-center">
                                            <h2 class="mb-0">{{ $totalEmployes }}</h2>
                                            <p class="mb-0">Employes analyses</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="box bg-success text-white">
                                        <div class="box-body text-center">
                                            <h2 class="mb-0">{{ $totalCommandes }}</h2>
                                            <p class="mb-0">Operations totales</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="box bg-info text-white">
                                        <div class="box-body text-center">
                                            <h2 class="mb-0">{{ number_format($totalEncaissement, 0, ',', ' ') }}</h2>
                                            <p class="mb-0">Total encaisse (CDF)</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="box bg-warning text-white">
                                        <div class="box-body text-center">
                                            <h2 class="mb-0">{{ number_format($panierMoyenGlobal, 0, ',', ' ') }}</h2>
                                            <p class="mb-0">Panier moyen (CDF)</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Employe</th>
                                                    <th>Role</th>
                                                    <th>Emplacement</th>
                                                    <th>Type d'activite</th>
                                                    <th>Statut</th>
                                                    <th>Operations</th>
                                                    <th>Total encaisse</th>
                                                    <th>Panier moyen</th>
                                                    <th>Performance</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($performances as $performance)
                                                    @php
                                                        $pourcentagePerformance = $performance['pourcentage_performance'];
                                                        $classPerformance = $pourcentagePerformance >= 80 ? 'success' : ($pourcentagePerformance >= 50 ? 'warning' : 'danger');
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $performance['employe']->name }}</strong>
                                                            <br>
                                                            <small class="text-muted">{{ $performance['employe']->email }}</small>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-info">{{ ucfirst($performance['employe']->role) }}</span>
                                                        </td>
                                                        <td>
                                                            {{ $performance['employe']->emplacement?->libelle ?? '-' }}
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-light">{{ $performance['type_activite'] }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            @if($performance['est_actif'])
                                                                <span class="badge badge-success">Actif</span>
                                                            @else
                                                                <span class="badge badge-secondary">Inactif</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-primary">{{ $performance['nombre_commandes'] }}</span>
                                                        </td>
                                                        <td class="text-end">
                                                            <strong class="text-success">
                                                                {{ number_format($performance['total_encaissement'], 0, ',', ' ') }}
                                                                {{ $performance['devise_principale'] }}
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
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="9" class="text-center text-muted py-4">Aucune donnee pour les filtres selectionnes.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            @php
                                $topServeurs = collect($performances)
                                    ->filter(fn($p) => ($p['employe']->role ?? null) === 'serveur')
                                    ->sortByDesc('total_encaissement')
                                    ->take(5);
                                $topCaissiers = collect($performances)
                                    ->filter(fn($p) => ($p['employe']->role ?? null) === 'caissier')
                                    ->sortByDesc('total_encaissement')
                                    ->take(5);
                            @endphp
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="box">
                                        <div class="box-header">
                                            <h4 class="box-title">Top 5 serveurs</h4>
                                        </div>
                                        <div class="box-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Nom</th>
                                                            <th>Operations</th>
                                                            <th>CA</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($topServeurs as $index => $performance)
                                                            <tr>
                                                                <td class="text-center"><span class="badge badge-primary">{{ $index + 1 }}</span></td>
                                                                <td>{{ $performance['employe']->name }}</td>
                                                                <td class="text-center">{{ $performance['nombre_commandes'] }}</td>
                                                                <td class="text-end text-success">{{ number_format($performance['total_encaissement'], 0, ',', ' ') }} {{ $performance['devise_principale'] }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="4" class="text-center text-muted">Aucune donnee serveur.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="box">
                                        <div class="box-header">
                                            <h4 class="box-title">Top 5 caissiers</h4>
                                        </div>
                                        <div class="box-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Nom</th>
                                                            <th>Transactions</th>
                                                            <th>Encaissement</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($topCaissiers as $index => $performance)
                                                            <tr>
                                                                <td class="text-center"><span class="badge badge-success">{{ $index + 1 }}</span></td>
                                                                <td>{{ $performance['employe']->name }}</td>
                                                                <td class="text-center">{{ $performance['nombre_commandes'] }}</td>
                                                                <td class="text-end text-success">{{ number_format($performance['total_encaissement'], 0, ',', ' ') }} {{ $performance['devise_principale'] }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="4" class="text-center text-muted">Aucune donnee caissier.</td>
                                                            </tr>
                                                        @endforelse
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
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateDebut = document.getElementById('date_debut');
    const dateFin = document.getElementById('date_fin');
    const serviceTypeSelect = document.getElementById('service_type');
    const emplacementSelect = document.getElementById('emplacement_id');
    const roleSelect = document.getElementById('role');
    const appliquerFiltres = document.getElementById('appliquerFiltres');
    const resetFiltres = document.getElementById('resetFiltres');

    function appliquerFiltresPerformance() {
        const params = new URLSearchParams();
        if (dateDebut && dateDebut.value) params.append('date_debut', dateDebut.value);
        if (dateFin && dateFin.value) params.append('date_fin', dateFin.value);
        if (serviceTypeSelect && serviceTypeSelect.value) params.append('service_type', serviceTypeSelect.value);
        if (emplacementSelect && emplacementSelect.value) params.append('emplacement_id', emplacementSelect.value);
        if (roleSelect && roleSelect.value) params.append('role', roleSelect.value);

        const baseUrl = '{{ route("reports.performance") }}';
        const query = params.toString();
        window.location.href = query ? `${baseUrl}?${query}` : baseUrl;
    }

    if (appliquerFiltres) {
        appliquerFiltres.addEventListener('click', appliquerFiltresPerformance);
    }

    if (resetFiltres) {
        resetFiltres.addEventListener('click', function() {
            if (dateDebut) dateDebut.value = '';
            if (dateFin) dateFin.value = '';
            if (serviceTypeSelect) serviceTypeSelect.value = '';
            if (emplacementSelect) emplacementSelect.value = '';
            if (roleSelect) roleSelect.value = '';
            window.location.href = '{{ route("reports.performance") }}';
        });
    }
});
</script>
@endpush
