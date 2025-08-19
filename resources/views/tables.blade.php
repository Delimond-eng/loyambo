@extends("layouts.admin")

@section("content")
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
	  	<div class="container-full">
			<!-- Content Header (Page header) -->
			<div class="content-header">
			</div>
			<!-- Main content -->
			<section class="content"  id="AppPlace" v-cloak>
				<div class="row">
					<div class="col-xl-6">
						<div class="box">
							<div class="box-header d-flex justify-content-between align-items-center" style="padding : 1.5rem">
								<h4 class="box-title">Les Tables & Chambres
									<small class="subtitle">Listes des tables & chambres</small>
								</h4>
								<a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#tableModal" class="btn btn-primary btn-rounded text-center">+ Nouvelle table</a>					
							</div>
							<div class="box-body p-0">
								<div class="table-responsive">
									<table class="table no-border table-vertical-center">
										<thead>
											<tr>
												<th class="p-0" style="min-width: 150px"></th>
												<th class="p-0" style="min-width: 150px"></th>
												<th class="p-0" style="min-width: 200px"></th>
											</tr>
										</thead>
										<tbody>
											<tr class="border-bottom" v-for="(data, i) in allTables" :key="i">
												<td>
													<a href="#" class="text-dark fw-600 hover-primary fs-16 fw-700">@{{ data.emplacement.type==='hôtel' ? 'Chambre NO.' : 'Table NO.'}} @{{ data.numero }}</a>
													<span class="text-fade d-block" v-if="data.emplacement">@{{ data.emplacement.libelle }}</span>
												</td>

												<td>
													<span :class="{'badge-primary-light':data.statut==='libre', 'badge-danger-light':data.statut==='occupée', 'badge-warning-light':data.statut==='réservée'}" class="badge badge-pill">@{{ data.statut }}</span>
												</td>

												<td class="d-flex align-items-center justify-content-end">
													<a href="#"  data-bs-toggle="modal" data-bs-target="#tableModal" @click="formTable = [{id:data.id, numero:data.numero, emplacement_id:data.emplacement_id}]" class="btn btn-primary-light btn-sm me-1"><span class="icon-Write fs-18"><span class="path1"></span><span class="path2"></span></span></a>
													<a href="#" class="btn btn-danger-light btn-sm"><span class="icon-Trash1 fs-18"><span class="path1"></span><span class="path2"></span></span></a>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Popup Model Plase Here -->
				<div id="tableModal" class="modal fade in" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<form class="modal-content" @submit.prevent="submitTables">
							<div class="modal-header">
								<h4 class="modal-title" id="myModalLabel">Création table & chambre</h4>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							<div class="modal-body">
								<div class="form-horizontal">
									<div class="form-group">
										<div class="input-group mb-3">
											<span class="input-group-text bg-transparent"><i
													class="ti-write text-primary"></i></span>
											<input v-model="formTable[0].numero" type="text" class="form-control ps-15 bg-transparent"
												placeholder="Numéro...ex: 01">
										</div>
									</div>
									<div class="form-group">
										<div class="input-group mb-3">
											<span class="input-group-text bg-transparent"><i
													class="ti-location-pin text-primary"></i></span>
											<select class="form-control ps-15 bg-transparent" v-model="formTable[0].emplacement_id">
												<option value="" hidden selected>Sélectionnez un emplacement</option>
												<option v-for="(emp, index) in allEmplacements" :value="emp.id">@{{ emp.libelle }}</option>
											</select>
										</div>
									</div>
								</div>
							</div>
							<div class="modal-footer d-flex">
								<button type="submit" class="btn btn-success" :disabled="isLoading"><span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span> Enregistrer</button>
								<button type="button" class="btn btn-danger float-end" data-bs-dismiss="modal">Fermer</button>
							</div>
						</form>
						<!-- /.modal-content -->
					</div>
					<!-- /.modal-dialog -->
				</div>
				<!-- /Popup Model Plase Here -->
			</section>
			<!-- /.content -->
	  	</div>
  	</div>
  <!-- /.content-wrapper -->
@endsection
@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/places.js") }}"></script>
@endpush