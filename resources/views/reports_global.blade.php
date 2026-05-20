@extends("layouts.admin")

@section("content")
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <div class="container-full AppReport">
        <!-- Content Header (Page header) -->
        <div class="content-header"></div>

        <!-- Main content -->
        <section class="content">
            <div class="row d-flex justify-content-center align-items-center">
                <div class="col-xl-12">
                    @include("components.menus.reports")
                </div>
                <div class="col-xl-10">
                    <div class="box">
                        <div class="box-header d-flex justify-content-center align-items-center" style="padding: 1.5rem">
                            <h4 class="box-title">
                                Rapports journaliers par journee de vente
                                <small class="subtitle text-center">Suivi clair du debut et de la fin de chaque journee</small>
                            </h4>
                        </div>

                        <div class="box-body" v-cloak>
<form method="GET" action="{{ route('reports.global') }}" class="mb-3">
                                <div class="row g-2">
                                    <div class="col-md-3">
                                        <label for="service_type">Service</label>
                                        <select name="service_type" id="service_type" class="form-control">
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
                                        <select name="emplacement_id" id="emplacement_id" class="form-control">
                                            <option value="">Tous</option>
                                            @foreach($emplacements as $emplacement)
                                                <option value="{{ $emplacement->id }}" {{ request('emplacement_id') == $emplacement->id ? 'selected' : '' }}>
                                                    {{ $emplacement->libelle }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="caissier_id">Caissier</label>
                                        <select name="caissier_id" id="caissier_id" class="form-control">
                                            <option value="">Tous</option>
                                            @foreach($caissiers as $caissier)
                                                <option value="{{ $caissier->id }}" {{ request('caissier_id') == $caissier->id ? 'selected' : '' }}>
                                                    {{ $caissier->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Periode</label>
                                        <div class="d-flex gap-1">
                                            <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
                                            <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12 text-center">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-filter"></i> Appliquer
                                        </button>
                                        <a href="{{ route('reports.global') }}" class="btn btn-secondary">
                                            <i class="fa fa-refresh"></i> Reinitialiser
                                        </a>
                                        <a href="{{ route('reports.global.export.pdf', request()->query()) }}" class="btn btn-outline-danger ms-2">
                                            <i class="fa fa-file-pdf"></i> Export PDF
                                        </a>
                                        <a href="{{ route('reports.global.export.excel', request()->query()) }}" class="btn btn-outline-success ms-1">
                                            <i class="fa fa-file-excel"></i> Export Excel
                                        </a>
                                    </div>
                                </div>
                            </form>

                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <div class="box bg-primary text-white">
                                        <div class="box-body text-center">
                                            <h3 class="mb-0">@{{ formatNumber(totalEncaisse) }}</h3>
                                            <p class="mb-0">Total encaisse</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="box bg-success text-white">
                                        <div class="box-body text-center">
                                            <h3 class="mb-0">@{{ totalFactures }}</h3>
                                            <p class="mb-0">Factures</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="box bg-info text-white">
                                        <div class="box-body text-center">
                                            <h3 class="mb-0">@{{ totalPaiements }}</h3>
                                            <p class="mb-0">Paiements</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="box bg-warning text-white">
                                        <div class="box-body text-center">
                                            <h3 class="mb-0">@{{ totalJours }}</h3>
                                            <p class="mb-0">Journees</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="example" class="table table-lg invoice-archive">
                                    <thead>
                                        <tr>
                                            <th>Journee</th>
                                            <th>Debut</th>
                                            <th>Fin</th>
                                            <th>Statut</th>
                                            <th>Duree</th>
                                            <th>Caissier</th>
                                            <th>Total encaisse</th>
                                            <th>Factures</th>
                                            <th>Paiements</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(data, index) in allReports" :key="index">
                                            <td>
                                                <strong>@{{ formatSaleDate(data.sale_day ? data.sale_day.sale_date : null) }}</strong>
                                            </td>
                                            <td>@{{ formatTime(data.sale_day ? data.sale_day.start_time : null) }}</td>
                                            <td>
                                                <span v-if="data.sale_day && data.sale_day.end_time">@{{ formatTime(data.sale_day.end_time) }}</span>
                                                <span v-else class="text-muted">--</span>
                                            </td>
                                            <td>
                                                <span class="badge" :class="(data.sale_day && data.sale_day.end_time) ? 'badge-success' : 'badge-warning'">
                                                    @{{ (data.sale_day && data.sale_day.end_time) ? 'Cloturee' : 'Ouverte' }}
                                                </span>
                                            </td>
                                            <td>@{{ formatDuration(data.sale_day ? data.sale_day.start_time : null, data.sale_day ? data.sale_day.end_time : null) }}</td>
                                            <td>
                                                <h6 class="mb-0">
                                                    <a href="#" class="fw-600">@{{ data.user.name }}</a>
                                                    <span class="d-block text-muted">@{{ data.user.role }}</span>
                                                </h6>
                                            </td>
                                            <td>
                                                <h6 class="mb-0 fw-bold">@{{ formatNumber(data.total_factures) }}</h6>
                                            </td>
                                            <td class="text-center">
                                                <span class="fw-600">@{{ data.total_factures_count || 0 }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="fw-600">@{{ data.total_paiements || 0 }}</span>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" @click="showReportDetails(data)" class="waves-effect waves-light btn btn-sm btn-outline btn-rounded btn-danger">
                                                    Afficher
                                                    <span v-if="load_id === `${data.user?.id || ''}-${data.sale_day?.id || ''}`" class="spinner-border spinner-border-sm ms-1"></span>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr v-if="allReports.length === 0">
                                            <td colspan="10" class="text-center text-muted py-4">Aucune donnee.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="modal fade modal-sell-detail" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h4 class="modal-title" id="myModalLabel" v-if="selectedSell">
                                Details des operations pour le caissier
                                <span v-if="selectedSell.user" class="fw-600 text-primary">@{{ selectedSell.user.name }}</span>
                            </h4>
                            <p class="text-muted mb-0" v-if="selectedSell && selectedSell.sale_day">
                                Journee: @{{ formatSaleDate(selectedSell.sale_day.sale_date) }} |
                                Debut: @{{ formatTime(selectedSell.sale_day.start_time) }} |
                                Fin: @{{ selectedSell.sale_day.end_time ? formatTime(selectedSell.sale_day.end_time) : 'Ouverte' }}
                            </p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <section v-if="selectedSell" class="invoice border-0 p-0 printableArea">
                            <div class="row" v-if="sellFactures.length > 0">
                                <div class="col-12 table-responsive">
                                    <table class="table table-bordered">
                                        <tbody>
                                            <tr>
                                                <th>N Facture</th>
                                                <th>Journee du</th>
                                                <th class="text-end">Montant</th>
                                                <th class="text-end">Montant paye</th>
                                                <th class="text-end">Serveur</th>
                                                <th class="text-end">Statut</th>
                                            </tr>
                                            <tr v-for="(fac, index) in sellFactures" :key="index">
                                                <td>@{{ fac.numero_facture }}</td>
                                                <td><span v-if="fac.sale_day">@{{ formatSaleDate(fac.sale_day.sale_date) }}</span></td>
                                                <td class="text-end">@{{ fac.total_ttc }}</td>
                                                <td class="text-end">@{{ allPayment(fac.payments) }}</td>
                                                <td class="text-end"><span v-if="fac.user">@{{ fac.user.name }}</span></td>
                                                <td class="text-end">
                                                    <span class="badge badge-pill" :class="{'badge-warning-light':fac.statut==='en_attente', 'badge-success-light':fac.statut==='payÃƒÂ©e', 'badge-danger-light':fac.statut==='annulÃƒÂ©e'}">
                                                        @{{ fac.statut.replaceAll('_', ' ') }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="text-center text-muted py-4" v-else>Aucune facture.</div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/reports.js") }}"></script>
@endpush
