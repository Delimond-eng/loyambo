@extends("layouts.admin")

@section("content")
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
	  	<div class="container-full">
			<!-- Content Header (Page header) -->
			<div class="content-header">
			</div>
			<!-- Main content -->
			<section class="content AppPlace" v-cloak>
				<div class="row d-flex justify-content-center align-items-center g-4">
					<div class="col-xl-12">
						@include("components.menus.emplacements")
					</div>
					<div class="col-xl-6">
						<div class="box">
							<div class="box-header d-flex justify-content-between align-items-center" style="padding : 1.5rem">
								<h4 class="box-title">Les Tables & Chambres
									<small class="subtitle">Listes des tables & chambres</small>
								</h4>
								<a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#tableModal" class="btn btn-sm btn-primary btn-rounded text-center">+ Nouvelle table</a>					
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
													<a href="#" class="text-dark fw-600 hover-primary fs-16 fw-700">Table NO.@{{ data.numero }}</a>
													<span class="text-fade d-block" v-if="data.emplacement">@{{ data.emplacement.libelle }}</span>
												</td>

												<td>
													
												</td>
												<td>
													<span :class="{'badge-primary-light':data.statut==='libre', 'badge-danger-light':data.statut==='occupée', 'badge-warning-light':data.statut==='réservée'}" class="badge badge-pill">@{{ data.statut }}</span>
												</td>

												<td class="d-flex align-items-center justify-content-end">
													<a href="#"  data-bs-toggle="modal" data-bs-target="#tableModal" @click="formTable = {id:data.id, numero:data.numero, emplacement_id:data.emplacement_id}" class="btn btn-primary-light btn-sm me-1"><span class="icon-Write fs-18"><span class="path1"></span><span class="path2"></span></span></a>
													<a href="#" class="btn btn-danger-light btn-sm"><span class="icon-Trash1 fs-18"><span class="path1"></span><span class="path2"></span></span></a>
												</td>
											</tr>
											<tr class="border-bottom" v-for="(data, j) in allChambres" :key="j">
												<td>
													<a href="#" class="text-dark fw-600 hover-primary fs-16 fw-700">Chambre NO. @{{ data.numero }}</a>
													<span class="text-fade d-block" v-if="data.emplacement">@{{ data.emplacement.libelle }}</span>
												</td>
												<td>
													<a href="#" class="text-dark fw-600 hover-primary fs-16 fw-700">Type <span class="text-primary">@{{ data.type }}</span></a>
													<span class="text-fade d-block">Prix : @{{ data.prix }} @{{ data.prix_devise }}</span>
												</td>

												<td>
													<span :class="{'badge-primary-light':data.statut==='libre', 'badge-danger-light':data.statut==='occupée', 'badge-warning-light':data.statut==='réservée'}" class="badge badge-pill">@{{ data.statut }}</span>
												</td>

												<td class="d-flex align-items-center justify-content-end">
													<a href="#"  data-bs-toggle="modal" data-bs-target="#tableModal" @click="formTable = {id:data.id, numero:data.numero, emplacement_id:data.emplacement_id, prix:data.prix, type:data.type, capacite:data.capacite, prix_devise:data.prix_devise}" class="btn btn-primary-light btn-sm me-1"><span class="icon-Write fs-18"><span class="path1"></span><span class="path2"></span></span></a>
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
									  	<label class="form-label">Numéro</label>
									  	<input type="text" v-model="formTable.numero" class="form-control" placeholder="Ex: 02" required>
									</div>
									<div class="form-group">
									  	<label class="form-label">Emplacement</label>
									 	<select class="form-select" v-model.number="formTable.emplacement_id">
											<option value="" hidden selected>Sélectionnez un emplacement</option>
											<option v-for="(emp, index) in allEmplacements" :value="emp.id">@{{ emp.libelle }}</option>
										</select>
									</div>
									<div class="form-group" v-if="isHotel">
									  	<label class="form-label">Prix de la chambre</label>
									 	<div class="d-flex">
											<input v-model="formTable.prix" type="number" class="form-control"
												placeholder="Prix de la chambre." required>
											<select style="width: 100px;" v-model="formTable.prix_devise" class="form-control">
												<option value="CDF" selected>CDF</option>
												<option value="USD">USD</option>
											</select>
										</div>
									</div>
									<div class="form-group" v-if="isHotel">
									  	<label class="form-label">Type de chambre</label>
									  	<select class="form-select" v-model="formTable.type">
											<option value="" hidden selected>Sélectionnez un type</option>
											<option value="simple">Simple</option>
											<option value="double">Double</option>
											<option value="suite">Suite</option>
										</select>
									</div>
									<div class="form-group" v-if="isHotel">
									  	<label class="form-label">Capacité de la chambre</label>
									  	<input type="text" v-model="formTable.capacite" class="form-control" placeholder="Ex: 1 ou 2.." required>
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