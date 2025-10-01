@extends("layouts.admin")

@section("content")
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <div class="container-full AppReport">
        <!-- Content Header (Page header) -->
        <div class="content-header">
        </div>
        <!-- Main content -->
        <section class="content">
            <div class="row d-flex justify-content-center align-items-center">
                <div class="col-xl-10">
                <div class="box">
                    <div class="box-header d-flex justify-content-between align-items-center" style="padding : 1.5rem">
                        <h4 class="box-title">Rapports journaliers des ventes
                            <small class="subtitle">Regroupés par journées de ventes</small>
                        </h4>
                    </div>
                    <div class="box-body" v-cloak>
                        <div class="table-responsive">
                            <table id="example" class="table table-lg invoice-archive">
                                <thead>
                                    <tr>
                                        <th>Journée de vente</th>
                                        <th>Caissier</th>
                                        <th>Total encaissé</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(data, index) in allReports">
                                        <td>@{{ data.sale_day.start_time }} <span v-if="data.sale_day.end_time"> au @{{ data.sale_day.end_time  }}</span><span class="text-success ms-2">Journée ouverte</span> </td>
                                        <td>
                                            <h6 class="mb-0">
                                                <a href="#" class="fw-600">@{{ data.user.name }}</a>
                                                <span class="d-block text-muted">@{{ data.user.role }}</span>
                                            </h6>
                                        </td>
                                        <td>
                                            <h6 class="mb-0 fw-bold">@{{ data.total_factures }}</h6>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" @click="showReportDetails(data)" class="waves-effect waves-light btn btn-sm btn-outline btn-rounded btn-danger">Afficher <span v-if="load_id === data.user.id" class="spinner-border spinner-border-sm ms-1"></span></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </section>
		<!-- /.content -->

         <div class="modal fade modal-sell-detail" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel" v-if="selectedSell">Détails des opérations pour le caissier <span v-if="selectedSell.user" class="fw-600 text-primary">@{{ selectedSell.user.name }}</span> </h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <section v-if="selectedSell" class="invoice border-0 p-0 printableArea">
                            <div class="row" v-if="sellFactures.length > 0">
                                <div class="col-12 table-responsive">
                                    <table class="table table-bordered">
                                        <tbody>
                                            <tr>
                                                <th>N° Facture</th>
                                                <th>Journée du</th>
                                                <th class="text-end">Montant</th>
                                                <th class="text-end">Montant payé</th>
                                                <th class="text-end">Serveur</th>
                                                <th class="text-end">Statut</th>
                                            </tr>
                                            <tr v-for="(fac, index) in sellFactures" :key="index">
                                                <td>@{{ fac.numero_facture }}</td>
                                                <td><span v-if="fac.sale_day">@{{ fac.sale_day.sale_date }}</span></td>
                                                <td class="text-end">@{{ fac.total_ttc }}</td>
                                                <td class="text-end">@{{ allPayment(fac.payments) }}</td>
                                                <td class="text-end"><span v-if="fac.user">@{{ fac.user.name }}</span></td>
                                                <td class="text-end"><span class="badge badge-pill" :class="{'badge-warning-light':fac.statut==='en_attente', 'badge-success-light':fac.statut==='payée', 'badge-danger-light':fac.statut==='annulée'}">@{{ fac.statut.replaceAll('_', ' ') }}</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- /.col -->
                            </div>
                        </section>
                    </div>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
	</div>
</div>
  <!-- /.content-wrapper -->
@endsection

@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/reports.js") }}"></script>	
@endpush