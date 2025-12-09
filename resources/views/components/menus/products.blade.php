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

        <button class="menu-tab" onclick="location.href='/products.mvts'">
            Inventaires
        </button>
    </div>
</div>