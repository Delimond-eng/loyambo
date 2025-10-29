<div class="menu-tabs-wrapper">
    <div class="menu-tabs">
        <button class="menu-tab {{ Route::is('Reservations') ? 'active' : '' }}" 
                onclick="location.href='{{ route('Reservations') }}'">
            Réservations
        </button>
        <button class="menu-tab {{ Route::is('Reservations.libres') ? 'active' : '' }}" 
                onclick="location.href='Reservations.libres'">
            Chambres libres
        </button>
        <button class="menu-tab {{ Route::is('Reservations.occupees') ? 'active' : '' }}" 
                onclick="location.href='Reservations.occupees'">
            Chambres occupées
        </button>
        <button class="menu-tab {{ Route::is('Reservations.reserve') ? 'active' : '' }}" 
                onclick="location.href='Reservations.reserve'">
            Chambres réservées
        </button>
    </div>
</div>
