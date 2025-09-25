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
                    <h3 class="page-title">Loyambo Restaurant & Hôtel</h3>
                    <div class="d-inline-block align-items-center">
                        <nav>
                            <ol class="breadcrumb">
                               <li class="breadcrumb-item ms-1" aria-current="page">Bienvenue {{ Auth::user()->name }}, Vous êtes connectés comme <span class="text-primary fw-700">{{ Auth::user()->role }}</span></li>
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
                        <button class="menu-btn" type="button" onclick="location.href='/dashboard'">
                            <img class="menu-icon" src="assets/icons/data-analysis.png" alt="Tableau de bord">
                            <div class="menu-label">Tableau de bord</div>
                        </button>

                        @can('voir-rapports')
                            @canCloseDay
                            <button class="menu-btn" type="button" onclick="location.href='/reports.global'">
                                <img class="menu-icon" src="assets/icons/document.png" alt="Rapports">
                                <div class="menu-label">Rapports</div>
                            </button>
                            @endif
                        @endcan

                        @can('voir-serveurs')
                            <button class="menu-btn" type="button" onclick="location.href='/serveurs'">
                                <img class="menu-icon" src="assets/icons/serving-dish.png" alt="Serveurs">
                                <div class="menu-label">Serveurs</div>
                            </button>
                        @endcan


                        @can('voir-produits')
                            <button class="menu-btn" type="button" onclick="location.href='/products'">
                                <img class="menu-icon" src="assets/icons/add-product.png" alt="Produits">
                                <div class="menu-label">Produits</div>
                            </button>
                        @endcan
                        
                        @can('voir-emplacements')
                            <button class="menu-btn" type="button" onclick="location.href='/tables.emplacements'"> 
                                <img class="menu-icon" src="assets/icons/home-button.png" alt="Emplacements">
                                <div class="menu-label">Emplacements</div>
                            </button>
                        @endcan
                        

                        @can('voir-factures')
                            @canCloseDay
                            <button class="menu-btn" type="button" onclick="location.href='/factures'">
                                <img class="menu-icon" src="assets/icons/quality-control.png" alt="Factures">
                                <span class="btn-badge">0</span>
                                <div class="menu-label">Factures</div>
                            </button>
                            @endif
                        @endcan

                        @can('voir-ventes')
                            @canCloseDay
                            <button class="menu-btn" type="button" onclick="location.href='/sells'">
                                <img class="menu-icon" src="assets/icons/online-shopping.png" alt="Ventes">
                                <div class="menu-label">Ventes</div>
                            </button>
                            @endif
                        @endcan


                        @can('voir-commandes')
                            @canCloseDay
                            <button class="menu-btn" type="button" onclick="location.href='/orders'">
                                <img class="menu-icon" src="assets/icons/room-service.png" alt="Commandes">
                                <span class="btn-badge">0</span>
                                <div class="menu-label">Commandes</div>
                            </button>
                            @endif
                        @endcan

                        @can('voir-chambres')
                            @canCloseDay
                                @if(Auth::user()->role==='caissier' && Auth::user()->emplacement->type==='hôtel')
                                <button class="menu-btn" type="button" onclick="location.href='/bedroom.reserve'">
                                    <img class="menu-icon" src="assets/icons/hotel-check-in.png" alt="Chambres">
                                    <span class="btn-badge">0</span>
                                    <div class="menu-label">Reservations</div>
                                </button>
                                @endif
                            @endif
                        @endcan


                        @can("manage-users")
                            <button class="menu-btn" type="button" onclick="location.href='/users'">
                                <img class="menu-icon" src="assets/icons/user.png" alt="Utilisateurs">
                                <div class="menu-label">Utilisateurs</div>
                            </button>
                        @endcan
                    </div>
                </main>
            </div>
        </section>
    </div>
</div>

<!-- Widget Licence -->
<div class="license-widget trial">
    <div class="d-flex justify-content-between mb-2 align-items-center">
        <h4 class="text-primary mb-0">Licence Trial</h4>
        <button class="btn btn-sm btn-soft-primary">Activer</button>
    </div>
    <div class="license-status status-trial">
        Essai (15 j restants)
    </div>
</div>

@endsection