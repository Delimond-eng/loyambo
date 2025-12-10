@extends("layouts.admin")

@section("content")
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
	  <div class="container-full">
		<!-- Content Header (Page header) -->
		<div class="content-header">
		</div>
		<!-- Main content -->
		<section class="content" id="AppInventory" v-cloak>
			<div class="row g-4">
                <div class="col-xl-12">
                    @include("components.menus.products")
                </div>

                <div class="col-md-12">
                    <div class="box">
                        <div class="box-header with-border text-center p-3">
                            <h4 class="box-title">Inventaire des produits</h4>
                            <h6 class="box-subtitle">Chaque inventaire doit concerner un emplacement bien spécifique !</h6>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs nav-fill" role="tablist">
                                <li class="nav-item"> <a class="nav-link active" data-bs-toggle="tab" href="#histories" role="tab"><span><i class="ion-folder"></i></span> <span class="hidden-xs-down ms-15">Historique des inventaires</span></a> </li>
                                <li class="nav-item"> <a class="nav-link" data-bs-toggle="tab" href="#inventories" role="tab"><span><i class="ion-plus"></i></span> <span class="hidden-xs-down ms-15">Nouvel inventaire</span></a> </li>
                            </ul>
                            <!-- Tab panes -->
                            <div class="tab-content">
                                <div class="tab-pane active" id="histories" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-striped no-border">
                                        <thead>
                                            <tr class="bb-3 border-primary">
                                                <th>Date début</th>
                                                <th>Date Fin</th>
                                                <th>Gérant</th>
                                                <th>Emplacement</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(data, index) in allInventories">
                                                <th scope="row">@{{ data.date_debut }}</th>
                                                <th scope="row">@{{ data.date_fin }}</th>
                                                <td>@{{data.admin.name }}</td>
                                                <td>@{{data.emplacement.libelle }}</td>
                                                
                                                <td><span class="badge badge-pill" :class="{'badge-warning': data.status === 'pending', 'badge-success': data.status === 'closed'}"> @{{getStatus(data.status) }}</span></td>
                                                <td>
                                                    <div class="d-flex">
                                                        <button type="button"  class="btn btn-primary btn-xs me-1"><i class="fa fa-eye"></i></button>
                                                        <button type="button" class="btn btn-info btn-xs me-1"><i class="mdi mdi-delete"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr v-if="allInventories.length === 0">
                                                <td colspan="10" class="text-center py-4">
                                                    <div class="py-50" v-if="isDataLoading">
                                                        <span class="spinner-border"></span>
                                                    </div>
                                                    <div v-else class="text-muted" >
                                                        <i class="fa fa-folder-open fa-2x mb-2"></i>
                                                        <p>
                                                            Aucune historique d'inventaire répertorié !
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
                                <div class="tab-pane" id="inventories" role="tabpanel">
                                    <div class="p-20">
                                        <div class="row d-flex justify-content-center" v-if="!currentInventory">
                                            <div class="col-md-12">
                                                <div class="text-center p-50">
                                                    <div v-if="!isDataLoading">
                                                        <p class="mb-1">
                                                            <img style="width:100px" src="{{ asset("assets/images/inventory.png") }}" alt="">
                                                        </p>
                                                        <p class="fs-14">Cliquez pour commencer un nouvel inventaire !</p>
                                                        @if (Auth::user()->role=="admin")
                                                        <button :disabled="isLoading" @click.prevent="openStartModal" class="btn btn-primary btn-xs"> <i class="mdi mdi-plus"></i> Commencer inventaire <span v-if="isLoading"
                                                                class="spinner-border spinner-border-sm ms-2"
                                                                style="height:12px; width:12px"></span></button>
                                                        @endif
                                                    </div>
                                                    <div v-else>
                                                        <div class="d-flex justify-content-center align-items-center p-5">
                                                            <span class="spinner-border text-primary"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                        <div class="row" v-else>
                                            <div class="col-xl-4">
                                                <div class="input-group mb-2">
                                                    <span class="input-group-text bg-transparent"><i
                                                        class="ti-search text-primary"></i>
                                                    </span>
                                                    <input type="text" class="form-control" v-model="search" placeholder="Recherche produit...">
                                                </div>
                                                <ul class="list-group list-group-flush">
                                                    <li class="list-group-item"
                                                        v-for="(data, i) in allProducts"
                                                        :key="i">
                                                        <div class="demo-checkbox">
                                                            <label :for="`Checked-${data.id}`" class="mb-0 fw-bold">
                                                                @{{ data.libelle }}
                                                            </label>
                                                            <input
                                                                class="filled-in chk-col-primary"
                                                                type="checkbox"
                                                                :id="`Checked-${data.id}`"
                                                                :checked="selectedProductIds.includes(data.id)"
                                                                @change="toggleProductSelection(data)"
                                                            >
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="col-xl-8">
                                                <div class="table-responsive">
                                                    <table class="table text-nowrap table-hover">
                                                        <thead>
                                                            <tr class="bb-3 border-primary">
                                                                <th scope="col">#</th>
                                                                <th scope="col">Produit</th>
                                                                <th scope="col">Qté théorique</th>
                                                                <th scope="col">Qté physique</th>
                                                                <th scope="col">Ecart</th>
                                                                <th scope="col">Valeur</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr v-for="(line, i) in inventoryLines" :key="line.id">
                                                                <td>@{{ i + 1 }}</td>
                                                                <td>@{{ line.libelle }}</td>
                                                                <td>@{{ line.stock_global ?? 0 }}</td>
                                                                <td>
                                                                    <input
                                                                        type="number"
                                                                        placeholder="0"
                                                                        class="form-control form-control-sm w-150"
                                                                        v-model.number="line.real_quantity" @input="() => {}">
                                                                </td>
                                                                <td>
                                                                    <span :class="{'text-success': getInventoryGap(line) > 0,'text-danger': getInventoryGap(line) < 0,}">@{{ getInventoryGap(line) }}</span>
                                                                </td>
                                                                <td>
                                                                    @{{ getInventoryValue(line) }}F
                                                                </td>
                                                            </tr>
                                                            <tr v-if="inventoryLines.length === 0">
                                                                <td colspan="10" class="text-center py-4">
                                                                    <div>
                                                                        <img style="width:50px" src="{{ asset("assets/images/inventory.png") }}" alt="">
                                                                        <p>
                                                                            Veuillez sélectionner le produit à inventoriser.
                                                                        </p>

                                                                    </div>
                                                                </td>
                                                            </tr>

                                                        </tbody>
                                                    </table>
                                                </div>

                                                <div class="d-flex flex-wrap align-items-center justify-content-between"  v-if="inventoryLines.length > 0">
                                                    <div>
                                                        <div class="d-flex flex-column">
                                                            <span>Total écart : <strong>@{{ getTotalGap() }}</strong> </span>
                                                            <span>Valeur totale : <strong>@{{ getTotalValue() }}</strong> F</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <button type="button" @click.prevent="inventoryLines=[]; selectedProductIds=[]" class="btn btn-dark-light bg-dark">Annuler</button>
                                                        <button type="button" @click.prevent="validateInventory" :disabled="isLoading" class="btn btn-success">Valider & Ajuster <span v-if="isLoading"
                                                            class="spinner-border spinner-border-sm ms-2"></span></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.box-body -->
                    </div>
                </div>
		    </div>

            <div class="modal fade modal-inventory-start" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Sélectionnez l'emplacement de l'inventaire !</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <ul class="list-group list-group-flush">
                                @foreach ($emplacements as $k => $place)
                                <li class="list-group-item">
                                    <div class="demo-radio-button">
                                        <input type="radio"
                                            class="with-gap radio-col-primary"
                                            id="emplacement_{{ $k }}"
                                            name="emplacement_id"
                                            value="{{ $place->id }}"
                                            v-model="form.emplacement_id">
                                        <label for="emplacement_{{ $k }}">{{ $place->libelle }}</label>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                            <button @click="startInventory"  class="btn btn-primary mt-2" style="width: 100%;"> <i class="mdi mdi-clipboard-check"></i> Lancer l'inventaire</button>
                        </div>
                    </div>
                    
                </div>
                <!-- /.modal-dialog -->
            </div>
		</section>
		<!-- /.content -->
	  </div>
  </div>
  <!-- /.content-wrapper -->

    
@endsection

@push("scripts")
    @push("scripts")
        <script type="module" src="{{ asset("assets/js/scripts/inventories.js") }}"></script>
    @endpush
@endpush
