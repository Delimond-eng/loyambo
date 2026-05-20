@extends("layouts.admin")


@push("styles")
    <link rel="stylesheet" href="{{ asset("assets/css/pos.css") }}">
@endpush

@section("content")
<div class="content-wrapper">
	<div class="container-full pos-module-wrapper AppService" v-cloak>
        <!-- Modal Liste Emplacements -->
        <div class="modal fade" id="modalEmplacement" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title fw-bold"><i class="fa fa-map-marker me-2"></i> Choisir votre emplacement</h5>
                    </div>
                    <div class="modal-body p-4">
                        <div v-if="emplacementsLoading" class="text-center py-4">
                            <i class="fa fa-spinner fa-spin fa-2x text-primary mb-2"></i>
                            <p class="text-muted mb-0">Chargement des emplacements...</p>
                        </div>
                        <div class="row g-3" v-else>
                            <div class="col-6" v-for="emp in emplacements" :key="emp.id">
                                <div class="card h-100 border-1 shadow-sm pos-emp-card" @click="selectEmplacement(emp)">
                                    <div class="card-body text-center p-3">
                                        <div class="icon-box mb-3 mx-auto bg-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <i class="fa fa-utensils fs-4" v-if="emp.type !== 'hôtel'"></i>
                                            <i class="fa fa-bed fs-4" v-else></i>
                                        </div>
                                        <h6 class="fw-bold mb-0">@{{ emp.libelle }}</h6>
                                        <small class="text-muted">@{{ emp.type }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="data-loading text-center py-5" v-if="isDataLoading">
            <div class="spinner-box mx-auto mb-3">
                <i class="fa fa-spinner fa-spin fa-3x text-primary"></i>
            </div>
            <h4 class="mt-2 text-primary fw-bold">Chargement...</h4>
        </div>

		<div class="pos-main-layout" v-else>
            <div class="pos-products-area">
                <div class="pos-navbar mb-4 shadow-sm rounded">
                    <div class="pos-nav-container">
                        <!-- Layout des inputs corrigé -->
                        <div class="pos-nav-inputs">
                            <div class="pos-search-box">
                                <i class="fa fa-search"></i>
                                <input style="width: 100%" type="text" placeholder="Rechercher un produit..." v-model="search" style="height: 40px;">
                            </div>

                            <div class="pos-place-selector">
                                <button v-if="!selectedTable" style="width: 100%" class="btn btn-outline-primary rounded-pill px-3 d-flex justify-content-between align-items-center" @click="showEmplacementModal" style="height: 45px;">
                                    <span><i class="fa fa-map-marker me-2"></i> @{{ currentEmplacement ? currentEmplacement.libelle : 'Sélectionner emplacement' }}</span>
                                    <i class="fa fa-chevron-down small"></i>
                                </button>
                                <div v-else class="border-1 border-primary rounded-pill px-3 d-flex justify-content-between align-items-center" style="height:45px; cursor: default; width: 100%">
                                    <span class="text-primary fw-bold"><i class="fa fa-map-marker me-2"></i>@{{ currentEmplacement ? currentEmplacement.libelle : (selectedTable.emplacement ? selectedTable.emplacement.libelle : 'Emplacement') }}</span>
                                    <span class="badge bg-primary-light text-primary">@{{ selectedTable.emplacement ? selectedTable.emplacement.type : '' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex overflow-auto pb-2 gap-2 pos-category-scroll mt-3 flex-nowrap" style="-webkit-overflow-scrolling: touch;">
                            <button class="pos-filter-btn flex-shrink-0" :class="{'active': !selectedCategory}" @click="viewAllProducts(); selectedCategory=null;">
                                Tous
                            </button>
                            <button v-for="data in allCategories" :key="data.id" class="pos-filter-btn flex-shrink-0"
                                :class="{'active': selectedCategory && selectedCategory.id === data.id}"
                                :style="selectedCategory && selectedCategory.id === data.id ? `background:${data.couleur}; color:#fff; border-color:${data.couleur}` : `border-color:${data.couleur}; color:${data.couleur}`"
                                @click="filterByCategory(data)">
                                @{{ data.libelle }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="pos-grid">
                    <div class="pos-product-card" v-for="data in allProducts" :key="data.id" @click="addToCart(data)">
                        <div class="fw-bold text-dark mb-1">@{{ data.libelle }}</div>
                        <div class="text-primary fw-bold">@{{ data.prix_unitaire }}F</div>
                        <div class="small text-muted" v-if="data.quantified">Stock: @{{ data.stock_actuel }}</div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Panier avec Radius et Ombre Dynamique -->
            <aside class="pos-sidebar-right" :class="{'active shadow-lg': showMobileCart}">
                <div class="pos-cart-container bg-white rounded">
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold"><i class="fa fa-shopping-basket me-2 text-primary"></i> Ma Commande</h5>
                        <button class="btn-close d-lg-none" @click="toggleMobileCart"></button>
                    </div>

                    <div class="pos-cart-list">
                        <div v-if="cart.length === 0" class="pos-empty-cart">
                            <div class="text-center opacity-20 py-100">
                                <i class="fa fa-shopping-cart" style="font-size: 5rem;"></i>
                                <p class="mt-3 fw-bold">Votre panier est vide</p>
                            </div>
                        </div>
                        <div v-for="item in cart" :key="item.id" class="pos-cart-item mx-0">
                            <div class="flex-grow-1">
                                <div class="fw-bold text-dark">@{{ item.libelle }}</div>
                                <div class="text-primary fw-bold small">@{{ item.prix_unitaire }}F</div>
                            </div>
                            <div class="pos-qte-btns">
                                <button class="pos-btn-circle minus" @click="item.qte > 1 ? item.qte-- : removeFromCart(item)"><i class="fa fa-minus"></i></button>
                                <span class="fw-bold px-2">@{{ item.qte }}</span>
                                <button class="pos-btn-circle plus" @click="item.qte++"><i class="fa fa-plus"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="pos-footer border-top bg-white rounded-bottom">
                        <div class="d-flex justify-content-between mb-3 px-2">
                            <span class="text-muted fw-bold">TOTAL</span>
                            <h4 class="fw-bold mb-0 text-primary">@{{ totalGlobal.toLocaleString() }} F</h4>
                        </div>
                        <button class="pos-pay-btn shadow-sm" :disabled="cart.length === 0 || isLoading" @click="createFacture">
                            <span v-if="isLoading"><i class="fa fa-spinner fa-spin me-2"></i>Validation...</span>
                            <span v-else><i class="fa fa-check-circle me-2"></i>Confirmer (@{{ totalQte }})</span>
                        </button>
                    </div>
                </div>
            </aside>

            <!-- FAB pour mobile -->
            <div class="d-lg-none position-fixed bottom-0 end-0 m-4" @click="toggleMobileCart" style="z-index: 1000;">
                <span class="badge bg-danger rounded-pill pos-fab-badge" style="z-index: 1111;">@{{ totalQte }}</span>
                <button class="btn btn-primary rounded-circle shadow-lg pos-fab-cart">
                    <i class="fa fa-shopping-basket fs-4"></i>
                </button>
            </div>
        </div>
	</div>
</div>
@endsection

@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/service.js") }}"></script>
@endpush
