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
                    <a href="{{ route("orders.portal") }}" class="waves-effect waves-light btn btn-danger text-center btn-rounded"><i class="fa fa-sign-out"></i> Clôturer la journée</a>
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

                 <div class="col-12 AppFacture" v-cloak>
                    <div class="box">
                        <div class="box-header p-4 d-sm-table d-lg-flex align-items-lg-center justify-content-between">
							<h4 class="box-title"><span class="text-primary fw-600">Liste des commandes en attente</span></h4>
                            @if (Auth::user()->role==='serveur')
                                <a @click="removeCachedUser" href="{{ route("orders.portal") }}" class="waves-effect waves-light btn btn-primary text-center btn-rounded">+ Nouvelle commande</a>
                            @else
                                <a href="{{ route("serveurs") }}" class="waves-effect waves-light btn btn-primary text-center btn-rounded">+ Nouvelle commande</a>
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
                                            <td>@{{ formateDate(data.sale_day.sale_date) }}</td>
                                            <td>@{{ formateDate(data.date_facture) }},<span class="fs-12">@{{ formateTime(data.date_facture) }}</span></td>
                                            <td>@{{ data.table.numero}}</td>
                                            <td>@{{ data.table.emplacement.libelle}}</td>
                                            <td>@{{ data.total_ttc }}</td>
                                            <td>@{{ data.user.name }}</td>
                                            <td>												
                                                <span class="badge badge-pill" :class="{'badge-warning':data.statut==='en_attente', 'badge-success':data.statut==='payée', 'badge-danger':data.statut==='annulée'}">@{{ data.statut.replaceAll('_', ' ') }}</span>
                                            </td>
                                            <td>												
                                                <div class="d-flex">
                                                    <button type="button" class="btn btn-success btn-xs me-1" @click="printInvoice(data, data.table.emplacement)"><i class="mdi mdi-printer"></i></button>
                                                    <button type="button"  @click="selectedFacture = data" data-bs-toggle="modal" data-bs-target=".modal-pay-trigger" v-if="data.statut==='en_attente'" class="btn btn-info btn-xs me-1"><span v-if="load_id===data.id" class="spinner-border spinner-border-sm"></span> <i v-else class="mdi mdi-glass-tulip"></i></button>
                                                    <button type="button" class="btn btn-primary btn-xs me-1" @click="selectedFacture = data" data-bs-toggle="modal" data-bs-target=".modal-invoice-detail"><i class="mdi mdi-eye"></i></button>
                                                    <button type="button" class="btn btn-danger-light btn-xs"><i class="mdi mdi-cancel"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade modal-invoice-detail" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button class="btn btn-success btn-sm me-2 rounded-3" @click="printInvoice"> <i class="mdi mdi-printer"></i></button>
                                    <button class="btn btn-primary btn-sm me-2 rounded-3"> <i class="mdi mdi-pencil"></i></button>
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
                                    
                                        <div class="row" v-if="selectedFacture.details">
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
                    
                    <div class="modal fade modal-pay-trigger" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content" v-if="selectedFacture">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="myModalLabel">Servir le bon de commande n°@{{ selectedFacture.id }}</h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="text-danger">Sélectionnez un mode de paiement.</p>
                                    <div class="flexbox flex-justified text-center">
                                        <a href="#"
                                            v-for="mode in modes" 
                                            @click="selectedMode=mode.value; selectedModeRef=''"
                                            class="b-1 border-primary text-decoration-none rounded py-20 cursor-pointer"
                                            :class="selectedMode && selectedMode === mode.value ? 'bg-primary text-white' :'text-primary bg-white'"
                                        >
                                            <p class="mb-0 fa-3x">
                                                <i :class="mode.icon"></i>
                                            </p>
                                            <p class="mb-2 fw-300">@{{ mode.label }}</p>
                                        </a>
                                    </div>
                                    <!-- Input de référence uniquement si le mode n'est pas CASH et qu'un mode est sélectionné -->
                                    <input 
                                        v-if="selectedMode && selectedMode !== 'cash'" 
                                        type="text" 
                                        v-model="selectedModeRef"
                                        placeholder="Réference du mode de paiement ..." 
                                        class="form-control mt-2 mb-2"
                                    >

                                    <div v-if="selectedMode" class="d-flex justify-content-center align-items-center">
                                        <button class="btn btn-success mt-5" @click="triggerPayment">
                                            Valider <i class="mdi mdi-check-all"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- /.modal-content -->
                        </div>
                        <!-- /.modal-dialog -->
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
    <script type="module" src="{{ asset("assets/js/scripts/facture.js") }}"></script>
    <script type="module" src="{{ asset("assets/js/scripts/dashboard.js") }}"></script>
@endpush



