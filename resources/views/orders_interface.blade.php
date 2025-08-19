@extends("layouts.admin")

@section("content")
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
	  <div class="container-full AppService">
		<!-- Content Header (Page header) -->
		<div class="content-header">
			<div class="d-flex align-items-center">
				<div class="me-auto">
					<h3 class="page-title">Bon de commande Table 03</h3>
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
						<div class="box-header bg-dark p-3">
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
									<a href="#" class="box box-shadowed text-center">
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