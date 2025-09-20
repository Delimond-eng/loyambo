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
			<div class="row">
                <div class="col-xl-12">
                    <div class="box">
                        <div class="box-header d-flex justify-content-between align-items-center" style="padding : 1.5rem">
                            <h4 class="box-title">Les produits
                                <small class="subtitle">Listes des produits</small>
                            </h4>
                            <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#productModal" class="btn btn-primary text-center btn-rounded">+ Nouveau produit</a>					
                        </div>
                        <div class="box-body">
                            <div class="table-responsive">

                                <table id="table-product" class="table table-lg invoice-archive">
                                    <thead>
                                        <tr>
                                            <th>REF</th>
                                            <th>Libellé</th>
                                            <th>Catégorie</th>
                                            <th>Prix unitaire</th>
                                            <th>Qté Initial</th>
                                            <th>unité</th>
                                            <th>Quantifiable</th>
                                            <th>statut</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(data, index) in allProducts" :key="index">
                                            <td>@{{data.reference }}</td>
                                            <td>
                                                <h6 class="mb-0">
                                                    <a href="#">@{{ data.libelle }}</a>
                                                </h6>
                                            </td>
                                            <td>
                                                <h6 v-if=" data.categorie" class="mb-0">
                                                    <a href="#">@{{ data.categorie.libelle }}</a>
                                                </h6>
                                            </td>
                                            <td>
                                                @{{ data.prix_unitaire}}
                                            </td>
                                            
                                            <td>
                                                <h6 class="mb-0 fw-bold">@{{ data.qte_init }}</h6>
                                            </td>
                                            <td>
                                                <h6 class="mb-0 fw-bold">@{{ data.unite ?? "----" }}</h6>
                                            </td>
                                            <td>
                                                <div class="demo-checkbox m-0 p-0">
                                                    <input type="checkbox" @change="updateQuantified(data, $event)" :id="`md_checkbox_${data.id}`" class="filled-in chk-col-success" :checked="data.quantified">
                                                    <label :for="`md_checkbox_${data.id}`">Quantifiable</label>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-pill badge-success">actif</span>
                                            </td>

                                             <td class="text-center">
                                                <div class="d-flex">
                                                    <button type="button" class="btn btn-primary btn-xs me-1"><i class="mdi mdi-view-grid"></i></button>
                                                    <button type="button" class="btn btn-primary-light btn-xs me-1" data-bs-toggle="modal" data-bs-target="#productModal"  @click="formProduct = data"><i class="mdi mdi-pencil"></i></button>
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
            <div id="productModal" class="modal fade in" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form class="modal-content" @submit.prevent="submitProduct">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">Création nouveau produit</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-horizontal">
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent"><i
                                                class="ti-bookmark-alt text-primary"></i></span>
                                        <input type="text" class="form-control ps-15 bg-transparent"
                                            placeholder="BARCODE" v-model="formProduct.code_barre">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent"><i
                                                class=" ti-harddrive text-primary"></i></span>
                                        <input type="text" class="form-control ps-15 bg-transparent"
                                            placeholder="Réference" v-model="formProduct.reference">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent"><i
                                                class="ti-panel text-primary"></i></span>
                                        <input type="text" class="form-control ps-15 bg-transparent"
                                            placeholder="Libellé" v-model="formProduct.libelle" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent"><i
                                                class="ti-money text-primary"></i></span>
                                        <input type="number" class="form-control ps-15 bg-transparent"
                                            placeholder="Prix unitaire" v-model="formProduct.prix_unitaire" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent"><i
                                                class="ti-layout-accordion-separated text-primary"></i></span>
                                        <select class="form-control ps-15 bg-transparent" v-model="formProduct.categorie_id">
                                            <option value="" selected hidden label="Catégorie"></option>
                                            @foreach ($categories as $cat )
                                            <option value="{{ $cat->id }}">{{ $cat->libelle }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                <span class="input-group-text bg-transparent"><i
                                                        class="ti-shopping-cart text-primary"></i></span>
                                                <input type="number" class="form-control ps-15 bg-transparent"
                                                    placeholder="Seuil réappro." v-model="formProduct.seuil_reappro">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                <span class="input-group-text bg-transparent"><i
                                                        class="ti-shopping-cart-full text-primary"></i></span>
                                                <input type="number" class="form-control ps-15 bg-transparent"
                                                    placeholder="Qté initial" v-model="formProduct.qte_init">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                <span class="input-group-text bg-transparent"><i
                                                        class="ti-layout-accordion-separated text-primary"></i></span>
                                                <select class="form-control ps-15 bg-transparent" v-model="formProduct.unite">
                                                    <option value="" selected hidden label="Unité"></option>
                                                    <option value="boite">Boite</option>
                                                    <option value="bouteille">Bouteille</option>
                                                    <option value="cannete">Cannette</option>
                                                    <option value="carton">Carton</option>
                                                    <option value="kg">Kg</option>
                                                    <option value="paquet">Paquet</option>
                                                    <option value="pce">Pce</option>
                                                    <option value="sachet">Sachet</option>
                                                    <option value="filet">Filet</option>
                                                    <option value="pots">Pots</option>
                                                    <option value="litre">Litre</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="demo-checkbox mt-2">
                                            <input type="checkbox" v-model="formProduct.quantified" id="basic_checkbox_2" class="filled-in">
                                            <label for="basic_checkbox_2">Quantifiable</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer d-flex">
                            <button type="submit" :disabled="isLoading" class="btn btn-success btn-block"><span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span>Enregistrer</button>
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
    @push("scripts")
        <script type="module" src="{{ asset("assets/js/scripts/product.js") }}"></script>
    @endpush
@endpush
