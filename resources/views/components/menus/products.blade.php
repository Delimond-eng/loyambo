 <div class="menu-tabs-wrapper">
    <div class="menu-tabs">
        <button class="menu-tab {{ Route::is("products") ? 'active' : '' }}" onclick="location.href='/products'">
            Produits
        </button>
        <button class="menu-tab {{ Route::is("products.categories") ? 'active' : '' }}" onclick="location.href='/products.categories'">
            Catégories
        </button>
        <button class="menu-tab {{ Route::is("products.mvts") ? 'active' : '' }}" onclick="location.href='/products.mvts'">
            Mouvements stocks
        </button>
    </div>
</div>