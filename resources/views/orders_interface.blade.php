@extends("layouts.admin")

@section("content")
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
	  <div class="container-full AppService" v-cloak>
		<!-- Content Header (Page header) -->
		<div class="content-header">
			<div class="d-flex align-items-center">
				<div class="me-auto">
					<h3 class="page-title">Bon de commande <span v-if="selectedTable">Table @{{ selectedTable.numero }}</span></h3>
					<div class="d-inline-block align-items-center">
						<nav>
							<ol class="breadcrumb">
								<li class="breadcrumb-item active" aria-current="page">Veuillez ajouter les éléments de la commande</li>
							</ol>
						</nav>
					</div>
				</div>
			</div>
		</div>

		<!-- Main content -->
		<section class="content">
			<div class="row">
				<div class="col-12 col-lg-8">
					<div class="box">
						<div class="box-header bg-primary p-3">
							<h4 class="box-title">Catégories</h4>
						</div>
						<div class="box-body">
							<div class="row">
								<div class="col-12">
									<div class="d-inline-block">
										<a href="#" @click="products = data.produits" :style="`background-color:${data.couleur}; color:${getTextColor(data.couleur)}`" class="waves-effect waves-light btn me-2 btn-rounded mb-2" v-for="(data, index) in allCategories" :key="index"><i class="icon-Dinner1 me-2"><span class="path1"></span><span class="path2"></span></i> @{{ data.libelle }}</a>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="box">
						<div class="box-header d-flex justify-content-between p-3 align-items-center">
							<h4 class="box-title">Produits</h4>
							<div class="input-group" style="width: 300px">
                                <span class="input-group-text bg-transparent"><i
                                        class="ti-search text-primary"></i></span>
                                <input type="text" name="name" class="form-control ps-15 bg-transparent"
                                    placeholder="Recherche produit...">
                            </div>
						</div>
						<div class="box-body">
							<div class="row">
								<div class="col-lg-4 col-6" v-for="(data, i) in allProducts" :key="i">
									<a href="#" @click="data.qte = 1; cart.push(data)" class="box box-shadowed text-center">
										<div class="box-body" :style="`background-color:${data.categorie.couleur}; color:${getTextColor(data.categorie.couleur)}`">
											<h4 class="text-truncate fw-700">@{{ data.libelle }}</h4>
											<h4>@{{ data.prix_unitaire }} F</h4>
										</div>
									</a>
								</div>
							</div>
						</div>
					</div>

				</div>

				<div class="col-12 col-lg-4">
					<div class="box">
						<div class="box-header p-3 bg-primary">
							<h4 class="box-title fw-600">Bon de commande <span v-if="selectedTable">Table @{{ selectedTable.numero }}</span></h4>
						</div>

						<div class="box-body">
							<div class="table-responsive" v-if="cart.length">
								<table class="table simple mb-0">
									<tbody>
										<tr v-for="(data, index) in cart">
											<td style="width:70%">@{{ data.libelle }}</td>
											<td style="width:20%">
												<input type="number" v-model="data.qte" class="form-control" placeholder="1" min="1">
											</td>
											<td style="width:10%" class="text-end fw-700">@{{ data.prix_unitaire * data.qte }}</td>
										</tr>
									
										<tr v-if="cart.length">
											<th class="bt-1">Total</th>
											<th class="bt-1"></th>
											<th class="bt-1 text-end fw-900 fs-18">@{{ totalGlobal }}</th>
										</tr>
									</tbody>
								</table>
							</div>

							<div v-else class="d-flex justify-content-center align-items-center flex-column">
								<span class="text-bg-secondary icon-Cart2 fs-140 opacity-50"><span class="path1"></span><span class="path2"></span></span>
								<h4 class="mt-3 text-uppercase fw-700">Panier vide !</h4>
							</div>
						</div>
						<div class="box-footer">
							<button class="btn btn-danger">Annuler</button>
							<button class="btn btn-success pull-right"> <i class="fa fa-arrow-right me-1"></i> Valider la commande</button>
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
    <script type="module" src="{{ asset("assets/js/scripts/service.js") }}"></script>
@endpush
