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
			<div class="row d-flex justify-content-center align-items-center g-4">
                <div class="col-xl-12">
                    @include("components.menus.products")
                </div>
                <div class="col-md-5">
                    <form class="box" @submit.prevent="submitStockMvt">
                        <div class="box-header d-flex justify-content-between align-items-center" style="padding : 1.5rem">
                            <h4 class="box-title">Approvisionnement stock des produits
                                <small class="subtitle text-danger">Veuillez créer une entrée dans l'emplacement récommandé !</small>
                            </h4>
                        </div>
                        <div class="box-body">
                            <div class="form-horizontal">
                                <div class="form-group">
                                    <label class="form-label">Produit <sup class="text-danger">*</sup></label>
                                    <div class="input-group mb-2">
                                        <select class="form-control select2">
                                            <option value="" selected="selected" hidden></option>
                                            @foreach ($produits as $prod)
                                            <option value="{{ $prod->id }}">{{ $prod->libelle }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Emplacement <sup class="text-danger">*</sup></label>
                                    <div class="input-group mb-2">
                                        <span class="input-group-text bg-transparent"><i
                                                class="ti-home text-primary"></i></span>
                                        <select class="form-control ps-15 bg-transparent" v-model="formEntree.emplacement_id">
                                            <option value="" selected hidden label="--Sélectionner l'emplacement du stock--"></option>
                                            @foreach ($emplacements as $emp )
                                            <option value="{{ $emp->id }}">{{ $emp->libelle }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Quantité <sup class="text-danger">*</sup></label>
                                    <div class="input-group mb-2">
                                        <span class="input-group-text bg-transparent"><i
                                                class="ti-shopping-cart-full text-primary"></i></span>
                                        <input type="number" class="form-control ps-15 bg-transparent" v-model="formEntree.quantite" placeholder="Saisir la qté à entrée. ex: 10" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary me-1" :disabled="isLoading">
                                <i class="ti-save-alt"></i> Enregistrer <span v-if="isLoading" class="spinner-border spinner-border-sm ms-2"></span>
                            </button>

                            <button type="button" @click="resetAll" class="btn btn-warning">
                                <i class="mdi mdi-cancel"></i> Annuler
                            </button>
                        </div>
                    </form>
                </div>
		    </div>
		</section>
		<!-- /.content -->
	  </div>
  </div>
  <!-- /.content-wrapper -->
@endsection

@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/product.js") }}"></script>
@endpush
