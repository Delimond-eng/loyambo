@extends("layouts.admin")

@section("content")

<div class="content-wrapper">
	<div class="container-full AppService" v-cloak>
		<main class="main-content split-layout">
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
                        <button v-for="(data, index) in allCategories" :key="index" class="filter-btn" @click="products = data.produits">
                            <i class="icon-Dinner1 me-2"><span class="path1"></span><span class="path2"></span></i>
                            @{{ data.libelle}}
                        </button>
                    </div>
                </div>

                <div class="products-grid" id="productsGrid">
                    <div class="product-card" v-for="(data, i) in allProducts" :key="i" @click="addToCart(data)">
						<span class="product-category">@{{ data.categorie.libelle }}</span>
						<h3 class="product-name">@{{ data.libelle }}</h3>
						<span class="product-price">@{{ data.prix_unitaire }}</span>
					</div>
                </div>
            </div>
        </main>
	</div>
</div>

@if (Auth::user()->role === 'serveur')
	<a class="btn btn-app btn-primary" style="position: fixed; right:30px; bottom: 30px;" href="{{ url("/orders") }}">
		<span class="badge bg-danger AppDashboard" v-cloak>@{{ counts.pendings ?? 0 }}</span>
		<i class="icon-Dinner1"><span class="path1"></span><span class="path2"></span></i>
	</a>
@endif
@endsection
@push("styles")
	<link rel="stylesheet" href="{{ asset("assets/css/pos.css") }}">
@endpush
@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/service.js") }}"></script>
@endpush
