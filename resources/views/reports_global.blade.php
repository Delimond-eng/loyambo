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
            <div class="row">
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
                                            <button type="button" class="waves-effect waves-light btn btn-sm btn-outline btn-rounded btn-danger">Afficher</button>
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
	</div>
</div>
  <!-- /.content-wrapper -->
@endsection

@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/reports.js") }}"></script>	
@endpush