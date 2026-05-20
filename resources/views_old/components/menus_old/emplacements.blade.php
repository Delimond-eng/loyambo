 <div class="menu-tabs-wrapper">
    <div class="menu-tabs">
        <button class="menu-tab {{ Route::is("tables.emplacements") ? 'active' : '' }}" onclick="location.href='/tables.emplacements'">
            Emplacements
        </button>
        <button class="menu-tab {{ Route::is("tables") ? 'active' : '' }}" onclick="location.href='/tables'">
            Tables & chambres
        </button>
        <button class="menu-tab {{ Route::is("tables.occuped") ? 'active' : '' }}" onclick="location.href='/tables.occuped'">
            Occupation Tables <span class="btn-badge AppDashboard" v-cloak>@{{ tablePendingsCount }}</span>
        </button>
        <button class="menu-tab {{ Route::is("beds.occuped") ? 'active' : '' }}" onclick="location.href='/beds.occuped'">
            Occupation chambres<span class="btn-badge AppDashboard" v-cloak>@{{ bedPendingsCount }}</span>
        </button>
    </div>
</div>

@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/places.js") }}"></script>	
@endpush