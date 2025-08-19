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
                <div class="col-md-3">
                    <form class="box" @submit.prevent="submitStockMvt">
                        <div class="box-header d-flex justify-content-between align-items-center" style="padding : 1.5rem">
                            <h4 class="box-title">Bon NO.14
                                <small class="subtitle">Effectuez un mouvement stock</small>
                            </h4>
                        </div>
                        <div class="box-body">
                            <div class="form-horizontal">
                                <div class="form-group">
                                    <label class="form-label">Produit concerné</label>
                                    <div class="input-group mb-3">
                                        <select class="form-control select2">
                                            <option value="" selected="selected" hidden></option>
                                            @foreach ($produits as $prod)
                                            <option value="{{ $prod->id }}">{{ $prod->libelle }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent"><i
                                                class="ti-calendar text-primary"></i></span>
                                        <input v-model="formMvt.date_mouvement" type="date" class="form-control ps-15 bg-transparent">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent"><i
                                                class="ti-layout-accordion-separated text-primary"></i></span>
                                        <select class="form-control ps-15 bg-transparent" v-model="formMvt.type_mouvement" required>
                                            <option value="" selected hidden label="Type de mouvement"></option>
                                            <option value="entrée">Entrée</option>
                                            <option value="sortie">Sortie</option>
                                            <option value="transfert">transfert</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent"><i
                                                class="ti-arrow-left text-primary"></i></span>
                                        <select class="form-control ps-15 bg-transparent" v-model="formMvt.source">
                                            <option value="" selected hidden label="Source"></option>
                                            @foreach ($emplacements as $emp )
                                            <option value="{{ $emp->id }}">{{ $emp->libelle }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent"><i
                                                class="ti-arrow-right text-primary"></i></span>
                                        <select class="form-control ps-15 bg-transparent"  v-model="formMvt.destination">
                                            <option value="" selected hidden label="Destination"></option>
                                            @foreach ($emplacements as $emp )
                                            <option value="{{ $emp->id }}">{{ $emp->libelle }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent"><i
                                                class="ti-shopping-cart-full text-primary"></i></span>
                                        <input type="number" class="form-control ps-15 bg-transparent" v-model="formMvt.quantite" placeholder="Saisir la qté. ex: 10" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary me-1" :disabled="isLoading">
                                <i class="ti-save-alt"></i> Enregistrer <span v-if="isLoading" class="spinner-border spinner-border-sm ms-2"></span>
                            </button>

                            <button type="button" @click="resetAll" class="btn btn-warning">
                                <i class="ti-trash"></i> Annuler
                            </button>
                        </div>
                    </form>
                </div>

                <div class="col-md-9">
                    <div class="box">
                        <div class="box-header d-flex justify-content-between align-items-center" style="padding : 1.5rem">
                            <h4 class="box-title">Mouvements stock
                                <small class="subtitle">Tous les mouvements de stock</small>
                            </h4>
                        </div>
                        <div class="box-body">
                            <div class="table-responsive">
                                <table id="example" class="table table-lg invoice-archive">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Produit</th>
                                            <th>Type Mvt</th>
                                            <th>Quantité</th>
                                            <th>Source</th>
                                            <th>Destination</th>
                                            <th>Utilisateur</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(data, index) in mouvements" :key="index">
                                            <td>@{{ formateSimpleDate(data.date_mouvement)}}</td>
                                            <td>
                                                <h6 class="mb-0" v-if="data.produit">
                                                    @{{ data.produit.libelle }}
                                                </h6>
                                            </td>
                                            <td>
                                                 @{{ data.type_mouvement }}
                                            </td>
                                            <td>
                                                @{{ data.quantite }}
                                            </td>
                                            <td>
                                                <span v-if="data.prov">@{{ data.prov.libelle }}</span>
                                                <span v-else>Non définie</span>
                                            </td>
                                            <td>
                                                <span v-if="data.dest">@{{ data.dest.libelle }}</span>
                                                <span v-else>Non définie</span>
                                            </td>
                                            <td>
                                                <h6 class="mb-0 fw-bold" v-if="data.user">@{{ data.user.name }}</h6>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex">
                                                    <button type="button" class="btn btn-primary btn-xs me-1"><i class="mdi mdi-printer"></i></button>
                                                    <button type="button" class="btn btn-primary-light btn-xs me-1" @click="editMvt(data)"><i class="mdi mdi-pencil"></i></button>
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
		</section>
		<!-- /.content -->
	  </div>
  </div>
  <!-- /.content-wrapper -->
@endsection

@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/product.js") }}"></script>
@endpush
