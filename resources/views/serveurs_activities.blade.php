@extends("layouts.admin")

@section("content")
<div class="content-wrapper">
    <div class="container-full AppService" v-cloak>
        <div class="content-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h4 class="page-title fw-bold">Controle des Activites Serveurs</h4>
                <div class="d-flex align-items-center gap-2">
                    <span v-if="pendingServeursReportsCount > 0" class="badge bg-warning text-dark px-3 py-2">
                        @{{ pendingServeursReportsCount }} rapport(s) en attente
                    </span>
                    <button class="btn btn-danger shadow-sm" @click="triggerClosingDay" :disabled="isClosingDayLoading">
                        <span v-if="isClosingDayLoading" class="spinner-border spinner-border-sm me-2"></span>
                        <i v-else class="fa fa-power-off me-2"></i> Cloturer la journee globale
                    </button>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="row g-4">
                <div class="col-xl-12">
                    @include("components.menus.serveurs")
                </div>

                <!-- Squelette de chargement -->
                <div v-if="isDataLoading" class="col-12 text-center py-100">
                    <div class="spinner-border text-primary" role="status"></div>
                    <h5 class="mt-3 text-muted">Analyse des ventes en cours...</h5>
                </div>

                <!-- Liste des serveurs actifs -->
                <template v-else>
                    <div class="col-xl-4 col-md-6" v-for="srv in allServeurs" :key="srv.user_id">
                        <div class="box shadow-sm border-start border-4" :class="srv.rapport_statut === 'done' ? 'border-success' : 'border-warning'">
                            <div class="box-body p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-light rounded-circle p-2 me-3">
                                        <img src="assets/images/service.jpg" class="rounded-circle" width="50">
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="fw-bold mb-0">@{{ srv.user.name }}</h5>
                                        <span class="badge badge-dot me-1" :class="srv.user.last_log && srv.user.last_log.status === 'online' ? 'badge-success' : 'badge-danger'"></span>
                                        <small class="text-muted text-uppercase">@{{ srv.user.emplacement ? srv.user.emplacement.libelle : 'Serveur Mobile' }}</small>
                                    </div>
                                    <div class="text-end" v-if="srv.rapport_statut === 'done'">
                                        <i class="fa fa-check-circle text-success fs-20"></i>
                                    </div>
                                </div>

                                <div class="row text-center bg-light rounded py-3 mb-3 mx-0">
                                    <div class="col-6 border-end">
                                        <h4 class="fw-bold mb-0 text-primary">@{{ srv.total_encaisse.toLocaleString() }}</h4>
                                        <small class="text-muted text-uppercase fs-10">Total Facture</small>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="fw-bold mb-0 text-dark">@{{ srv.total_ticket }}</h4>
                                        <small class="text-muted text-uppercase fs-10">Tickets Emis</small>
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button v-if="srv.rapport_statut === 'none'"
                                            @click="triggerSingleClosing(srv)"
                                            class="btn btn-primary-light btn-sm fw-bold border-primary">
                                        <i class="fa fa-balance-scale me-2"></i> Verifier et cloturer
                                    </button>
                                    <div v-else class="alert alert-success py-2 mb-0 text-center small fw-bold">
                                        <i class="fa fa-lock me-2"></i> SESSION CLOTUREE
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Si aucun serveur n'a vendu -->
                <div v-if="!isDataLoading && allServeurs.length === 0" class="col-12 text-center py-100">
                    <img src="{{ asset('assets/images/no-data.png') }}" width="150" class="mb-3 opacity-50">
                    <h4 class="text-muted">Aucune vente enregistree pour cette journee.</h4>
                </div>
            </div>

            <!-- MODAL DE CLOTURE DETAILLEE -->
            <div id="reportAppendModal" class="modal fade" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <form class="modal-content border-0 shadow-lg" @submit.prevent="triggerSendServeurReport">
                        <div class="modal-header bg-primary text-white py-3">
                            <h5 class="modal-title fw-bold"><i class="fa fa-balance-scale me-2"></i> Rapport de fin de service</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4" v-if="selectedData">
                            <div class="text-center mb-4">
                                <h4 class="fw-bold text-dark mb-1">@{{ selectedData.user.name }}</h4>
                                <p class="text-muted small">Controle des encaissements especes et tickets</p>
                            </div>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold"><i class="fa fa-money me-2"></i>Especes remises par le serveur</label>
                                    <div class="input-group input-group-lg shadow-sm">
                                        <input type="number" v-model.number="form.total_especes" class="form-control border-primary" placeholder="0" required>
                                        <span class="input-group-text bg-primary text-white fw-bold">F</span>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2 px-1">
                                        <small class="text-muted">Theorique: <strong>@{{ selectedData.total_encaisse }} F</strong></small>
                                        <small :class="selectedData.total_encaisse - form.total_especes > 0 ? 'text-danger' : 'text-success'" class="fw-bold">
                                            Ecart: @{{ selectedData.total_encaisse - form.total_especes }} F
                                        </small>
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <label class="form-label fw-bold"><i class="fa fa-ticket me-2"></i>Tickets physiques rapportes</label>
                                    <div class="input-group shadow-sm">
                                        <input type="number" v-model.number="form.tickets_serveur" class="form-control border-info" placeholder="Nombre de souches" required>
                                        <span class="input-group-text bg-info text-white"><i class="fa fa-copy"></i></span>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2 px-1">
                                        <small class="text-muted">Systeme: <strong>@{{ selectedData.total_ticket }} tickets</strong></small>
                                        <small :class="selectedData.total_ticket - form.tickets_serveur !== 0 ? 'text-danger' : 'text-success'" class="fw-bold">
                                            Diff: @{{ selectedData.total_ticket - form.tickets_serveur }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-top p-3 d-flex justify-content-between">
                            <button type="button" class="btn btn-dark btn-sm" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-success px-4 shadow-sm" :disabled="isLoading">
                                <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span>
                                <i v-else class="fa fa-lock me-2"></i> Valider la cloture serveur
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="missingServeurReportsModal" class="modal fade" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-warning">
                            <h5 class="modal-title fw-bold text-uppercase">
                                <i class="fa fa-exclamation-triangle me-2"></i> Rapports manquants avant cloture
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="alert alert-warning border mb-3">
                                <p class="fw-bold mb-1">@{{ missingReportsMessage || "Certains serveurs actifs doivent encore remettre leur rapport de caisse." }}</p>
                                <small class="text-dark">
                                    Serveurs en attente: <strong>@{{ missingServeursReports.length }}</strong> |
                                    Montant vendu: <strong>@{{ formatAmount(missingReportsTotal) }} F</strong>
                                </small>
                            </div>

                            <div v-if="missingServeursReports.length === 0" class="text-center py-4">
                                <i class="fa fa-check-circle text-success fs-2 d-block mb-2"></i>
                                <h5 class="fw-bold mb-1">Tous les rapports serveurs ont ete enregistres.</h5>
                                <p class="text-muted mb-0">Relancez la cloture pour terminer la journee.</p>
                            </div>

                            <div v-else class="table-responsive">
                                <table class="table table-bordered table-hover align-middle mb-0">
                                    <thead class="table-light">
                                    <tr>
                                        <th>Serveur</th>
                                        <th class="text-end">Montant vendu</th>
                                        <th class="text-center">Tickets caisse</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="srv in missingServeursReports" :key="'missing-' + (srv.user_id || srv.serveur_id)">
                                        <td>
                                            <strong>@{{ srv.user ? srv.user.name : (srv.name || 'Serveur') }}</strong>
                                            <div class="text-muted small">
                                                @{{ srv.user && srv.user.emplacement ? srv.user.emplacement.libelle : (srv.emplacement ? srv.emplacement.libelle : 'Sans emplacement') }}
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold text-primary">@{{ formatAmount(srv.montant_vendu || srv.total_encaisse) }} F</td>
                                        <td class="text-center">@{{ srv.total_ticket || 0 }}</td>
                                        <td class="text-center">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-primary"
                                                @click="triggerSingleClosing(srv, { fromMissingModal: true })"
                                            >
                                                <i class="fa fa-balance-scale me-1"></i> Effectuer le rapport
                                            </button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-top d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary" @click="refreshMissingServeursReports" :disabled="isRefreshingMissingReports">
                                <span v-if="isRefreshingMissingReports" class="spinner-border spinner-border-sm me-2"></span>
                                <i v-else class="fa fa-refresh me-2"></i> Actualiser la liste
                            </button>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Fermer</button>
                                <button
                                    type="button"
                                    class="btn btn-success"
                                    @click="triggerClosingDay({ skipConfirm: true })"
                                    :disabled="missingServeursReports.length > 0 || isClosingDayLoading"
                                >
                                    <span v-if="isClosingDayLoading" class="spinner-border spinner-border-sm me-2"></span>
                                    <i v-else class="fa fa-lock me-2"></i> Relancer la cloture
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @include("components.modals.day_report")
        </section>
    </div>
</div>
@endsection

@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/service.js") }}"></script>
@endpush
