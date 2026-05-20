<div class="menu-tabs-wrapper">
    <div class="menu-tabs">
        <button class="menu-tab {{ Route::is('reservations') ? 'active' : '' }}" 
                onclick="location.href='{{ route('reservations') }}'">
            Réservations
        </button>
        <button class="menu-tab {{ Route::is('chambres.all') ? 'active' : '' }}" 
                onclick="location.href='/chambres/all'">
            Toutes les chambres
        </button>
        <button class="menu-tab {{ Route::is('chambres.libre') ? 'active' : '' }}" 
                onclick="location.href='/chambres/libre'">
            Chambres libres
        </button>
        <button class="menu-tab {{ Route::is('chambres.occupee') ? 'active' : '' }}" 
                onclick="location.href='/chambres/occupee'">
            Chambres occupées
        </button>
        <button class="menu-tab {{ Route::is('chambres.reservee') ? 'active' : '' }}" 
                onclick="location.href='/chambres/reservee'">
            Chambres réservées
        </button>
    </div>
</div>
