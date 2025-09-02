@extends("layouts.admin")

@section("content")
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
	  <div class="container-full">
		<!-- Content Header (Page header) -->

		<section class="content AppService" v-cloak>

		  <div class="row">
			  <div class="col-12 col-lg-8">
				<div class="box">
				  <div class="box-header" style="padding:1.5rem">
					<h4 class="box-title">SERVEURS EN SERVICE</h4>
				  </div>

				  <div class="box-body">
					<div class="table-responsive">
						<table class="table product-overview">
							<thead>
								<tr>
									<th>Serveur</th>
									<th style="text-align:center">Total facturé</th>
									<th style="text-align:center">Action</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="(data, index) in allServeurs" :key="index">
									<td>
										<div class="d-flex align-items-center">
											<img src="assets/images/service.jpg" class="me-2" alt="" width="80">
											<div>
												<h5 class="fw-500">@{{ data.user.name }}</h5>
												<p v-if="data.user.last_log"><span class="badge badge-dot me-2" :class="data.user.last_log.status ==='offline' ? 'badge-danger' : 'badge-success'"></span>@{{ formateDate(data.user.last_log.logged_in_at) }},<span class="fs-12"> @{{ formateTime(data.user.last_log.logged_in_at) }}</span></p>
												<p v-else>Aucune activité</p>
											</div>
										</div>
									</td>
									<td align="center" class="fw-900">@{{  data.total_encaisse}}</td>
									<td align="center"><a href="javascript:void(0)" class="btn btn-circle btn-danger btn-xs" title="Clôturer"><i class="fa fa-sign-out"></i></a></td>
								</tr>
							</tbody>
						</table>
					</div>

				  </div>
				</div>
			  </div>
		  </div>

		</section>
	  </div>
  </div>
  <!-- /.content-wrapper -->
@endsection

@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/service.js") }}"></script>
@endpush
