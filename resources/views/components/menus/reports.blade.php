 <div class="menu-tabs-wrapper">
    <div class="menu-tabs">
        <button class="menu-tab {{ Route::is("reports.global") ?'active' : '' }}" onclick="location.href='/reports.global'">
            Ventes journalières
        </button>
        <button class="menu-tab {{ Route::is("reports.service.vente") ?'active' : '' }}" onclick="location.href='/reports.service.vente'">
            Ventes par service
        </button>
        <button class="menu-tab {{ Route::is("reports.performance") ?'active' : '' }}" onclick="location.href='/reports.performance'">
            Performance du personnel
        </button>
        <button class="menu-tab {{ Route::is("reports.produits") ?'active' : '' }}" onclick="location.href='/reports.produits'">
            Produits les plus vendus
        </button>
        <button class="menu-tab {{ Route::is("reports.commandes") ?'active' : '' }}" onclick="location.href='/reports.commandes'">
            Commandes
        </button>
        @if (Auth::user()->role === 'admin')
            <button class="menu-tab {{ Route::is("reports.inventaires") ?'active' : '' }}" onclick="location.href='/reports.inventaires'">
                Inventaires
            </button>
        @endif
        
        <button class="menu-tab {{ Route::is("reports.stocks") ?'active' : '' }}" onclick="location.href='/reports.stocks'">
            Stocks
        </button>
        <button class="menu-tab {{ Route::is("reports.Mouvements") ?'active' : '' }}" onclick="location.href='/reports.Mouvements'">
            Mouvements
        </button>
        @if(Auth::user()->role ==='admin')
        <button class="menu-tab {{ Route::is("reports.finances") ?'active' : '' }}" onclick="location.href='/reports.finances'">
            Finances
        </button>
        @endif
    </div>
</div>
