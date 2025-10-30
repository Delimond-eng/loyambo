 <div class="menu-tabs-wrapper">
    <div class="menu-tabs">
        <button class="menu-tab {{ Route::is("serveurs") ? 'active' : '' }}" onclick="location.href='/serveurs'">
            Liste des serveurs
        </button>
        <button class="menu-tab {{ Route::is("serveurs.activities") ? 'active' : '' }}" onclick="location.href='/serveurs.activities'">
            Serveurs en service
        </button>
        <button class="menu-tab {{ Route::is("sells") ? 'active' : '' }}" onclick="location.href='/sells'">
            Ventes journalières
        </button>
    </div>
</div>
