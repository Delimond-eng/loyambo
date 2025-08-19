@extends("layouts.admin")

@section("content")
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
	  	<div class="container-full">
			<!-- Content Header (Page header) -->
			<div class="content-header">
			</div>
			<!-- Main content -->
			<section class="content" id="AppPlace" v-cloak>
				<div class="row">
					<div class="col-xl-6">
						<div class="box">
							<div class="box-header d-flex justify-content-between align-items-center" style="padding : 1.5rem">
								<h4 class="box-title">Les emplacements
									<small class="subtitle">Listes des emplacements</small>
								</h4>
								<a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#emplacementModal" class="btn btn-primary btn-rounded text-center">+ Nouveau emplacement</a>					
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
											<tr class="border-bottom" v-for="(data, index) in allEmplacements" :key="index">
												<td>
													<a href="#" class="text-dark fw-600 hover-primary fs-16 fw-700">@{{ data.libelle }}</a>
												</td>
												<td>
													<span class="badge badge-pill badge-primary-light">@{{ data.type }}</span>
												</td>
												<td class="d-flex align-items-center justify-content-end">
													<div class="d-flex">
														<button type="button" class="btn btn-primary btn-xs me-1" data-bs-toggle="modal" data-bs-target="#addTablesModal" @click="formTable=[{numero:'', emplacement_id:data.id}]; selectedEmplacement=data"><i class="mdi mdi-plus"></i></button>
														<button type="button" class="btn btn-primary-light btn-xs me-1"  data-bs-toggle="modal" data-bs-target="#emplacementModal" @click="formEmplacement = data"><i class="mdi mdi-pencil"></i></button>
														<button type="button" class="btn btn-danger-light btn-xs"><i class="mdi mdi-delete"></i></button>
													</div>
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
				<div id="emplacementModal" class="modal fade in" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<form class="modal-content" @submit.prevent="submitEmplacement">
							<div class="modal-header">
								<h4 class="modal-title" id="myModalLabel">Création emplacement</h4>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							<div class="modal-body">
								<div class="form-horizontal">
									<div class="form-group">
										<div class="input-group mb-3">
											<span class="input-group-text bg-transparent"><i
													class="ti-home text-primary"></i></span>
											<input type="text" class="form-control ps-15 bg-transparent"
												placeholder="Nom de l'emplacement" v-model="formEmplacement.libelle" required>
										</div>
									</div>
									<div class="form-group">
										<div class="input-group mb-3">
											<span class="input-group-text bg-transparent"><i
													class=" ti-pin2  text-primary"></i></span>
											<select class="form-control ps-15 bg-transparent" v-model="formEmplacement.type" required>
												<option value="" selected hidden label="Type"></option>
												<option value="restaurant & lounge">Restaurant & Lounge</option>
												<option value="hôtel">Hôtel</option>
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

				<!-- Popup Model Plase Here -->
				<div id="addTablesModal" class="modal fade in" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<form class="modal-content" @submit.prevent="submitTables">
							<div class="modal-header">
								<h4 class="modal-title" id="myModalLabel" v-if="selectedEmplacement">
									Ajoutez des <span v-if="selectedEmplacement.type==='hôtel'">chambres</span><span v-else>tables</span>
									: @{{ selectedEmplacement.type }} : @{{ selectedEmplacement.libelle }}
								</h4>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							<div class="modal-body">
								<div class="box"v-if="selectedEmplacement">
									<div class="box-body p-0" v-if="selectedEmplacement.tables.length">
										<h4 class="box-title d-block">
											 <span v-if="selectedEmplacement.type==='hôtel'">Chambres</span><span v-else>Tables</span> existantes
										</h4>
										<div class="d-inline-block">
											<a href="#" v-for="(c, j) in selectedEmplacement.tables" class="waves-effect waves-light fw-500 btn btn-outline btn-sm btn-rounded btn-primary mb-2 me-1" :key="j"><span v-if="selectedEmplacement.type==='hôtel'">#chambre</span><span v-else>#table</span> @{{ c.numero }}</a>
										</div>
									</div>
								</div>
								<div class="form-horizontal" v-if="selectedEmplacement">
									<div class="form-group" v-for="(input, index) in formTable" :key="index">
										<div class="input-group mb-3">
											<span class="input-group-text bg-transparent"><i
													class="ti-panel text-primary"></i></span>
											<input type="text" class="form-control ps-15 bg-transparent"
												placeholder="Numéro..ex: 01" v-model="input.numero" required>
											<button type="button" v-if="index===0" @click="formTable.push({numero:'', emplacement_id:selectedEmplacement.id})" class="btn btn-sm btn-primary"><i class="mdi mdi-plus"></i></button>
											<button type="button" v-else @click="formTable.splice(index, 1)" class="btn btn-sm btn-danger-light"><i class="mdi mdi-close"></i></button>
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

