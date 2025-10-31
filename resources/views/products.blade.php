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
			<div class="row g-4">
                <div class="col-xl-12">
                    @include("components.menus.products")
                </div>
                <div class="col-xl-12">
                    <div class="box">
                        <div class="box-header d-flex justify-content-between align-items-center" style="padding : 1.5rem">
                            <h4 class="box-title">Les produits
                                <small class="subtitle">Listes des produits</small>
                            </h4>
<<<<<<< HEAD
                            <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#productModal" class="btn btn-primary btn-sm text-center btn-rounded">+ Nouveau produit</a>					
=======
                            <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#productModal" class="btn btn-primary btn-sm text-center btn-rounded" @click="resetAll">+ Nouveau produit</a>					
>>>>>>> 07123be (31/10/2025)
                        </div>
                        <div class="box-body">
                            <div class="table-responsive">

                                <table id="table-product" class="table table-lg invoice-archive">
                                    <thead>
                                        <tr>
                                            <th>REF</th>
                                            <th>Libellé</th>
                                            <th>Catégorie</th>
<<<<<<< HEAD
=======
                                            <th>Emplacement</th>
>>>>>>> 07123be (31/10/2025)
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
<<<<<<< HEAD
                                            <td>@{{data.reference }}</td>
=======
                                            <td>@{{ data.reference }}</td>
>>>>>>> 07123be (31/10/2025)
                                            <td>
                                                <h6 class="mb-0">
                                                    <a href="#">@{{ data.libelle }}</a>
                                                </h6>
                                            </td>
                                            <td>
                                                <h6 v-if="data.categorie" class="mb-0">
                                                    <a href="#">@{{ data.categorie.libelle }}</a>
                                                </h6>
                                                <span v-else class="text-muted">---</span>
                                            </td>
                                            <td>
                                                <h6 v-if="data.emplacement" class="mb-0">
                                                    @{{ data.emplacement.libelle }}
                                                </h6>
                                                <span v-else class="text-muted">---</span>
                                            </td>
                                            <td>
                                                @{{ data.prix_unitaire }}
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
<<<<<<< HEAD
                                                    <button type="button" class="btn btn-primary btn-xs me-1"><i class="mdi mdi-view-grid"></i></button>
                                                    <button type="button" class="btn btn-primary-light btn-xs me-1" data-bs-toggle="modal" data-bs-target="#productModal"  @click="formProduct = data"><i class="mdi mdi-pencil"></i></button>
                                                    <button type="button" class="btn btn-danger-light btn-xs"><i class="mdi mdi-delete"></i></button>
=======
                                                    
                                                    <button type="button" class="btn btn-primary-light btn-xs me-1" data-bs-toggle="modal" data-bs-target="#productModal"  @click="formProduct = {...data}"><i class="mdi mdi-pencil"></i></button>
                                                   <button 
            type="button" 
            class="btn btn-danger-light btn-xs"
            @click="supprimerProduit(data)"
            :disabled="isLoading"
            title="Supprimer le produit"
        >
            <i class="mdi mdi-delete"></i>
        </button>
>>>>>>> 07123be (31/10/2025)
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
<<<<<<< HEAD
                            <h4 class="modal-title" id="myModalLabel">Création nouveau produit</h4>
=======
                            <h4 class="modal-title" id="myModalLabel">@{{ formProduct.id ? 'Modifier le produit' : 'Création nouveau produit' }}</h4>
>>>>>>> 07123be (31/10/2025)
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-horizontal">
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent"><i
                                                class="ti-bookmark-alt text-primary"></i></span>
<<<<<<< HEAD
                                        <input type="text" class="form-control ps-15 bg-transparent"
                                            placeholder="BARCODE" v-model="formProduct.code_barre">
=======
                                       <input type="text" class="form-control ps-15 bg-transparent"
                                         placeholder="BARCODE" v-model="formProduct.code_barre" readonly>
>>>>>>> 07123be (31/10/2025)
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent"><i
                                                class=" ti-harddrive text-primary"></i></span>
                                        <input type="text" class="form-control ps-15 bg-transparent"
<<<<<<< HEAD
                                            placeholder="Réference" v-model="formProduct.reference">
=======
                                          placeholder="Réference" v-model="formProduct.reference" readonly>
>>>>>>> 07123be (31/10/2025)
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
<<<<<<< HEAD
                                            placeholder="Prix unitaire" v-model="formProduct.prix_unitaire" required>
=======
                                            placeholder="Prix unitaire" v-model="formProduct.prix_unitaire" required step="0.01">
>>>>>>> 07123be (31/10/2025)
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent"><i
                                                class="ti-layout-accordion-separated text-primary"></i></span>
<<<<<<< HEAD
                                        <select class="form-control ps-15 bg-transparent" v-model="formProduct.categorie_id">
                                            <option value="" selected hidden label="Catégorie"></option>
=======
                                        <select class="form-control ps-15 bg-transparent" v-model="formProduct.categorie_id" required>
                                            <option value="" selected hidden>Catégorie</option>
>>>>>>> 07123be (31/10/2025)
                                            @foreach ($categories as $cat )
                                            <option value="{{ $cat->id }}">{{ $cat->libelle }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

<<<<<<< HEAD
=======
                                <!-- Section Emplacement -->
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent">
                                            <i class="ti-location-pin text-primary"></i>
                                        </span>
                                        <select class="form-control ps-15 bg-transparent" v-model="formProduct.emplacement_id" required>
                                            <option value="" selected hidden>Choisir un emplacement</option>
                                            <option v-for="emp in emplacements" :key="emp.id" :value="emp.id">
                                                @{{ emp.libelle }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <!-- Fin Section Emplacement -->

>>>>>>> 07123be (31/10/2025)
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                <span class="input-group-text bg-transparent"><i
                                                        class="ti-shopping-cart text-primary"></i></span>
                                                <input type="number" class="form-control ps-15 bg-transparent"
<<<<<<< HEAD
                                                    placeholder="Seuil réappro." v-model="formProduct.seuil_reappro">
=======
                                                    placeholder="Seuil réappro." v-model="formProduct.seuil_reappro" min="0">
>>>>>>> 07123be (31/10/2025)
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <div class="input-group mb-3">
                                                <span class="input-group-text bg-transparent"><i
                                                        class="ti-shopping-cart-full text-primary"></i></span>
                                                <input type="number" class="form-control ps-15 bg-transparent"
<<<<<<< HEAD
                                                    placeholder="Qté initial" v-model="formProduct.qte_init">
=======
                                                    placeholder="Qté initial" v-model="formProduct.qte_init" min="0">
>>>>>>> 07123be (31/10/2025)
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
<<<<<<< HEAD
                                                    <option value="" selected hidden label="Unité"></option>
=======
                                                    <option value="" selected hidden>Unité</option>
>>>>>>> 07123be (31/10/2025)
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
<<<<<<< HEAD
                            <button type="submit" :disabled="isLoading" class="btn btn-success btn-block"><span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span>Enregistrer</button>
=======
                            <button type="submit" :disabled="isLoading" class="btn btn-success btn-block">
                                <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span>
                                @{{ formProduct.id ? 'Modifier' : 'Enregistrer' }}
                            </button>
>>>>>>> 07123be (31/10/2025)
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
<<<<<<< HEAD

    
@endsection

@push("scripts")
    @push("scripts")
        <script type="module" src="{{ asset("assets/js/scripts/product.js") }}"></script>
    @endpush
@endpush
=======
@endsection

@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/product.js") }}"></script>
@endpush
>>>>>>> 07123be (31/10/2025)
