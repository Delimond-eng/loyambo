@extends("layouts.admin")

@push("styles")
    <link rel="stylesheet" href="assets/css/menu.css">
@endpush

@section("content") 
<div class="content-wrapper">
    <div class="container-full">
        <div class="content-header">
            <div class="d-flex align-items-center justify-content-center">
                <div class="align-center">
                    <h3 class="page-title text-center">Bienvenue chez {{ Auth::user()->etablissement->nom }}</h3>
                    <div class="d-inline-block align-items-center">
                        <nav>
                            <ol class="breadcrumb text-center">
                               <li class="breadcrumb-item ms-1" aria-current="page">Bienvenue {{ Auth::user()->name }}, Vous êtes connectés comme <span class="text-primary fw-700">{{ Auth::user()->role }} @if(Auth::user()->emplacement) , <span class="fa fa-home me-1"></span> {{ Auth::user()->emplacement->libelle ?? "" }} @endif</span></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <!-- Main content -->
        <section class="content">
            <div class="app-shell" role="application" aria-label="Application de restaurant — menu rapide">
                <main class="menu-wrap" id="main">
                    <div class="menu-grid" id="menuGrid">
                        <button class="menu-btn b-1 border-primary"  @unless(Blade::check('licenceActive')) disabled @endunless type="button" onclick="location.href='/dashboard'">
                            <img class="menu-icon" src="assets/icons/data-analysis.png" alt="Tableau de bord">
                            <div class="menu-label">Tableau de bord</div>
                        </button>

                        @can('voir-rapports')
                            @canCloseDay
                            <button class="menu-btn b-1 border-primary"  @unless(Blade::check('licenceActive')) disabled @endunless type="button" onclick="location.href='/reports.global'">
                                <img class="menu-icon" src="assets/icons/document.png" alt="Rapports">
                                <div class="menu-label">Rapports</div>
                            </button>
                            @endif
                        @endcan

                        @can('voir-serveurs')
                            <button class="menu-btn b-1 border-primary"  @unless(Blade::check('licenceActive')) disabled @endunless type="button" onclick="location.href='/serveurs'">
                                <img class="menu-icon" src="assets/icons/serving-dish.png" alt="Serveurs">
                                <div class="menu-label">Serveurs</div>
                            </button>
                        @endcan


                        @can('voir-produits')
                            <button class="menu-btn b-1 border-primary"  @unless(Blade::check('licenceActive')) disabled @endunless type="button" onclick="location.href='/products'">
                                <img class="menu-icon" src="assets/icons/add-product.png" alt="Produits">
                                <div class="menu-label">Produits</div>
                            </button>
                        @endcan
                        
                        @can('voir-emplacements')
                            <button class="menu-btn b-1 border-primary"  @unless(Blade::check('licenceActive')) disabled @endunless type="button" onclick="location.href='/tables.emplacements'"> 
                                <img class="menu-icon" src="assets/icons/home-button.png" alt="Emplacements">
                                <div class="menu-label">Emplacements</div>
                            </button>
                        @endcan
                        
                        @can('voir-factures')
                            @canCloseDay
                            <button class="menu-btn b-1 border-primary"  @unless(Blade::check('licenceActive')) disabled @endunless type="button" onclick="location.href='/factures'">
                                <img class="menu-icon" src="assets/icons/quality-control.png" alt="Factures">
                                <span class="btn-badge AppDashboard" v-cloak>@{{ counts.facs ?? 0 }}</span>
                                <div class="menu-label">Factures</div>
                            </button>
                            @endif
                        @endcan

                        @can('voir-ventes')
                            @canCloseDay
                            <button class="menu-btn b-1 border-primary"  @unless(Blade::check('licenceActive')) disabled @endunless type="button" onclick="location.href='/sells'">
                                <img class="menu-icon" src="assets/icons/online-shopping.png" alt="Ventes">
                                <div class="menu-label">Ventes</div>
                            </button>
                            @endif
                        @endcan


                        @can('voir-commandes')
                            @canCloseDay
                            <button class="menu-btn b-1 border-primary"  @unless(Blade::check('licenceActive')) disabled @endunless type="button" onclick="location.href='/orders'">
                                <img class="menu-icon" src="assets/icons/room-service.png" alt="Commandes">
                                <span class="btn-badge AppDashboard" v-cloak>@{{ counts.pendings ?? 0 }}</span>
                                <div class="menu-label">Commandes</div>
                            </button>
                            @endif
                        @endcan

                        @can('voir-chambres')
                            @canCloseDay
                                @if(Auth::user()->role==='caissier' && Auth::user()->emplacement->type==='hôtel')
                                <button class="menu-btn b-1 border-primary"  @unless(Blade::check('licenceActive')) disabled @endunless type="button" onclick="location.href='/Reservations'">
                                    <img class="menu-icon" src="assets/icons/hotel-check-in.png" alt="Chambres">
                                    <div class="menu-label">Reservations</div>
                                </button>
                                @endif
                            @endif
                        @endcan


                        @can("manage-users")
                            <button class="menu-btn b-1 border-primary" @unless(Blade::check('licenceActive')) disabled @endunless type="button" onclick="location.href='/users'">
                                <img class="menu-icon" src="assets/icons/user.png" alt="Utilisateurs">
                                <div class="menu-label">Utilisateurs</div>
                            </button>
                        @endcan

                        @if (Auth::user()->role === 'admin')
                            <button class="menu-btn b-1 border-primary" @unless(Blade::check('licenceActive')) disabled @endunless type="button" onclick="location.href='/settings'">
                                <img class="menu-icon" src="assets/icons/settings.png" alt="Utilisateurs">
                                <div class="menu-label">Paramètres</div>
                            </button>
                        @endif
                    </div>
                </main>
            </div>
        </section>
    </div>
</div>

<!-- Widget Licence -->
@licenceActive
<div class="license-widget {{ auth()->user()->etablissement->licence->type === 'trial' ? 'trial' : 'active' }}">
    <div class="d-flex justify-content-between mb-2 align-items-center">
        <h4 class="text-primary mb-0">Licence {{ auth()->user()->etablissement->licence->type }}</h4>
        <a href="{{ route('licence.payment', ['ets_id' => auth()->user()->ets_id]) }}" class="btn btn-sm btn-soft-primary">
            Activer
        </a>
    </div>
    <div class="license-status {{ auth()->user()->etablissement->licence->type === 'trial' ? 'status-trial' : 'status-active' }}">
        Essai ({{ now()->diffInDays(auth()->user()->etablissement->licence->date_fin, false) }} j restants)
    </div>
</div>
@else
<div class="row d-flex justify-content-center">
    <div class="col-xl-4 col-12">
        <div class="alert alert-danger text-center">
            Votre licence a expiré. Veuillez <a href="{{ route('licence.payment', ['ets_id' => auth()->user()->ets_id]) }}">renouveler</a>.
        </div>
    </div>
</div>
@endlicenceActive


@endsection

@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/dashboard.js") }}"></script>
@endpush