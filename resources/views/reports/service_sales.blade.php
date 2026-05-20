@extends("layouts.admin")

@section("content")
<!-- Content Wrapper. Contains page content -->
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
                            <h4 class="box-title">Ventes par service - etape 1/2</h4>
                            <p class="text-muted">Choisissez un service puis un emplacement. Les resultats seront groupes par journee de vente (debut/fin).</p>
                        </div>

                        <div class="box-body">
<div class="row mb-3">
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
                                    <a href="{{ route('reports.service.vente') }}" class="btn btn-secondary">
                                        <i class="fa fa-refresh"></i> Reinitialiser
                                    </a>
                                </div>
                            </div>

                            <div class="emplacement-buttons-container">
                                <div class="d-flex flex-wrap justify-content-center gap-2" id="emplacementButtons">
                                    @foreach($emplacements as $emplacement)
                                        <a href="{{ route('reports.service_sales.emplacement', ['emplacement_id' => $emplacement->id]) }}"
                                           class="btn btn-outline-primary emplacement-btn"
                                           style="min-width: 150px; margin: 5px;">
                                            {{ $emplacement->libelle }}
                                            <br>
                                            <small class="text-muted">{{ $emplacement->type }}</small>
                                        </a>
                                    @endforeach
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
    transition: all 0.3s ease;
    border: 2px solid #e0e0e0;
    padding: 12px 8px;
    text-align: center;
}

.emplacement-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    border-color: #007bff;
    color: white !important;
    background-color: #007bff;
}

.emplacement-btn:hover,
.emplacement-btn:hover small {
    color: white !important;
}

.emplacement-btn.active {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}

.emplacement-btn.active small {
    color: rgba(255, 255, 255, 0.8) !important;
}

@media (max-width: 768px) {
    .emplacement-btn {
        min-width: 140px !important;
        font-size: 14px;
        padding: 10px 6px;
    }
}

@media (max-width: 576px) {
    .emplacement-buttons-container {
        text-align: center;
    }

    .emplacement-btn {
        min-width: 120px !important;
        margin: 3px !important;
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
            window.location.href = '{{ route("reports.service.vente") }}' + (params.toString() ? ('?' + params.toString()) : '');
        });
    }
});
</script>
@endpush
