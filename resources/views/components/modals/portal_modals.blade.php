<!-- Modal Commandes (Design Professionnel POS) -->
<div class="modal fade modal-commande" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" v-if="selectedPendingTable">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">Bons de commande Table @{{ selectedPendingTable.numero }}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex">
                    <button @click="goToOrderPannel(selectedPendingTable, true)" class="btn btn-primary btn-xs mb-20 me-2">+ Nouveau bon de commande</button>
                    <button v-if="selectedPendingTable.commandes.length === 0" @click="libererTable(selectedPendingTable)" class="btn btn-danger btn-xs mb-20 me-2">Liberer table <i class="mdi mdi-arrange-bring-forward"></i></button>
                    <button v-if="selectedPendingTable.commandes.length > 1" class="btn btn-info btn-xs mb-20" @click="fusionnerCmds(selectedPendingTable.commandes)"><i class="mdi mdi-link me-1"></i>Fusionner les commandes</button>
                </div>
                <div class="row g-2">
                    <div class="col-12 col-lg-6 mb-3" v-for="(cmd, index) in selectedPendingTable.commandes">
                        <div class="box ribbon-box border-1 b-1 border-primary rounded-3 shadow-sm">
                            <!-- Ribbon statut -->
                            <div class="ribbon-two ribbon-two-primary" v-if="cmd.statut_service==='servie'">
                                <span v-if="cmd.statut_service==='servie'">Servie</span>
                                <span v-else>En attente</span>
                            </div>

                            <div class="box-body m-0">
                                <!-- Titre -->
                                <div class="text-center border-bottom pb-2 mb-3">
                                    <h5 class="fw-bold text-primary mb-0">
                                        <i class="mdi mdi-file-document-outline me-2"></i>
                                        Bon de Commande N°@{{ cmd.id }}
                                    </h5>
                                </div>

                                <!-- Actions -->
                                <div class="d-flex flex-wrap justify-content-center gap-2">
                                    <!-- Edit -->
                                    <button  @click="editCommande(cmd)" class="btn btn-circle btn-sm btn-primary">
                                        <i class="fa fa-pencil"></i>
                                    </button>

                                    <!-- Impression -->
                                    <button class="btn btn-circle btn-sm btn-success"
                                            @click="printInvoiceFromJson(cmd, selectedPendingTable.emplacement)">
                                        <i class="fa fa-print"></i>
                                    </button>

                                    <!-- Servir -->
                                    <button v-if="cmd.statut_service==='en_attente'"
                                            @click="servirCmd(cmd)"
                                            class="btn btn-circle btn-sm btn-warning">
                                        <i class="fa fa-glass"></i>
                                    </button>

                                    <!-- Paiement -->
                                    @if (Auth::user()->hasRole("caissier") || Auth::user()->hasRole("admin"))
                                        <button @click="openPaymentModal(cmd)"
                                                class="btn btn-circle btn-sm btn-dark">
                                            <span v-if="load_id===cmd.id" class="spinner-border spinner-border-sm"></span>
                                            <i v-else class="fa fa-money"></i>
                                        </button>
                                    @endif

                                    <!-- Voir facture -->
                                    <button class="btn btn-circle btn-sm btn-info"
                                            @click="viewInvoiceDetail(cmd)">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- MODAL DE PAIEMENT "DESIGN POS PRO" -->
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

<!-- Modal Détails Facture -->
<div class="modal fade modal-invoice-detail" id="modal-invoice-detail" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Détails de la commande</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" v-if="selectedFacture">
                <div class="p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-primary">Bon N°@{{ selectedFacture.id }}</h5>
                        <small class="text-muted">@{{ formateDate2(selectedFacture.date_facture) }}</small>
                    </div>
                    <span class="badge rounded-pill" :class="selectedFacture.statut === 'payée' ? 'bg-success' : 'bg-warning'">
                        @{{ selectedFacture.statut.toUpperCase() }}
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead><tr><th class="ps-4">Designation</th><th class="text-center">Quantité</th><th class="text-end pe-4">Total</th></tr></thead>
                        <tbody>
                            <tr v-for="detail in selectedFacture.details">
                                <td class="ps-4">@{{ detail.produit.libelle }}</td>
                                <td class="text-center">@{{ detail.quantite }}</td>
                                <td class="text-end pe-4 fw-bold">@{{ detail.total_ligne }} F</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr><td colspan="2" class="text-end ps-4">TOTAL TTC</td><td class="text-end pe-4 text-primary fs-18">@{{ selectedFacture.total_ttc }} F</td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

