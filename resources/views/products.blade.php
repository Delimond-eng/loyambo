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
                            <h4 class="box-title">Gestion des Produits
                                <small class="subtitle">Inventaire Global & Tarification par Emplacement</small>
                            </h4>
                            <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#productModal" class="btn btn-primary btn-sm text-center btn-rounded">+ Nouveau produit</a>
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
                                            <th>Stock Global</th>
                                            <th>unité</th>
                                            <th>Quantifiable</th>
                                            <th>TVA</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(data, index) in allProducts" :key="index">
                                            <td>@{{data.reference }}</td>
                                            <td><h6 class="mb-0 fw-bold">@{{ data.libelle }}</h6></td>
                                            <td><span class="badge badge-light">@{{ data.categorie ? data.categorie.libelle : '---' }}</span></td>
                                            <td>@{{ data.prix_unitaire }} </td>
                                            <td>
                                                <h6 class="mb-0 fw-bold" :class="data.stock_actuel <= data.seuil_reappro ? 'text-danger' : 'text-success'">
                                                    @{{ data.stock_actuel ?? 0 }} @{{ data.unite }}
                                                </h6>
                                            </td>
                                            <td>@{{ data.unite ?? "----" }}</td>
                                            <td>
                                                <div class="demo-checkbox m-0 p-0">
                                                    <input type="checkbox" @change="updateQuantified(data, $event)" :id="`md_checkbox_${data.id}`" class="filled-in chk-col-succes m-0 p-0" :checked="data.quantified">
                                                    <label :for="`md_checkbox_${data.id}`"></label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="demo-checkbox m-0 p-0">
                                                    <input type="checkbox" @change="updateTva(data, $event)" :id="`md_checkbox_tva${data.id}`" class="filled-in chk-col-success m-0 p-0" :checked="data.tva">
                                                    <label :for="`md_checkbox_tva${data.id}`"></label>
                                                </div>
                                            </td>
                                             <td class="text-center">
                                                <div class="d-flex justify-content-center">
                                                    <button type="button" class="btn btn-primary-light btn-xs me-1" data-bs-toggle="modal" data-bs-target="#productModal"  @click="editProduct(data)"><i class="mdi mdi-pencil"></i></button>
                                                    <button type="button" class="btn btn-danger-light btn-xs" @click="supprimerProduit(data)"><i class="mdi mdi-delete"></i></button>
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

            <!-- Popup Model Product -->
            <div id="productModal" class="modal fade in" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <form class="modal-content" @submit.prevent="submitProduct">
                        <div class="modal-header border-bottom">
                            <h4 class="modal-title" id="myModalLabel">Fiche Produit & Tarifs</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <!-- Colonne Informations Produits -->
                                <div class="col-md-5 border-end">
                                    <h5 class="fw-bold mb-3 text-primary">Configuration de base</h5>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Code barre</label>
                                            <input type="text" class="form-control bg-light" v-model="formProduct.code_barre" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Référence</label>
                                            <input type="text" class="form-control bg-light" v-model="formProduct.reference" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label">Désignation du produit</label>
                                        <input type="text" class="form-control" placeholder="Nom du produit" v-model="formProduct.libelle" required>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Prix unitaire</label>
                                            <input type="number" class="form-control" placeholder="0" v-model="formProduct.prix_unitaire" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Catégorie</label>
                                            <select class="form-control" v-model="formProduct.categorie_id" required>
                                                <option value="" disabled>Choisir...</option>
                                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">@{{ cat.libelle }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Unité</label>
                                            <select class="form-control" v-model="formProduct.unite">
                                                <option value="pce">Pièce</option>
                                                <option value="bouteille">Bouteille</option>
                                                <option value="cannette">Cannette</option>
                                                <option value="boite">Boite</option>
                                                <option value="kg">Kg</option>
                                                <option value="litre">Litre</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Seuil d'alerte</label>
                                            <input type="number" class="form-control" v-model="formProduct.seuil_reappro" placeholder="5">
                                        </div>
                                    </div>

                                    <!-- STOCK INITIAL GLOBAL -->
                                    <div class="bg-primary-light p-3 rounded border border-primary mb-3" v-if="!formProduct.id">
                                        <h6 class="fw-bold text-primary mb-2"><i class="ti-package me-2"></i>Stock Initial Global</h6>
                                        <div class="row">
                                            <div class="col-md-7">
                                                <label class="small text-muted">Quantité disponible à la création</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" v-model="formProduct.qte_init" placeholder="0">
                                                    <span class="input-group-text">@{{ formProduct.unite }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-5" v-if="formProduct.qte_init">
                                                <label class="small text-muted">Emplacement</label>
                                                <select class="form-select" v-model="formProduct.emplacement_id">
                                                    <option value="">--Choisir--</option>
                                                    <option v-for="emp in emplacements" :key="emp.id" :value="emp.id">@{{ emp.libelle }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-3 mt-3">
                                        <div class="demo-checkbox">
                                            <input type="checkbox" v-model="formProduct.quantified" id="check_quantified" class="filled-in chk-col-primary">
                                            <label for="check_quantified">Gérer le stock</label>
                                        </div>
                                        <div class="demo-checkbox">
                                            <input type="checkbox" v-model="formProduct.tva" id="check_tva" class="filled-in chk-col-primary">
                                            <label for="check_tva">TVA</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Colonne Prix par Emplacement -->
                                <div class="col-md-7">
                                    <h5 class="fw-bold mb-3 text-success">Tarification par Point de Vente</h5>
                                    <p class="text-muted small">Définissez le prix auquel ce produit sera vendu dans chaque emplacement.</p>

                                    <div class="table-responsive" style="max-height: 400px;">
                                        <table class="table table-hover border">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Emplacement</th>
                                                    <th style="width: 200px;">Prix de vente (F)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="emp in emplacements" :key="emp.id">
                                                    <td class="align-middle">
                                                        <span class="fw-bold text-dark">@{{ emp.libelle }}</span><br>
                                                        <span class="badge badge-secondary-light fs-10">@{{ emp.type }}</span>
                                                    </td>
                                                    <td>
                                                        <div class="input-group">
                                                            <input type="number" class="form-control border-success fw-bold text-end"
                                                                placeholder="0"
                                                                :value="getLocationPrice(emp.id)"
                                                                @input="updateLocationPrice(emp.id, $event.target.value)">
                                                            <span class="input-group-text bg-success text-white border-success">F</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer d-flex justify-content-end border-top">
                            <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Fermer</button>
                            <button type="submit" :disabled="isLoading" class="btn btn-primary px-4">
                                <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span>
                                <i class="fa fa-save me-1"></i> Enregistrer le produit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
		</section>
	  </div>
  </div>
@endsection

@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/product.js") }}"></script>
@endpush
