

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
        <section class="content AppFacture" v-cloak data-rate="@lastRate">
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
                                                <span class="badge badge-pill" :class="{'badge-warning-light':data.statut==='en_attente', 'badge-success-light':data.statut==='payÃ©e', 'badge-danger-light':data.statut==='annulÃ©e'}">@{{ data.statut.replaceAll('_', ' ') }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex">
                                                    <button type="button" class="btn btn-success btn-xs me-1" @click="printInvoice(data, data.table ? data.table.emplacement : data.chambre.emplacement)"><i class="mdi mdi-printer"></i></button>
                                                    <button type="button" :disabled="load_id===data.id" @click="servirCmd(data)" v-if="data.statut_service==='en_attente'" class="btn btn-warning btn-xs me-1">
                                                        <span v-if="load_id===data.id" class="spinner-border spinner-border-sm"></span>
                                                        <i v-else-if="data.table" class="fa fa-glass"></i>
                                                    </button>
                                                    @if (Auth::user()->hasRole("caissier") || Auth::user()->hasRole("admin"))
                                                        <button type="button" @click="openPaymentModal(data)" class="btn btn-info me-1 btn-xs"><i class="fa fa-money"></i></button>
                                                    @endif
                                                    <button type="button" class="btn btn-primary btn-xs me-1" @click="selectedFacture = data" data-bs-toggle="modal" data-bs-target=".modal-invoice-detail"><i class="mdi mdi-eye"></i></button>
                                                        <button type="button" class="btn btn-danger-light btn-xs" @click="supprimerFacture(data)"><i class="mdi mdi-cancel"></i></button>
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
                            <h4 class="modal-title" id="myModalLabel">Facture dÃ©tails</h4>
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
                                            <th class="text-end">QuantitÃ©</th>
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
                                            <th class="text-end">CapacitÃ©</th>
                                            <th class="text-end">Type</th>
                                            <th class="text-end">Prix</th>
                                        </tr>
                                        <tr>
                                            <td>1</td>
                                            <td>Chambre nÂ°@{{ selectedFacture.chambre.numero }}</td>
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
                                        <p class="lead d-print-none"><b>Statut : </b><span class="badge badge-pill" :class="{'badge-warning-light':selectedFacture.statut==='en_attente', 'badge-success-light':selectedFacture.statut==='payÃ©e', 'badge-danger-light':selectedFacture.statut==='annulÃ©e'}">@{{ selectedFacture.statut.replaceAll('_', ' ') }}</span></p>

                                        <div>
                                            <p>Total HT  :  @{{ selectedFacture.total_ht }}</p>
                                            <p>Remise (@{{ selectedFacture.remise }}%)  :  0</p>
                                            <p>TVA  :  @{{ selectedFacture.tva }}</p>
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

            <!-- Modal mode de paiement (design harmonisé) -->
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

        </section>
        <!-- /.content -->
    </div>
</div>
@endsection

@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/facture.js") }}"></script>
@endpush





