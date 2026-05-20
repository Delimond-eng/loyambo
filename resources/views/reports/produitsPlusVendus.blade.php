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
                            <h4 class="box-title">Produits les plus vendus - etape 1/2</h4>
                            <p class="text-muted mb-0">Choisissez un service puis un emplacement pour afficher le classement detaille des produits.</p>
                        </div>

                        <div class="box-body">
<div class="row mb-4">
                                <div class="col-md-4">
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
                                <div class="col-md-8 d-flex align-items-end justify-content-center">
                                    <button type="button" class="btn btn-primary me-2" id="appliquerFiltres">
                                        <i class="fa fa-filter"></i> Appliquer
                                    </button>
                                    <a href="{{ route('reports.produits') }}" class="btn btn-secondary">
                                        <i class="fa fa-refresh"></i> Reinitialiser
                                    </a>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-xl-4 col-md-6 mb-3">
                                    <div class="box bg-primary text-white h-100">
                                        <div class="box-body text-center">
                                            <h2 class="mb-0">{{ $emplacements->count() }}</h2>
                                            <p class="mb-0">Emplacements disponibles</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-md-6 mb-3">
                                    <div class="box bg-success text-white h-100">
                                        <div class="box-body text-center">
                                            <h2 class="mb-0">{{ request('service_type') ? 1 : $serviceTypes->count() }}</h2>
                                            <p class="mb-0">Service(s) en vue</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-md-12 mb-3">
                                    <div class="box bg-info text-white h-100">
                                        <div class="box-body text-center">
                                            <h2 class="mb-0">{{ request('service_type') ? (request('service_type') === 'restaurant & lounge' ? 'Restaurant & Lounge' : ucfirst(request('service_type'))) : 'Tous' }}</h2>
                                            <p class="mb-0">Service selectionne</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="emplacement-buttons-container">
                                <div class="d-flex flex-wrap justify-content-center gap-2" id="emplacementButtons">
                                    @forelse($emplacements as $emplacement)
                                        <a href="{{ route('reports.produits.plusVendus.details', ['emplacement_id' => $emplacement->id, 'service_type' => request('service_type')]) }}"
                                           class="btn btn-outline-primary emplacement-btn">
                                            <span class="fw-600 d-block">{{ $emplacement->libelle }}</span>
                                            <small class="text-muted d-block">{{ $emplacement->type }}</small>
                                            <small class="d-block mt-1">Voir le classement</small>
                                        </a>
                                    @empty
                                        <div class="alert alert-warning text-center w-100">
                                            Aucun emplacement disponible pour ce filtre.
                                        </div>
                                    @endforelse
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

@push('styles')
<style>
.emplacement-buttons-container {
    max-width: 100%;
    overflow: hidden;
}

.emplacement-btn {
    min-width: 185px;
    margin: 5px;
    transition: all 0.25s ease;
    border: 2px solid #d7dde4;
    padding: 12px 10px;
    text-align: center;
}

.emplacement-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-color: #007bff;
    color: #fff !important;
    background-color: #007bff;
}

.emplacement-btn:hover small,
.emplacement-btn:hover span {
    color: #fff !important;
}

@media (max-width: 768px) {
    .emplacement-btn {
        min-width: 150px;
        font-size: 14px;
        padding: 10px 8px;
    }
}

@media (max-width: 576px) {
    .emplacement-btn {
        min-width: 125px;
        margin: 3px;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const serviceTypeSelect = document.getElementById('service_type');
    const appliquerFiltres = document.getElementById('appliquerFiltres');

    if (appliquerFiltres) {
        appliquerFiltres.addEventListener('click', function() {
            const params = new URLSearchParams();
            if (serviceTypeSelect && serviceTypeSelect.value) {
                params.append('service_type', serviceTypeSelect.value);
            }
            const baseUrl = '{{ route("reports.produits") }}';
            const query = params.toString();
            window.location.href = query ? `${baseUrl}?${query}` : baseUrl;
        });
    }
});
</script>
@endpush
