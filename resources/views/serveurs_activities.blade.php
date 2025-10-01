@extends("layouts.admin")

@section("content")
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
	  <div class="container-full">
		<!-- Content Header (Page header) -->

		<section class="content AppService" v-cloak>
		  	<div class="row d-flex justify-content-center align-items-center g-4">
				<div class="col-xl-12">
                    @include("components.menus.serveurs")
                </div>

				<div class="col-12 col-lg-8">
					<div class="box">
						<div class="box-header d-flex justify-content-between align-items-center" style="padding:1.5rem">
							<h4 class="box-title fw-600">SERVEURS EN SERVICE</h4>
							<button class="btn btn-sm btn-round btn-danger btn-xs" @click="triggerClosingDay" title="Clôturer">Clôturer la journée <i class="fa fa-sign-out ms-1"></i></button>
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
													<img src="assets/images/service.jpg" class="me-2" alt="" width="50">
													<div>
														<h5 class="fw-500">@{{ data.user.name }}</h5>
														<p v-if="data.user.last_log"><span class="badge badge-dot me-2" :class="data.user.last_log.status ==='offline' ? 'badge-danger' : 'badge-success'"></span>@{{ formateDate(data.user.last_log.logged_in_at) }},<span class="fs-12"> @{{ formateTime(data.user.last_log.logged_in_at) }}</span></p>
														<p v-else>Aucune activité</p>
													</div>
												</div>
											</td>
											<td align="center" class="fw-900">@{{  data.total_encaisse}}</td>
											<td align="center"><a href="javascript:void(0)" @click="triggerSingleClosing(data)" class="btn btn-xs btn-danger btn-outline btn-xs" title="Clôturer"><i class="fa fa-sign-out me-1"></i>Clôturer</a></td>
										</tr>
									</tbody>
								</table>
							</div>

						</div>
					</div>
				</div>
		  	</div>
			<div id="reportAppendModal" class="modal fade in" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<form class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title" id="myModalLabel">Clôture de la journée pour le serveur : <span v-if="selectedData" class="fw-600 text-primary">@{{ selectedData.user.name }}</span></h4>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<div class="form-horizontal row" v-if="selectedData">
								<div class="form-group col-md-4">
									<label class="form-label">Valeur théorique</label>
									<input type="text" :value="selectedData.total_encaisse" class="form-control" placeholder="Ex: 02" readonly>
								</div>
								<div class="form-group col-md-4">
									<label class="form-label">Total espèces</label>
									<input type="text" class="form-control" placeholder="Ex: 50000" required>
								</div>
								<div class="form-group col-md-4">
									<label class="form-label text-muted">Difference</label>
									<input type="text" class="form-control" placeholder="Ex:1" readonly>
								</div>
								<div class="form-group col-md-4">
									<label class="form-label">Nombre tickets du serveur</label>
									<input type="text" :value="selectedData.total_ticket" class="form-control" placeholder="Ex: 02" readonly>
								</div>
								<div class="form-group col-md-4">
									<label class="form-label">Nombre tickets emis</label>
									<input type="text" class="form-control" placeholder="Ex: 02" required>
								</div>
								<div class="form-group col-md-4">
									<label class="form-label text-muted">Difference</label>
									<input type="text" class="form-control" placeholder="Ex: 02" readonly>
								</div>
							</div>
						</div>
						<div class="modal-footer d-flex">
							<button type="submit" class="btn btn-google" :disabled="isLoading"><span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span> <i v-else class="fa fa-sign-out me-1"></i> Clôturer</button>
							<button type="button" class="btn btn-dark btn-outline float-end" data-bs-dismiss="modal">Fermer</button>
						</div>
					</form>
					<!-- /.modal-content -->
				</div>
				<!-- /.modal-dialog -->
			</div>
		</section>
	  </div>
  </div>
  <!-- /.content-wrapper -->
@endsection

@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/service.js") }}"></script>
@endpush
