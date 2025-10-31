
@extends("layouts.admin")

@section("content")
<div class="content-wrapper">
    <div class="container-full">
        <!-- Content Header (Page header) -->	  
        <div class="content-header">
            <div class="d-flex align-items-center justify-content-between">
                <div class="me-auto">
                    <h3 class="page-title">Liste des commandes</h3>
                    <!-- <div class="d-inline-block align-items-center">
                        <nav>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="mdi mdi-home-outline"></i></a></li>
                                <li class="breadcrumb-item" aria-current="page">Commandes</li>
                                <li class="breadcrumb-item active" aria-current="page">Listes des commandes</li>
                            </ol>
                        </nav>
                    </div> -->
                </div>
                @if (Auth::user()->role==='serveur')
                    <a @click="removeCachedUser" href="{{ route("orders.portal") }}" class="waves-effect waves-light btn btn-danger btn-sm text-center btn-rounded">+ Nouvelle commande</a>
                @else
                    @canCloseDay
                    <a href="{{ route("serveurs") }}" class="waves-effect waves-light btn btn-danger btn-sm text-center btn-rounded">+ Nouvelle commande</a>
                    @endif
                @endif
            </div>
        </div>

        <!-- Main content -->
        <section class="content AppFacture" v-cloak>
            <div class="row">
                <div class="col-12">
                    <div class="box">
                        <div class="box-body">
                            <div class="table-responsive rounded card-table">
                                <table class="table border-no" id="example1">
                                    <thead>
                                        <tr>
                                            <th>N° Cmde</th>
                                            <th>N° FACTURE</th>
                                            <th>N° Table/Chambre</th>
                                            <th>Montant</th>
                                            <th>Emplacement</th>
                                            <th>Serveur</th>
                                            <th>Délai</th>
                                            <th>Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(data, index) in allFactures" class="hover-primary">
                                            <td>N°@{{ data.id }}</td>
                                            <td>@{{ data.numero_facture }}</td>
                                            <td>
                                                <span v-if="data.table">@{{ data.table.numero}}</span>
                                                <span v-if="data.chambre">@{{ data.chambre.numero}}</span>
                                            </td>
                                            <td>@{{ data.total_ttc }}</td>
                                            <td>
                                                <span v-if="data.table">@{{ data.table.emplacement.libelle}}</span>
                                                <span v-if="data.chambre">@{{ data.chambre.emplacement.libelle}}</span>
                                            </td>
                                            <td><span class="fw-600">@{{ data.user.name}}</span></td>
                                            <td>												
                                                --
                                            <td>												
                                                <span class="badge badge-pill" :class="{'badge-warning-light':data.statut==='en_attente', 'badge-success-light':data.statut==='payée', 'badge-danger-light':data.statut==='annulée'}">@{{ data.statut.replaceAll('_', ' ') }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex">
                                                    <button type="button" class="btn btn-success btn-xs me-1" @click="printInvoice(data, data.table ? data.table.emplacement : data.chambre.emplacement)"><i class="mdi mdi-printer"></i></button>
                                                    <button type="button" :disabled="load_id===data.id" @click="servirCmd(data)" v-if="data.statut_service==='en_attente'" class="btn btn-warning btn-xs me-1">
                                                        <span v-if="load_id===data.id" class="spinner-border spinner-border-sm"></span> 
                                                        <i v-else-if="data.table" class="fa fa-glass"></i>
                                                    </button>
                                                    @if (Auth::user()->hasRole("caissier") || Auth::user()->hasRole("admin"))
                                                        <button type="button" @click="selectedFacture = data" data-bs-toggle="modal" data-bs-target=".modal-pay-trigger" class="btn btn-info me-1 btn-xs"><i class="fa fa-money"></i></button>
                                                    @endif
                                                    <button type="button" class="btn btn-primary btn-xs me-1" @click="selectedFacture = data" data-bs-toggle="modal" data-bs-target=".modal-invoice-detail"><i class="mdi mdi-eye"></i></button>
                                                  <button 
                                                        type="button" 
                                                        class="btn btn-danger-light btn-xs"
                                                        @click="removeCommande(data)"
                                                        :disabled="isLoading"
   
                                                    >
                                                        <i class="mdi mdi-cancel"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade modal-invoice-detail" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
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
                                <div class="row" v-else>
                                    <div class="col-12 table-responsive">
                                        <table class="table table-bordered">
                                        <tbody>
                                        <tr>
                                            <th>#</th>
                                            <th>Designation</th>
                                            <th class="text-end">Capacité</th>
                                            <th class="text-end">Type</th>
                                            <th class="text-end">Prix</th>
                                        </tr>
                                        <tr>
                                            <td>1</td>
                                            <td>Chambre n°@{{ selectedFacture.chambre.numero }}</td>
                                            <td class="text-end">@{{ selectedFacture.chambre.capacite }}</td>
                                            <td class="text-end">@{{ selectedFacture.chambre.type }}</td>
                                            <td class="text-end">@{{ selectedFacture.chambre.prix }}</td>
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

            <!-- Modal mode de paiement -->
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
                                <button class="btn btn-success mt-5" style="width: 100%;" @click="triggerPayment">
                                    Valider <i class="mdi mdi-check-all"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
        </section>
        <!-- /.content -->
    </div>
</div>
@endsection

@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/facture.js") }}"></script>
@endpush
