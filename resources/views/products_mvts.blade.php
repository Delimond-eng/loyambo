@extends("layouts.admin")

@section("content")
<div class="content-wrapper">
    <div class="container-full">
        <section class="content" id="AppProduct" v-cloak>
            <div class="row g-4">
                <div class="col-xl-12">
                    @include("components.menus.products")
                </div>

                <div class="col-md-12">
                    <div class="box shadow-sm">
                        <!-- Navigation des filtres par type -->
                        <div class="box-header with-border bg-white p-0">
                            <ul class="nav nav-tabs nav-fill" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link" :class="{'active': filter_type === ''}" @click="filterByType('')" href="javascript:void(0)">
                                        <i class="fa fa-list me-2"></i>Tous les flux
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" :class="{'active': filter_type === 'entrée'}" @click="filterByType('entrée')" href="javascript:void(0)">
                                        <i class="fa fa-plus-circle text-success me-2"></i>Entrées
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" :class="{'active': filter_type === 'vente'}" @click="filterByType('vente')" href="javascript:void(0)">
                                        <i class="fa fa-shopping-cart text-primary me-2"></i>Ventes
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" :class="{'active': filter_type === 'sortie'}" @click="filterByType('sortie')" href="javascript:void(0)">
                                        <i class="fa fa-minus-circle text-danger me-2"></i>Sorties
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" :class="{'active': filter_type === 'ajustement'}" @click="filterByType('ajustement')" href="javascript:void(0)">
                                        <i class="fa fa-balance-scale text-info me-2"></i>Ajustements
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" :class="{'active': filter_type === 'transfert'}" @click="filterByType('transfert')" href="javascript:void(0)">
                                        <i class="fa fa-exchange text-warning me-2"></i>Transferts
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="box-header with-border d-flex justify-content-between align-items-center bg-white p-3">
                            <h4 class="box-title fw-bold text-primary text-uppercase">
                                Journal : <span class="text-dark">@{{ filter_type ? filter_type : 'Tous les flux' }}</span>
                            </h4>
                            <div class="d-flex gap-2 align-items-center">
                                <span class="small text-muted">Période :</span>
                                <input class="form-control form-control-sm w-150" v-model="filter_date1" type="date" @change="viewAllStockMvts">
                                <input class="form-control form-control-sm w-150" v-model="filter_date2" type="date" @change="viewAllStockMvts">
                            </div>
                        </div>

                        <div class="box-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr class="text-uppercase small fw-bold">
                                            <th>Date</th>
                                            <th>Désignation Produit</th>
                                            <th class="text-center">Type Flux</th>
                                            <th class="text-center">Quantité</th>
                                            <th class="text-center">Point de Vente / Emplacement</th>
                                            <th class="text-center">Auteur</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="data in mouvements" :key="data.id">
                                            <td class="small text-muted">@{{ formateSimpleDate(data.date_mouvement) }}</td>
                                            <td>
                                                <h6 class="mb-0 fw-bold" v-if="data.produit">@{{ data.produit.libelle }}</h6>
                                                <span class="text-danger small" v-else>Article inconnu</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge" :class="{
                                                    'bg-success-light text-success': data.type_mouvement === 'entrée',
                                                    'bg-danger-light text-danger': data.type_mouvement === 'sortie',
                                                    'bg-primary-light text-primary': data.type_mouvement === 'vente',
                                                    'bg-warning-light text-warning': data.type_mouvement === 'transfert',
                                                    'bg-info-light text-info': data.type_mouvement === 'ajustement'
                                                }">@{{ data.type_mouvement.toUpperCase() }}</span>
                                            </td>
                                            <td class="text-center fw-bold">@{{ data.quantite }}</td>
                                            <td class="text-center">
                                                <span v-if="data.emplacement" class="badge badge-secondary-light">@{{ data.emplacement.libelle }}</span>
                                                <span v-else class="text-muted italic small">Flux Global</span>
                                            </td>
                                            <td class="text-center small">
                                                <span v-if="data.user">@{{ data.user.name }}</span>
                                                <span v-else>Système</span>
                                            </td>
                                            <td class="text-center">
                                                <button v-if="data.type_mouvement !== 'vente'" class="btn btn-danger-light btn-xs" @click="deleteMvt(data)">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                                <span v-else class="text-fade small"><i class="fa fa-lock"></i></span>
                                            </td>
                                        </tr>

                                        <tr v-if="mouvements.length === 0">
                                            <td colspan="7" class="text-center py-100">
                                                <div v-if="isDataLoading" class="spinner-border text-primary"></div>
                                                <div v-else>
                                                    <i class="fa fa-history fa-3x text-muted mb-3"></i>
                                                    <p class="text-muted">Aucun flux trouvé pour ce critère.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="box-footer border-top">
                            <Paginator
                                :current-page="pagination.current_page"
                                :last-page="pagination.last_page"
                                :total-items="pagination.total"
                                @page-changed="changePage">
                            </Paginator>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/product.js") }}"></script>
@endpush
