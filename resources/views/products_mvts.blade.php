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
                <div class="col-md-3">
                    <form class="box" @submit.prevent="submitMvt">
                        <div class="box-header d-flex justify-content-between align-items-center" style="padding : 1.5rem">
                            <h4 class="box-title">Création nouveau mouvement
                                <small class="subtitle">Effectuez un mouvement stock</small>
                            </h4>
                        </div>
                        <div class="box-body">
                            <div class="form-horizontal">
                                <div class="form-group">
                                    <label class="form-label">Produit concerné</label>
                                    <div class="input-group">
                                        <select class="form-control select2">
                                            <option value="" selected="selected" hidden></option>
                                            @foreach ($produits as $prod)
                                            <option value="{{ $prod->id }}">{{ $prod->libelle }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Date mouvement</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent"><i
                                                class="ti-calendar text-primary"></i></span>
                                        <input v-model="formMvt.date_mouvement" type="date" class="form-control ps-15 bg-transparent">
                                    </div>
                                </div>

                                <div class="form-group" v-if="formMvt.type_mouvement !== 'vente'">
                                    <label class="form-label">Type de Mouvement</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent"><i
                                                class="ti-layout-accordion-separated text-primary"></i></span>
                                        <select :disabled="disabled" class="form-control ps-15 bg-transparent" v-model="formMvt.type_mouvement" required>
                                            <option value="" selected hidden label="Type de mouvement"></option>
                                            <option value="entrée">Entrée</option>
                                            <option value="sortie">Sortie</option>
                                            <option value="transfert">transfert</option>
                                            <option value="ajustement">Ajustement </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group" v-if="(formMvt.type_mouvement !== 'vente' && formMvt.type_mouvement !== 'ajustement' && formMvt.type_mouvement !== 'sortie' && formMvt.type_mouvement !== 'entrée') || formMvt.type_mouvement === 'transfert'">
                                    <label class="form-label">Emplacement Source</label>
                                    <div class="input-group">
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

                                <div class="form-group" v-if="(formMvt.type_mouvement !== 'vente' && formMvt.type_mouvement !== 'ajustement') || formMvt.type_mouvement === 'entrée' || formMvt.type_mouvement === 'sortie'">
                                    <label class="form-label">Emplacement Déstination</label>
                                    <div class="input-group">
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
                                    <label class="form-label">Numéro bon</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent"><i
                                                class="ti-ticket text-primary"></i></span>
                                        <input type="text" :disabled="formMvt.type_mouvement === 'vente'" class="form-control ps-15 bg-transparent" v-model="formMvt.numdoc" placeholder="Saisir le numéro bon" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Quantité</label>
                                    <div class="input-group">
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

                            <ul class="nav nav-tabs justify-content-end" role="tablist">
                                <li class="nav-item"> <a :class="{'active': filter_date1 === '' && filter_date2 === '' }" class="nav-link" @click="filterByType('')" data-bs-toggle="tab" href="#home13" role="tab"><span class="hidden-sm-up"><i class="ion-home"></i></span> <span class="hidden-xs-down">Tout</span></a> </li>
                                <li class="nav-item"> <a class="nav-link"  @click="filterByType('vente')"data-bs-toggle="tab" href="#profile13" role="tab"><span class="hidden-sm-up"><i class="ion-person"></i></span> <span class="hidden-xs-down">Vente</span></a> </li>
                                <li class="nav-item"> <a class="nav-link" @click="filterByType('entrée')" data-bs-toggle="tab" href="#messages13" role="tab"><span class="hidden-sm-up"><i class="ion-email"></i></span> <span class="hidden-xs-down">Entrée</span></a> </li>
                                <li class="nav-item"> <a class="nav-link" @click="filterByType('sortie')" data-bs-toggle="tab" href="#messages13" role="tab"><span class="hidden-sm-up"><i class="ion-email"></i></span> <span class="hidden-xs-down">Sortie</span></a> </li>
                                <li class="nav-item"> <a class="nav-link" @click="filterByType('transfert')" data-bs-toggle="tab" href="#messages13" role="tab"><span class="hidden-sm-up"><i class="ion-email"></i></span> <span class="hidden-xs-down">Transfert</span></a> </li>
                                <li class="nav-item"> <a class="nav-link" @click="filterByType('ajustement')" data-bs-toggle="tab" href="#messages13" role="tab"><span class="hidden-sm-up"><i class="ion-email"></i></span> <span class="hidden-xs-down">Ajustement</span></a> </li>
                            </ul>
                        </div>
                        <div class="box-body">
                            <div class="form-group row">
                                <label for="example-text-input" class="col-sm-6 col-form-label">Filtrer par date mouvement</label>
                                <div class="col-sm-3">
                                    <input class="form-control" @input="viewAllStockMvts" v-model="filter_date1" type="date">
                                </div>
                                <div class="col-sm-3">
                                    <input class="form-control" @input="viewAllStockMvts" v-model="filter_date2" type="date">
                                </div>
                            </div>
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
                                            <th>Emplacement</th>
                                            <th>Utilisateurs</th>
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
                                                <span class="badge" 
                                                :class="{
                                                    'badge-warning-light':data.type_mouvement==='sortie',
                                                    'badge-danger-light':data.type_mouvement==='ajustement',
                                                    'badge-primary-light':data.type_mouvement==='vente',
                                                    'badge-success-light':data.type_mouvement==='entrée',
                                                }">
                                                @{{ data.type_mouvement }}
                                            </span>
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
                                                <span>@{{ data.emplacement.libelle }}</span>
                                            </td>
                                            <td>
                                                <h6 class="mb-0 fw-bold" v-if="data.user">@{{ data.user.name }}</h6>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex">
                                                    <button type="button" class="btn btn-primary-light btn-xs me-1" @click="editMvt(data)"><i class="mdi mdi-pencil"></i></button>
                                                    <button v-if="data.type_mouvement !== 'ajustement' || data.type_mouvement === 'vente'" type="button" @click="deleteMvt(data)" class="btn btn-danger-light btn-xs">
                                                        <i class="spinner-border spinner-border-sm" v-if="load_id===data.id"></i>
                                                        <i class="mdi mdi-delete" v-else></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        <tr v-if="mouvements.length === 0">
                                            <td colspan="10" class="text-center py-4">
                                                <div class="py-50" v-if="isDataLoading">
                                                    <span class="spinner-border"></span>
                                                </div>
                                                <div v-else class="text-muted" >
                                                    <i class="fa fa-folder-open fa-2x mb-2"></i>
                                                    <p>
                                                        Aucun mouvement stock trouvé.
                                                    </p>

                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <Paginator
                                :current-page="pagination.current_page"
                                :last-page="pagination.last_page"
                                :total-items="pagination.total"
                                :per-page="pagination.per_page"
                                @page-changed="changePage"
                                @per-page-changed="onPerPageChange">
                            </Paginator>
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
    <script>
        $('#slimtest4').slimScroll({
            color: '#0bb2d4'
            ,size: '10px'
            ,height: '400px',
            alwaysVisible: true
        });
    </script>
@endpush
