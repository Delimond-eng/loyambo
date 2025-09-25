 <div class="menu-tabs-wrapper">
    <div class="menu-tabs">
        <button class="menu-tab {{ Route::is("tables.emplacements") ? 'active' : '' }}" onclick="location.href='/tables.emplacements'">
            Emplacements
        </button>
        <button class="menu-tab {{ Route::is("tables") ? 'active' : '' }}" onclick="location.href='/tables'">
            Tables & chambres
        </button>
        <button class="menu-tab {{ Route::is("tables.occuped") ? 'active' : '' }}" onclick="location.href='/tables.occuped'">
            Occupation Tables <span class="btn-badge">0</span>
        </button>
        <button class="menu-tab {{ Route::is("beds.occuped") ? 'active' : '' }}" onclick="location.href='/beds.occuped'">
            Occupation chambres<span class="btn-badge">0</span>
        </button>
    </div>
</div>