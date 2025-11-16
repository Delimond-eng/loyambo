@extends("layouts.admin")

@section("content")
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
	  <div class="container-full">
		<!-- Content Header (Page header) -->
		<div class="content-header">
		</div>
		<!-- Main content -->
		<section class="content" id="AppProduct" v-cloak>
			<div class="row d-flex justify-content-center g-4">
				<div class="col-xl-12">
                    @include("components.menus.products")
                </div>
			  	<div class="col-xl-6">
					<div class="box">
						<div class="box-header d-flex justify-content-between align-items-center" style="padding : 1.5rem">
							<h4 class="box-title">Les catégories
								<small class="subtitle">Listes des catégories de produit</small>
							</h4>
							<a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#categoryModal" class="btn btn-primary btn-sm text-center btn-rounded">+ Nouvelle catégorie</a>					
						</div>
						<div class="box-body p-0">
							<div class="table-responsive">
								<table class="table no-border table-vertical-center">
									<thead>
										<tr>
											<th class="p-0" style="width: 40px"></th>
											<th class="p-0" style="min-width: 150px"></th>
											<th class="p-0" style="min-width: 200px"></th>
										</tr>
									</thead>
									<tbody>
										<tr class="border-bottom" v-for="(data, index) in allCategories" :key="index">
											<td>
												<div class="h-50 w-50 l-h-50 rounded text-center">
													<div :style="`background-color:${data.couleur} !important`"  class="bg-primary fw-900 rounded w-40 h-40 l-h-40">
													</div>
												</div>
											</td>
											<td>
												<div>
													<a href="#" class="text-dark fw-600 hover-primary fs-16 fw-700">@{{ data.libelle }}</a> <br>	
													<span class="fs-12">@{{ data.type_service }}</span>
												</div>
											</td>

											<td class="d-flex align-items-center justify-content-end">
												<a href="#" class="btn btn-primary-light btn-sm me-1" data-bs-toggle="modal" data-bs-target="#productListModalByCategory" @click="selectedCategory = data"><span class="icon-Dinner1 fs-18"><span class="path1"></span><span class="path2"></span></span></a>
												<a href="#" data-bs-toggle="modal" data-bs-target="#categoryModal"  @click="formCategory = data" class="btn btn-info-light btn-sm me-1"><span class="icon-Write fs-18"><span class="path1"></span><span class="path2"></span></span></a>
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
			<div id="categoryModal" class="modal fade in" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<form class="modal-content" @submit.prevent="submitCategorie">
						<div class="modal-header">
							<h4 class="modal-title" id="myModalLabel">Création catégorie</h4>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<div class="form-horizontal">
								<div class="form-group">
									<div class="input-group mb-3">
										<span class="input-group-text bg-transparent"><i
												class="ti-write text-primary"></i></span>
										<input type="text" v-model="formCategory.libelle" class="form-control ps-15 bg-transparent"
											placeholder="Saisir la catégorie..." required>
									</div>
								</div>
								<!-- <div class="form-group">
									<div class="input-group mb-3">
										<span class="input-group-text bg-transparent"><i
												class="ti-panel text-primary"></i></span>
										<input type="text" class="form-control ps-15 bg-transparent"
											placeholder="Code" v-model="formCategory.code" required>
									</div>
								</div> -->
								<div class="form-group">
									<div class="input-group mb-3">
										<span class="input-group-text bg-transparent"><i
												class="ti-layout-accordion-separated text-primary"></i></span>
										<select class="form-control ps-15 bg-transparent" v-model="formCategory.type_service" required>
											<option value="" selected hidden label="Sélectionnez service"></option>
											<option value="cuisine">Cuisine</option>
											<option value="boisson">Boisson</option>
										</select>
									</div>
								</div>
								<div class="form-group">
									<div class="input-group mb-3">
										<span class="input-group-text bg-transparent"><i
												class="ti-brush text-primary"></i></span>
										<input type="color" v-model="formCategory.couleur" class="form-control ps-15 bg-transparent">
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer d-flex">
							<button type="submit" class="btn btn-success btn-block" :disabled="isLoading"><span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span>Enregistrer</button>
							<button type="button" @click="resetAll" class="btn btn-danger float-end" data-bs-dismiss="modal">Fermer</button>
						</div>
					</form>
					<!-- /.modal-content -->
				</div>
				<!-- /.modal-dialog -->
			</div>
			<!-- /Popup Model Plase Here -->
			<!-- Popup Model Plase Here -->
			<div id="productListModalByCategory" class="modal fade in" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<div class="modal-dialog modal-xl">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title" id="myModalLabel" v-if="selectedCategory">Liste des @{{ selectedCategory.libelle }}</h4>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<div class="table-responsive" v-if="selectedCategory">
                                <table id="table-product" class="table table-lg invoice-archive">
                                    <thead>
                                        <tr>
                                            <th>REF</th>
                                            <th>Libellé</th>
                                            <th>Prix unitaire</th>
                                            <th>Qté Initial</th>
                                            <th>unité</th>
                                            <th>statut</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(data, index) in selectedCategory.produits" :key="index">
                                            <td>@{{data.reference }}</td>
                                            <td>
                                                <h6 class="mb-0">
                                                    <a href="#">@{{ data.libelle }}</a>
                                                </h6>
                                            </td>
                                            <td>
                                                @{{ data.prix_unitaire}}
                                            </td>
                                            <td>
                                                <h6 class="mb-0 fw-bold">@{{ data.qte_init }}</h6>
                                            </td>
                                            <td>
                                                <h6 class="mb-0 fw-bold">@{{ data.unite }}</h6>
                                            </td>
                                           
                                            <td>
                                                <span class="badge badge-pill badge-success">Paid on Mar 16, 2018</span>
                                            </td>

                                             <td class="text-center">
												<a href="#" class="btn btn-danger-light btn-rounded btn-sm"><span class="icon-Trash1 fs-16"><span class="path1"></span><span class="path2"></span></span></a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
						</div>
						<div class="modal-footer d-flex">
							<button type="button" class="btn btn-primary btn-block">Exporter excel</button>
						</div>
					</div>
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
    <script type="module" src="{{ asset("assets/js/scripts/product.js") }}"></script>
@endpush