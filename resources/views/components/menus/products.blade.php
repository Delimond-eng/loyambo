 <div class="menu-tabs-wrapper">
    <div class="menu-tabs">
        <button class="menu-tab {{ Route::is("products") ? 'active' : '' }}" onclick="location.href='/products'">
            Produits
        </button>
        <button class="menu-tab {{ Route::is("products.entree") ? 'active' : '' }}" onclick="location.href='/products.entree'">
            Approvisionnement
        </button>

        <button class="menu-tab {{ Route::is("products.categories") ? 'active' : '' }}" onclick="location.href='/products.categories'">
            Catégories
        </button>

        <button class="menu-tab {{ Route::is("fiche_stock") ? 'active' : '' }}" onclick="location.href='/fiche_stock'">
            Fiche de stock
        </button>

        @if (Auth::user()->role === "admin")
            <button class="menu-tab {{ Route::is("products.inventories") ? 'active' : '' }}" onclick="location.href='/products.inventories'">
                Inventaires
            </button>
        @endif
    </div>
</div>