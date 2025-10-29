<div class="menu-tabs-wrapper">
    <div class="menu-tabs">
        <button class="menu-tab {{ Route::is('Reservations') ? 'active' : '' }}" 
                onclick="location.href='{{ route('Reservations') }}'">
            Réservations
        </button>
        <button class="menu-tab {{ Route::is('Reservations.libres') ? 'active' : '' }}" 
                onclick="location.href=''">
            Chambres libres
        </button>
        <button class="menu-tab {{ Route::is('Reservations.occupees') ? 'active' : '' }}" 
                onclick="location.href=''">
            Chambres occupées
        </button>
    </div>
</div>
