<div class="menu-tabs-wrapper">
    <div class="menu-tabs">
        <button class="menu-tab {{ Route::is('reports.global') ? 'active' : '' }}" onclick="location.href='{{ route('reports.global') }}'">
            Journaliers (debut/fin)
        </button>
        <button class="menu-tab {{ (Route::is('reports.service.vente*') || Route::is('reports.service_sales.*')) ? 'active' : '' }}" onclick="location.href='{{ route('reports.service.vente') }}'">
            Ventes par service
        </button>
        <button class="menu-tab {{ Route::is('reports.performance') ? 'active' : '' }}" onclick="location.href='{{ route('reports.performance') }}'">
            Performance du personnel
        </button>
        <button class="menu-tab {{ (Route::is('reports.produits') || Route::is('reports.produits.plusVendus.*')) ? 'active' : '' }}" onclick="location.href='{{ route('reports.produits') }}'">
            Produits les plus vendus
        </button>
        <button class="menu-tab {{ Route::is('reports.reservations*') ? 'active' : '' }}" onclick="location.href='{{ route('reports.reservations') }}'">
            Reservations hotel
        </button>

        @if (Auth::user()->role === 'admin')
            <button class="menu-tab {{ Route::is('reports.finances') ? 'active' : '' }}" onclick="location.href='{{ route('reports.finances') }}'">
                Finances
            </button>
        @endif
    </div>
</div>
