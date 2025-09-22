<header class="main-header">
    <div class="inside-header bg-transparent">
        <div class="d-lg-flex logo-box justify-content-start d-none">
            <!-- Logo -->
            <a href="{{ route("home") }}" class="logo">
                <!-- logo-->
                <div class="logo-lg">
                    <span class="light-logo"><img src="{{ asset("assets/images/logo-3.jpg") }}" alt="logo"></span>
                    <span class="dark-logo"><img src="{{ asset("assets/images/logo-light-text.png") }}" alt="logo"></span>
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
                        <span class="label label-danger">@{{ cart.length }}</span>
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
                            <img src="{{ asset("assets/images/avatar/avatar-2.png") }}"
                                class="user-image rounded-circle avatar bg-white mx-10" alt="User Image">
                        </a>
                        <ul class="dropdown-menu animated flipInX">
                            <li class="user-body">
                                <a class="dropdown-item" href="{{ url('/licences/pricing') }}"> <i class="mdi mdi-key-variant text-muted me-2"></i>Licence trial <small class="text-info">(7 jrs restants)</small></a>
                                @canCloseDay
                                    @can("cloturer-journee")
                                    <a class="dropdown-item text-danger" href="{{ route("orders.portal") }}"><i class="fa fa-sign-out me-2"></i>
                                    Clotûrer la journée</a>
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
            <h5 class="modal-title fs-20 fw-700 d-flex" v-if="selectedTable">Bon de commande Table <div class="bg-primary fw-500 fs-12 ms-2 rounded-circle w-30 h-30 l-h-30 text-center">@{{ selectedTable.numero }}</div></h5>
            <h5 class="modal-title fs-20 text-danger" v-else>Aucune Table trouvé</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div v-if="cart.length" class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>PU</th>
                                <th style="width:100px">QTE</th>
                                <th style="text-align:center">Total</th>
                                <th style="text-align:center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(data, index) in cart">
                                <td class="fs-12">
                                    @{{data.libelle}}
                                </td>
                                <td class="fs-12">@{{ data.prix_unitaire }}</td>
                                <td>
                                    <input type="number" v-model="data.qte" class="form-control" placeholder="1" min="1">
                                </td>
                                <td align="center" class="fw-900 fs-12">@{{ data.prix_unitaire * data.qte }}</td>
                                <td align="center"><a href="javascript:void(0)" class="btn btn-danger-light btn-xs" @click="removeFromCart(data)" title=""><i class="ti-close"></i></a></td>
                            </tr>
                            <tr>
                                <td class="fw-800">Total</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="fw-800">@{{ totalGlobal }}</td>
                            </tr>					
                        </tbody>
                    </table>
                </div>
                <div v-else style="height:100%" class="d-flex justify-content-center align-items-center flex-column">
                    <p class="mt-3 text-danger">Panier vide !</p>
                </div>
            </div>
            <div class="modal-footer modal-footer-uniform" v-if="selectedTable">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-success float-end" @click="createFacture" :disabled="isLoading"> <i class="mdi mdi-check-all me-1"></i> Valider la commande <span v-if="isLoading" class="spinner-border spinner-border-sm ms-2"></span></button>
            </div>
        </div>
    </div>
</div>

@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/service.js") }}"></script>
@endpush


