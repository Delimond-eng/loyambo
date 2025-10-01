<header class="main-header">
    <div class="inside-header bg-transparent">
        <div class="d-lg-flex logo-box justify-content-start">
            <!-- Logo -->
            <a href="{{ route("home") }}" class="logo">
                @if ((!Route::is("home") && Auth::user()->role !== 'serveur') || (!Route::is("orders.portal") && Auth::user()->role==='serveur'))
                    <!-- Bouton retour -->
                    <button onclick="window.history.back()" class="btn btn-sm text-white fs-20 shadow-sm">
                        <i class="mdi mdi-arrow-left"></i>
                    </button>
                @endif

                <!-- logo -->
                <div class="logo-lg">
                    <span class="light-logo">
                        <img src="{{ asset("assets/images/logo-3.jpg") }}" alt="logo">
                    </span>
                    <span class="dark-logo">
                        <img src="{{ asset("assets/images/logo-light-text.png") }}" alt="logo">
                    </span>
                </div>
            </a>
        </div>
        <!-- Header Navbar -->
        <nav class="navbar navbar-static-top">
            <!-- Sidebar toggle button-->
            <div class="app-menu">
                <ul class="header-megamenu nav">
                    <li class="btn-group nav-item d-none d-xl-inline-block">
                        <div class="app-menu">
                            <div class="search-bx mx-5">
                                
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="navbar-custom-menu r-side" >
                <ul class="nav navbar-nav AppService" v-cloak>
                    <li class="btn-group nav-item">
                        <span class="label label-danger">@{{ totalQte }}</span>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#modal-right" title="Setting" class="waves-effect waves-light nav-link full-screen btn-danger-light">
                            <span class="icon-Cart2"><span class="path1"></span><span class="path2"></span></span>
                        </a>
                    </li>

                    <!-- User Account-->
                    <li class="dropdown user user-menu">
                        <a href="#"
                            class="dropdown-toggle p-0 text-dark hover-primary ms-md-30 ms-10 d-flex align-items-center"
                            data-bs-toggle="dropdown" title="User">
                            <span class="ps-30 d-md-inline-block d-none"></span>
                            <div class="text-start d-md-inline-block d-none">
                                <strong class="text-white">{{ Auth::user()->name }}</strong><br>
                                <small class="text-white">{{ Auth::user()->role }}</small>
                            </div>
                            <img src="{{ asset("assets/images/profil-2.png") }}"
                                class="user-image rounded-circle avatar bg-white mx-10" alt="User Image">
                        </a>
                        <ul class="dropdown-menu animated flipInX AppService">
                            <li class="user-body">
                                <a class="dropdown-item" href="{{ url('/licences/pricing') }}"> <i class="mdi mdi-key-variant text-muted me-2"></i>Licence trial <small class="text-info">(7 jrs restants)</small></a>
                                @canCloseDay
                                    @can("cloturer-journee")
                                    <a class="dropdown-item text-danger" href="javascript:void(0);" @click="triggerClosingDay"><i class="fa fa-sign-out me-2"></i>Clotûrer la journée</a>
                                    @endcan
                                @else
                                    @can("ouvrir-journee")
                                    <a class="dropdown-item text-primary btn-start-day" href="#"><i class="fa fa-sign-in me-2"></i>
                                    Commencer la journée</a>
                                    @endcan
                                @endif
                                <div class="dropdown-divider"></div>
                                <form id="logout-form" hidden action="{{ route('logout') }}" method="POST">
                                @csrf
                                </form>
                                <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i
                                        class="ti-lock text-muted me-2"></i>
                                    Deconnexion</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</header>
<div class="modal modal-right fade AppService" id="modal-right" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title fs-20 fw-700 d-flex" v-if="selectedTable">Bon de commande,  Table <div class="text-primary fw-500 ms-2 text-center">@{{ selectedTable.numero }}</div></h5>
            <h5 class="modal-title fs-20 text-danger" v-else>Aucune Table trouvé</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body d-flex flex-column">
                 <div class="cart-items">
                    <template v-if="cart.length === 0">
                        <div class="empty-cart">
                            <i class="mdi mdi-cart-outline"></i>
                            <p>Le Panier est vide !</p>
                        </div>
                    </template>
                    <template v-else>
                        <div v-for="item in cart" :key="item.id" class="cart-item">
                            <div class="cart-item-image">
                                <i class="icon-Dinner1 fs-18 text-primary"><span class="path1"></span><span class="path2"></span></i>
                            </div>
                            <div class="cart-item-details">
                                <div class="cart-item-name">
                                    @{{ item.libelle }}
                                </div>
                                <div class="cart-item-info">@{{ item.qte }} x @{{ item.prix_unitaire }}F</div>
                            </div>
                            <input type="number" style="width:60px" v-model="item.qte" class="form-control" placeholder="1" min="1">
                            <div class="cart-item-actions">
                                <button @click="removeFromCart(item)" class="btn btn-danger-light btn-xs ms-2">
                                    <i class="mdi mdi-close fs-10"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            <div class="modal-footer modal-footer-uniform" v-if="selectedTable">
                <div class="cart-total">
                    <span>Total</span>
                    <span class="total-amount">@{{ totalGlobal }}F</span>
                </div>
                <button class="pay-button" :disabled="cart.length === 0 || isLoading" @click="createFacture">
                    <i class="ri-money-dollar-circle-line"></i>
                    <span v-if="isLoading">Validation en cours....</span>
                    <span v-else>Valider la commande</span>
                </button>
                <!-- <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-success float-end" @click="createFacture" :disabled="isLoading"> <i class="mdi mdi-check-all me-1"></i> Valider la commande <span v-if="isLoading" class="spinner-border spinner-border-sm ms-2"></span></button> -->
            </div>
        </div>
    </div>
</div>


@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/service.js") }}"></script>
@endpush


