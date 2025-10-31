@extends("layouts.admin")

@section("content")

<div class="content-wrapper">
	<div class="container-full AppService" v-cloak>
        <div class="data-loading" v-if="isDataLoading">
            <img src="{{ asset("assets/images/loading.gif") }}" alt="loading">
            <h4 class="mt-2">Chargement...</h4>
        </div>
		<main class="main-content" v-else>
            <div class="products-section">
                <div class="controls">
                    <div class="search-container">
                        <i class="fa fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Rechercher un produit..."  v-model="search">
                    </div>

                    <div class="category-filters">
                        <button class="filter-btn active" @click="viewAllProducts">
                            Tous
                        </button>
                        <button v-for="(data, index) in allCategories" :key="index" class="filter-btn" :style="`background:${data.couleur}; color:${getTextColor(data.couleur)}`" @click="products = data.produits">
                            <i class="icon-Dinner1 me-2" :style="`color:${getTextColor(data.couleur)}`"><span class="path1"></span><span class="path2"></span></i>
                            @{{ data.libelle}}
                        </button>
                    </div>
                </div>

                <div class="products-grid" id="productsGrid">
                    <div class="product-card" v-for="(data, i) in allProducts" :key="i" :style="`border-color:${data.categorie.couleur}`" @click="addToCart(data)">
						<!-- <span class="product-category">@{{ data.categorie.libelle }}</span> -->
						<h3 class="product-name">@{{ data.libelle }}</h3>
						<span class="product-price">@{{ data.prix_unitaire }}</span>
					</div>
                </div>
            </div>

            <aside class="sidebar">
                <div class="cart-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="mdi mdi-cart-outline"></i>
                        <h2>Panier</h2>
                    </div>
                    <div>
                        <span v-if="selectedTable">Table @{{ selectedTable.numero }}</span>
                        <span v-if="selectedChambre">Chambre @{{ selectedChambre.numero }}</span>
                    </div>
                </div>
                <div class="cart-items">
                    <template v-if="cart.length === 0">
                        <div class="empty-cart">
                            <i class="mdi mdi-cart-outline"></i>
                            <p>Le Panier est vide !</p>
                        </div>
                    </template>
                    <template v-else>
                        <div v-for="item in cart" :key="item.id" class="cart-item">
                            <div class="cart-item-image">
                                <i class="icon-Dinner1 fs-18 text-primary"><span class="path1"></span><span class="path2"></span></i>
                            </div>
                            <div class="cart-item-details">
                                <div class="cart-item-name">
                                    @{{ item.libelle }}
                                </div>
                                <div class="cart-item-info">@{{ item.qte }} x @{{ item.prix_unitaire }}F</div>
                            </div>
                            <input type="number" style="width:60px" v-model="item.qte" class="form-control" placeholder="1" min="1">
                            <div class="cart-item-actions">
                                <button @click="removeFromCart(item)" class="btn btn-danger-light btn-xs ms-2">
                                    <i class="mdi mdi-close fs-10"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="cart-footer">
                    <div class="cart-total">
                        <span>Total</span>
                        <span class="total-amount">@{{ totalGlobal }}F</span>
                    </div>
                    <button class="pay-button" :disabled="cart.length === 0 || isLoading" @click="createFacture">
                        <i class="ri-money-dollar-circle-line"></i>
                        <span v-if="isLoading">Validation en cours....</span>
                        <span v-else>Valider la commande</span>
                    </button>
                </div>
            </aside>
        </main>
	</div>
</div>


@endsection
@push("styles")
	<link rel="stylesheet" href="{{ asset("assets/css/pos.css") }}">
@endpush
@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/service.js") }}"></script>
@endpush
