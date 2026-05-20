@extends("layouts.admin")

@section("content")
 <!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <div class="container-full">
        <div class="content-header">
            <div class="d-flex align-items-center justify-content-between">
                <div class="me-auto">
                    <h3 class="page-title">Tableau de bord</h3>
                    <div class="d-inline-block align-items-center">
                        <nav>
                            <ol class="breadcrumb">
                               <li class="breadcrumb-item ms-1" aria-current="page">Bienvenue {{ Auth::user()->name }}, Vous êtes connectés comme <span class="text-primary fw-700">{{ Auth::user()->role }}</span></li>
                            </ol>
                        </nav>
                    </div>
                </div>

                @canCloseDay
                    @can("cloturer-journee")
                    <div class="AppService">
                        <button @click="triggerClosingDay" class="waves-effect waves-light btn btn-danger text-center btn-rounded"><i class="fa fa-sign-out"></i> Clôturer la journée</button>
                    </div>
                    @endcan
                @else
                    @can("ouvrir-journee")
                    <button class="btn-start-day waves-effect waves-light btn btn-primary text-center btn-rounded"><i class="fa fa-sign-in"></i> Commencer la journée</button>
                    @endcan
                @endif
            </div>
        </div>
        <!-- Main content -->
        <section class="content">
            <div class="row">
                <div class="col-12">
                    <div class="box AppDashboard" v-cloak>
                        <div class="row g-0 py-2">
                            @can("voir-utilisateurs")
                            <div class="col-12 col-lg-3">
                                <div class="box-body be-1 border-light">
                                    <div class="flexbox mb-1">
                                    <span>
                                        <span class="icon-User text-primary fs-40"><span class="path1"></span><span class="path2"></span></span><br>
                                        Utilisateurs connectés
                                    </span>
                                    <span class="text-primary fs-40" v-cloak>@{{ counts.users }}</span>
                                    </div>
                                    <div class="progress progress-xxs mt-10 mb-0">
                                    <div class="progress-bar" role="progressbar" style="width: 35%; height: 4px;" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                            @endcan


                            <div class="col-12 col-lg-3 hidden-down">
                                <div class="box-body be-1 border-light">
                                    <div class="flexbox mb-1">
                                    <span>
                                        <span class="icon-Selected-file text-info fs-40"><span class="path1"></span><span class="path2"></span></span><br>
                                        Factures journalières
                                    </span>
                                    <span class="text-info fs-40" v-cloak>@{{ counts.facs }}</span>
                                    </div>
                                    <div class="progress progress-xxs mt-10 mb-0">
                                    <div class="progress-bar bg-info" role="progressbar" style="width: 55%; height: 4px;" aria-valuenow="55" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>


                            <div class="col-12 col-lg-3">
                                <div class="box-body be-1 border-light">
                                    <div class="flexbox mb-1">
                                    <span>
                                        <span class="icon-Info-circle text-warning fs-40"><span class="path1"></span><span class="path2"></span><span class="path3"></span></span><br>
                                        Commandes annulées
                                    </span>
                                    <span class="text-warning fs-40" v-cloak>@{{ counts.cancelled }}</span>
                                    </div>
                                    <div class="progress progress-xxs mt-10 mb-0">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: 65%; height: 4px;" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-lg-3">
                                <div class="box-body">
                                    <div class="flexbox mb-1">
                                    <span>
                                        <span class="icon-Cart2 d-block fs-40 text-success"><span class="path1"></span><span class="path2"></span></span>
                                        Ventes journalières
                                    </span>
                                    <span class="text-success fs-40" v-cloak>@{{ counts.sells }}</span>
                                    </div>
                                    <div class="progress progress-xxs mt-10 mb-0">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: 40%; height: 4px;" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if(Auth::user()->role === 'admin' || Auth::user()->role === 'manager')
                <div class="col-12">
                    <div class="box">
                        <div class="box-header with-border p-4">
                            <h4 class="box-title">Tableau de bord administratif</h4>
                            <p class="text-muted mb-0">Aperçu des ventes et encaissements</p>
                        </div>
                        <div class="box-body">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <div class="box bg-primary text-white">
                                        <div class="box-body text-center">
                                            <h3 class="mb-0" id="admin-total-today">0</h3>
                                            <p class="mb-0">Aujourd'hui</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="box bg-success text-white">
                                        <div class="box-body text-center">
                                            <h3 class="mb-0" id="admin-total-week">0</h3>
                                            <p class="mb-0">Cette semaine</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="box bg-info text-white">
                                        <div class="box-body text-center">
                                            <h3 class="mb-0" id="admin-total-month">0</h3>
                                            <p class="mb-0">Ce mois</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="box bg-warning text-white">
                                        <div class="box-body text-center">
                                            <h3 class="mb-0" id="admin-total-services">0</h3>
                                            <p class="mb-0">Services</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-8">
                                    <div id="chart-admin-daily"></div>
                                </div>
                                <div class="col-lg-4">
                                    <div id="chart-admin-services"></div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-lg-6">
                                    <div id="chart-admin-modes"></div>
                                </div>
                                <div class="col-lg-6">
                                    <div id="chart-admin-emplacements"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if(Auth::user()->role === 'caissier')
                <div class="col-12">
                    <div class="box">
                        <div class="box-header with-border p-4">
                            <h4 class="box-title">Tableau de bord caissier</h4>
                            <p class="text-muted mb-0">Votre performance d'encaissement</p>
                        </div>
                        <div class="box-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="box bg-primary text-white">
                                        <div class="box-body text-center">
                                            <h3 class="mb-0" id="cashier-total-today">0</h3>
                                            <p class="mb-0">Aujourd'hui</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="box bg-success text-white">
                                        <div class="box-body text-center">
                                            <h3 class="mb-0" id="cashier-total-week">0</h3>
                                            <p class="mb-0">Cette semaine</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="box bg-info text-white">
                                        <div class="box-body text-center">
                                            <h3 class="mb-0" id="cashier-total-month">0</h3>
                                            <p class="mb-0">Ce mois</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-8">
                                    <div id="chart-cashier-daily"></div>
                                </div>
                                <div class="col-lg-4">
                                    <div id="chart-cashier-modes"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                 <div class="col-12 AppFacture" v-cloak data-rate="@lastRate">
                    <div class="box">
                        <div class="box-header p-4 d-sm-table d-lg-flex align-items-lg-center justify-content-between">
							<h4 class="box-title"><span class="text-primary fw-600">Liste des commandes en attente</span></h4>
                            @if (Auth::user()->role==='serveur')
                                <a @click="removeCachedUser" href="{{ route("orders.portal") }}" class="waves-effect waves-light btn btn-sm btn-primary text-center btn-rounded">+ Nouvelle commande</a>
                            @else
                                @canCloseDay
                                <a href="{{ route("serveurs") }}" class="waves-effect waves-light btn btn-primary btn-sm text-center btn-rounded">+ Nouvelle commande</a>
                                @endif
                            @endif

						</div>
                        <div class="box-body">
                            <div class="table-responsive rounded card-table">
                                <table class="table border-no" id="example1">
                                    <thead>
                                        <tr>
                                            <th>N° FAC</th>
                                            <th>Journée du</th>
                                            <th>Date facture</th>
                                            <th>N° Table/Chambre</th>
                                            <th>Emplacement</th>
                                            <th>Montant</th>
                                            <th>Serveur</th>
                                            <th>Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(data, index) in allFactures" class="hover-primary">
                                            <td>@{{ data.numero_facture }}</td>
                                            <td>@{{ data.sale_day ? formateDate(data.sale_day.sale_date) : '---' }}</td>
                                            <td>@{{ formateDate(data.date_facture) }},<span class="fs-12">@{{ formateTime(data.date_facture) }}</span></td>
                                            <td>
                                                <span v-if="data.table">Table n°@{{ data.table.numero}}</span>
                                                <span v-if="data.chambre">Chambre n°@{{ data.chambre.numero}}</span>
                                            </td>
                                            <td>
                                                <span v-if="data.table"> @{{ data.table.emplacement.libelle}}</span>
                                                <span v-if="data.chambre"> @{{ data.chambre.emplacement.libelle}}</span>
                                            </td>
                                            <td>@{{ data.total_ttc }}</td>
                                            <td>@{{ data.user.name }}</td>
                                            <td>
                                                <span class="badge badge-pill" :class="{'badge-warning':data.statut==='en_attente', 'badge-success':data.statut==='payée', 'badge-danger':data.statut==='annulée'}">@{{ data.statut.replaceAll('_', ' ') }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex">
                                                    <button type="button" class="btn btn-success btn-xs me-1" @click="printInvoice(data, data.table ? data.table.emplacement : data.chambre.emplacement)"><i class="mdi mdi-printer"></i></button>
                                                    <button type="button" :disabled="load_id===data.id" @click="servirCmd(data)" v-if="data.statut_service==='en_attente'" class="btn btn-warning btn-xs me-1">
                                                        <span v-if="load_id===data.id" class="spinner-border spinner-border-sm"></span>
                                                        <i v-else-if="data.table" class="fa fa-glass"></i>
                                                    </button>
                                                    @if (Auth::user()->hasRole("caissier") || Auth::user()->hasRole("admin"))
                                                        <button v-if="data.statut!=='payée'" type="button" @click="openPaymentModal(data)" class="btn btn-info me-1 btn-xs"><i class="fa fa-money"></i></button>
                                                    @endif
                                                    <button type="button" class="btn btn-primary btn-xs me-1" @click="selectedFacture = data" data-bs-toggle="modal" data-bs-target=".modal-invoice-detail"><i class="mdi mdi-eye"></i></button>
                                                    <button type="button" v-if="data.statut !== 'payée'" class="btn btn-danger-light btn-xs"><i class="mdi mdi-cancel"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3" v-if="pagination.total > 0">
                        <Paginator
                            :current-page="pagination.current_page"
                            :last-page="pagination.last_page"
                            :total-items="pagination.total"
                            :per-page="pagination.per_page"
                            @page-changed="changePage"
                            @per-page-changed="onPerPageChange"
                        />
                    </div>

                    <div class="modal fade modal-invoice-detail" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <!-- <button class="btn btn-success btn-sm me-2 rounded-3" @click="printInvoice"> <i class="mdi mdi-printer"></i></button>
                                    <button class="btn btn-primary btn-sm me-2 rounded-3"> <i class="mdi mdi-pencil"></i></button> -->
                                    <h4 class="modal-title" id="myModalLabel">Facture détails</h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <section v-if="selectedFacture" class="invoice border-0 p-0 printableArea">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="page-header">
                                                    <h2 class="d-inline"><span class="fs-30">@{{ selectedFacture.numero_facture }}</span></h2>
                                                    <div class="pull-right text-end">
                                                        <h3>@{{ formateDate(selectedFacture.date_facture) }}</h3>
                                                    </div>
                                                </div>
                                            </div>
                                        <!-- /.col -->
                                        </div>

                                        <div class="row" v-if="selectedFacture.details.length > 0">
                                            <div class="col-12 table-responsive">
                                                <table class="table table-bordered">
                                                <tbody>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Designation</th>
                                                    <th class="text-end">Quantité</th>
                                                    <th class="text-end">Prix unitaire</th>
                                                    <th class="text-end">Sous total</th>
                                                </tr>
                                                <tr v-for="(detail, index) in selectedFacture.details" :key="index">
                                                    <td>@{{ index+1 }}</td>
                                                    <td>@{{ detail.produit.libelle }}</td>
                                                    <td class="text-end">@{{ detail.quantite }}</td>
                                                    <td class="text-end">@{{ detail.prix_unitaire }}</td>
                                                    <td class="text-end">@{{ detail.total_ligne }}</td>
                                                </tr>
                                                </tbody>
                                                </table>
                                            </div>
                                            <!-- /.col -->
                                        </div>
                                        <div class="row" v-else-if="selectedFacture.chambre">
                                            <div class="col-12 table-responsive">
                                                <table class="table table-bordered">
                                                <tbody>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Chambre</th>
                                                    <th class="text-end">Capacité</th>
                                                    <th class="text-end">Type</th>
                                                    <th class="text-end">Prix</th>
                                                </tr>
                                                <tr>
                                                    <td>#1</td>
                                                    <td>Chambre n°@{{ selectedFacture.chambre.numero }}</td>
                                                    <td class="text-end">@{{ selectedFacture.chambre.capacite }}</td>
                                                    <td class="text-end">@{{  selectedFacture.chambre.type }}</td>
                                                    <td class="text-end">@{{  selectedFacture.chambre.prix }}</td>
                                                </tr>
                                                </tbody>
                                                </table>
                                            </div>
                                            <!-- /.col -->
                                        </div>
                                        <div class="row">
                                            <div class="col-12 text-end">
                                                <p class="lead d-print-none"><b>Statut : </b><span class="badge badge-pill" :class="{'badge-warning-light':selectedFacture.statut==='en_attente', 'badge-success-light':selectedFacture.statut==='payée', 'badge-danger-light':selectedFacture.statut==='annulée'}">@{{ selectedFacture.statut.replaceAll('_', ' ') }}</span></p>

                                                <div>
                                                    <p>Total HT  :  @{{ selectedFacture.total_ht }}</p>
                                                    <p>Remise (@{{ selectedFacture.remise }}%)  :  0</p>
                                                    <p>TVA  :  0</p>
                                                </div>
                                                <div class="total-payment">
                                                    <h3><b>Total TTC :</b> @{{ selectedFacture.total_ttc }}</h3>
                                                </div>
                                            </div>
                                            <!-- /.col -->
                                        </div>
                                    </section>
                                </div>

                            </div>
                            <!-- /.modal-content -->
                        </div>
                        <!-- /.modal-dialog -->
                    </div>

                    <div class="modal fade" id="modal-pay-trigger" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                                <div class="modal-header bg-white border-0 pt-4 px-4 pb-0">
                                    <h5 class="modal-title fw-900 text-dark text-uppercase letter-spacing-1">Encaisser la commande</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <div class="modal-body px-4" v-if="selectedFacture">
                                    <div class="bg-dark rounded-3 p-3 mb-3 shadow-inner position-relative overflow-hidden">
                                        <div class="position-absolute top-0 end-0 p-2 opacity-10">
                                            <i class="fa fa-receipt fa-4x" style="transform: rotate(15deg);"></i>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-end position-relative">
                                            <div>
                                                <small class="text-white-50 text-uppercase fw-bold letter-spacing-1" style="font-size: 10px;">Total net à payer</small>
                                                <h1 class="text-white fw-900 mb-0">@{{ selectedFacture.total_ttc.toLocaleString() }} <small class="fs-14">CDF</small></h1>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-primary px-3 py-2 fw-bold" style="font-size: 14px;">1$ = @{{ payment.rate || 0 }} CDF</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center bg-light rounded-3 p-3 border">
                                        <div>
                                            <small class="text-muted text-uppercase fw-bold fs-11">Taux appliqué</small>
                                            <div class="fw-800 text-dark">1 $ = @{{ payment.rate || 0 }} CDF</div>
                                        </div>
                                        <small class="text-muted">Équiv. à payer : <span class="fw-bold text-primary">@{{ amountToPayUSD }} $</span></small>
                                    </div>

                                    <div>
                                        <div class="d-flex justify-content-between align-items-center my-2">
                                            <label class="fw-bold text-dark mb-0">Montant reçu du client</label>
                                            <div class="btn-group btn-group-sm rounded-pill p-1 bg-light">
                                                <button class="btn rounded-pill border-0 px-3" :class="payment.currency === 'CDF' ? 'btn-primary shadow-sm' : 'btn-light'" @click="payment.currency = 'CDF'">CDF</button>
                                                <button class="btn rounded-pill border-0 px-3" :class="payment.currency === 'USD' ? 'btn-primary shadow-sm' : 'btn-light'" @click="payment.currency = 'USD'">USD</button>
                                            </div>
                                        </div>
                                        <div class="position-relative">
                                            <input type="number" class="form-control form-control-lg border-2 text-end fw-900 fs-30 text-primary pe-70" style="height: 70px; border-radius: 12px;" v-model.number="payment.amount_received" placeholder="0">
                                            <span class="position-absolute top-50 end-0 translate-middle-y me-3 fw-bold text-muted fs-18">@{{ payment.currency }}</span>
                                        </div>
                                    </div>

                                    <div class="row g-2 my-2">
                                        <template v-if="payment.currency === 'CDF'">
                                            <div class="col" v-for="val in [1000, 5000, 10000, 20000]">
                                                <button style="width: 100%" class="btn btn-outline-light text-dark fw-bold py-1 border-1 shadow-xs" style="border-radius: 10px; background: #f8f9fa;" @click="payment.amount_received += val">+@{{ val.toLocaleString() }}</button>
                                            </div>
                                        </template>
                                        <template v-else>
                                            <div class="col" v-for="val in [5, 10, 20, 50, 100]">
                                                <button style="width: 100%" class="btn btn-outline-light text-dark fw-bold py-1 border-1 shadow-xs" style="border-radius: 10px; background: #f8f9fa;" @click="payment.amount_received += val">+@{{ val }}$</button>
                                            </div>
                                        </template>
                                    </div>

                                    <div class="p-3 rounded-3 transition-all" :class="paymentChange >= 0 ? 'bg-success-light' : 'bg-danger-light'" style="border: 1px dashed currentcolor;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="fw-bold text-uppercase fs-11 letter-spacing-1">@{{ paymentChange >= 0 ? 'Rendu (Monnaie)' : 'Reste à percevoir' }}</span>
                                                <h3 class="fw-900 mb-0 mt-1">@{{ Math.abs(paymentChange).toLocaleString() }} <small class="fs-12">CDF</small></h3>
                                            </div>
                                            <div class="fs-30"><i class="mdi" :class="paymentChange >= 0 ? 'mdi-cash-plus text-success' : 'mdi-cash-minus text-danger'"></i></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer border-0 px-4">
                                    <button style="width: 100%" class="btn btn-primary btn-lg fw-bold shadow-lg" @click="triggerPayment" :disabled="paymentChange < 0 || isLoading">
                                        <i class="mdi mdi-check-circle-outline fs-20 me-2"></i> VALIDER LA TRANSACTION
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- <div class="col-12">
                    <div class="box bg-transparent no-shadow">
                        <div class="box-header pt-0 mb-0  px-0 d-flex align-items-center justify-content-between">
                            <h4 class="box-title">
                                Produits
                            </h4>
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent"><i
                                            class="ti-search text-primary"></i></span>
                                    <input type="text" class="form-control ps-15 bg-white"
                                        placeholder="Recherche...">
                                </div>
                            </div>
                        </div>
                        <div class="box-body px-0 pt-0 mt-0">
                            <div class="row">
                                <div class="col-xxxl-3 col-xl-4 col-lg-6 col-12">
                                    <div class="box food-box">
                                        <div class="box-body text-center">
                                        <div class="menu-item"><img src="assets/images/food/dish-1.png" class="img-fluid w-p75" alt=""></div>
                                        <div class="menu-details text-center">
                                            <h4 class="mt-20 mb-10">Kung Pao Tofu Recipe</h4>
                                            <p>Food/Noodle</p>
                                        </div>
                                        <div class="act-btn d-flex justify-content-between">
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-success-light btn-xs mb-5"><i class="fa fa-eye-slash"></i></a>
                                                <small class="d-block">View</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-danger-light btn-xs mb-5"><i class="fa fa-edit"></i></a>
                                                <small class="d-block">Edit</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-primary-light btn-xs mb-5"><i class="fa fa-trash"></i></a>
                                                <small class="d-block">Delete</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-info-light btn-xs mb-5"><i class="fa fa-plus-square-o"></i></a>
                                                <small class="d-block">Duplicate</small>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxxl-3 col-xl-4 col-lg-6 col-12">
                                    <div class="box food-box">
                                        <div class="box-body text-center">
                                        <div class="menu-item"><img src="assets/images/food/dish-2.png" class="img-fluid w-p75" alt=""></div>
                                        <div class="menu-details text-center">
                                            <h4 class="mt-20 mb-10">Pan Seared Salmon </h4>
                                            <p>Food/Noodle</p>
                                        </div>
                                        <div class="act-btn d-flex justify-content-between">
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-success-light btn-xs mb-5"><i class="fa fa-eye-slash"></i></a>
                                                <small class="d-block">View</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-danger-light btn-xs mb-5"><i class="fa fa-edit"></i></a>
                                                <small class="d-block">Edit</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-primary-light btn-xs mb-5"><i class="fa fa-trash"></i></a>
                                                <small class="d-block">Delete</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-info-light btn-xs mb-5"><i class="fa fa-plus-square-o"></i></a>
                                                <small class="d-block">Duplicate</small>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxxl-3 col-xl-4 col-lg-6 col-12">
                                    <div class="box food-box">
                                        <div class="box-body text-center">
                                        <div class="menu-item"><img src="assets/images/food/dish-3.png" class="img-fluid w-p75" alt=""></div>
                                        <div class="menu-details text-center">
                                            <h4 class="mt-20 mb-10">Dal Palak Recipe </h4>
                                            <p>Food/Noodle</p>
                                        </div>
                                        <div class="act-btn d-flex justify-content-between">
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-success-light btn-xs mb-5"><i class="fa fa-eye-slash"></i></a>
                                                <small class="d-block">View</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-danger-light btn-xs mb-5"><i class="fa fa-edit"></i></a>
                                                <small class="d-block">Edit</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-primary-light btn-xs mb-5"><i class="fa fa-trash"></i></a>
                                                <small class="d-block">Delete</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-info-light btn-xs mb-5"><i class="fa fa-plus-square-o"></i></a>
                                                <small class="d-block">Duplicate</small>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxxl-3 col-xl-4 col-lg-6 col-12">
                                    <div class="box food-box">
                                        <div class="box-body text-center">
                                        <div class="menu-item"><img src="assets/images/food/dish-4.png" class="img-fluid w-p75" alt=""></div>
                                        <div class="menu-details text-center">
                                            <h4 class="mt-20 mb-10">Vegetable Jalfrezi</h4>
                                            <p>Food/Noodle</p>
                                        </div>
                                        <div class="act-btn d-flex justify-content-between">
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-success-light btn-xs mb-5"><i class="fa fa-eye-slash"></i></a>
                                                <small class="d-block">View</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-danger-light btn-xs mb-5"><i class="fa fa-edit"></i></a>
                                                <small class="d-block">Edit</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-primary-light btn-xs mb-5"><i class="fa fa-trash"></i></a>
                                                <small class="d-block">Delete</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-info-light btn-xs mb-5"><i class="fa fa-plus-square-o"></i></a>
                                                <small class="d-block">Duplicate</small>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxxl-3 col-xl-4 col-lg-6 col-12">
                                    <div class="box food-box">
                                        <div class="box-body text-center">
                                        <div class="menu-item"><img src="assets/images/food/dish-5.png" class="img-fluid w-p75" alt=""></div>
                                        <div class="menu-details text-center">
                                            <h4 class="mt-20 mb-10">Palak Paneer Bhurji </h4>
                                            <p>Food/Noodle</p>
                                        </div>
                                        <div class="act-btn d-flex justify-content-between">
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-success-light btn-xs mb-5"><i class="fa fa-eye-slash"></i></a>
                                                <small class="d-block">View</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-danger-light btn-xs mb-5"><i class="fa fa-edit"></i></a>
                                                <small class="d-block">Edit</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-primary-light btn-xs mb-5"><i class="fa fa-trash"></i></a>
                                                <small class="d-block">Delete</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-info-light btn-xs mb-5"><i class="fa fa-plus-square-o"></i></a>
                                                <small class="d-block">Duplicate</small>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxxl-3 col-xl-4 col-lg-6 col-12">
                                    <div class="box food-box">
                                        <div class="box-body text-center">
                                        <div class="menu-item"><img src="assets/images/food/dish-6.png" class="img-fluid w-p75" alt=""></div>
                                        <div class="menu-details text-center">
                                            <h4 class="mt-20 mb-10">Kadai Paneer Gravy</h4>
                                            <p>Food/Noodle</p>
                                        </div>
                                        <div class="act-btn d-flex justify-content-between">
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-success-light btn-xs mb-5"><i class="fa fa-eye-slash"></i></a>
                                                <small class="d-block">View</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-danger-light btn-xs mb-5"><i class="fa fa-edit"></i></a>
                                                <small class="d-block">Edit</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-primary-light btn-xs mb-5"><i class="fa fa-trash"></i></a>
                                                <small class="d-block">Delete</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-info-light btn-xs mb-5"><i class="fa fa-plus-square-o"></i></a>
                                                <small class="d-block">Duplicate</small>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxxl-3 col-xl-4 col-lg-6 col-12">
                                    <div class="box food-box">
                                        <div class="box-body text-center">
                                        <div class="menu-item"><img src="assets/images/food/dish-1.png" class="img-fluid w-p75" alt=""></div>
                                        <div class="menu-details text-center">
                                            <h4 class="mt-20 mb-10">Gajar Matar Recipe</h4>
                                            <p>Food/Noodle</p>
                                        </div>
                                        <div class="act-btn d-flex justify-content-between">
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-success-light btn-xs mb-5"><i class="fa fa-eye-slash"></i></a>
                                                <small class="d-block">View</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-danger-light btn-xs mb-5"><i class="fa fa-edit"></i></a>
                                                <small class="d-block">Edit</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-primary-light btn-xs mb-5"><i class="fa fa-trash"></i></a>
                                                <small class="d-block">Delete</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-info-light btn-xs mb-5"><i class="fa fa-plus-square-o"></i></a>
                                                <small class="d-block">Duplicate</small>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxxl-3 col-xl-4 col-lg-6 col-12">
                                    <div class="box food-box">
                                        <div class="box-body text-center">
                                        <div class="menu-item"><img src="assets/images/food/dish-2.png" class="img-fluid w-p75" alt=""></div>
                                        <div class="menu-details text-center">
                                            <h4 class="mt-20 mb-10">Aloo Tamatar Ki Sabzi </h4>
                                            <p>Food/Noodle</p>
                                        </div>
                                        <div class="act-btn d-flex justify-content-between">
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-success-light btn-xs mb-5"><i class="fa fa-eye-slash"></i></a>
                                                <small class="d-block">View</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-danger-light btn-xs mb-5"><i class="fa fa-edit"></i></a>
                                                <small class="d-block">Edit</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-primary-light btn-xs mb-5"><i class="fa fa-trash"></i></a>
                                                <small class="d-block">Delete</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-info-light btn-xs mb-5"><i class="fa fa-plus-square-o"></i></a>
                                                <small class="d-block">Duplicate</small>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxxl-3 d-xxxl-none d-xl-block d-lg-none col-xl-4 col-lg-6 col-12">
                                    <div class="box food-box">
                                        <div class="box-body text-center">
                                        <div class="menu-item"><img src="assets/images/food/dish-3.png" class="img-fluid w-p75" alt=""></div>
                                        <div class="menu-details text-center">
                                            <h4 class="mt-20 mb-10">Vegan Thai Basil</h4>
                                            <p>Food/Noodle</p>
                                        </div>
                                        <div class="act-btn d-flex justify-content-between">
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-success-light btn-xs mb-5"><i class="fa fa-eye-slash"></i></a>
                                                <small class="d-block">View</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-danger-light btn-xs mb-5"><i class="fa fa-edit"></i></a>
                                                <small class="d-block">Edit</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-primary-light btn-xs mb-5"><i class="fa fa-trash"></i></a>
                                                <small class="d-block">Delete</small>
                                            </div>
                                            <div class="text-center mx-5">
                                                <a href="#" class="waves-effect waves-circle btn btn-circle btn-info-light btn-xs mb-5"><i class="fa fa-plus-square-o"></i></a>
                                                <small class="d-block">Duplicate</small>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div> -->
            </div>
        </section>
        <!-- /.content -->
    </div>
</div>
<!-- /.content-wrapper -->
@endsection

@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/service.js") }}"></script>
    <script type="module" src="{{ asset("assets/js/scripts/facture.js") }}"></script>
    <script type="module" src="{{ asset("assets/js/scripts/dashboard.js") }}"></script>
@endpush



