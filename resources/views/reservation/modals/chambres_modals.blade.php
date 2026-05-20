<!-- Modal Create Reservation -->
<div class="modal fade modal-reservation-create" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content border-0 shadow-lg" @submit.prevent="createReservation" style="border-radius: 24px;">
            <div class="modal-header border-light p-4">
                <h4 class="modal-title fw-800">Nouvelle Réservation</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" v-if="selectedChambre">
                <!-- Info Chambre Soft Design -->
                <div class="d-flex align-items-center p-3 mb-4 rounded-4 bg-light">
                    <div class="me-3 bg-white shadow-sm rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                        <svg v-if="selectedChambre.statut==='libre'" viewBox="0 0 24 24" width="32" height="32" stroke="#0d6efd" stroke-width="2" fill="none">
                            <path d="M2 20v-8a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v8"></path>
                            <path d="M4 10V6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v4"></path>
                        </svg>
                        <svg v-else viewBox="0 0 24 24" width="32" height="32" stroke="#ef4444" stroke-width="2" fill="none">
                            <circle cx="12" cy="13" r="3"></circle>
                            <path d="M7 20c0-2.8 2.2-5 5-5s5 2.2 5 5"></path>
                        </svg>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="mb-0 fw-700">Chambre #@{{ selectedChambre.numero }}</h5>
                        <p class="text-muted mb-0">@{{ selectedChambre.type }} • @{{ selectedChambre.emplacement?.libelle || 'Standard' }}</p>
                    </div>
                    <div class="text-end">
                        <span class="badge rounded-pill" :class="selectedChambre.statut==='libre' ? 'bg-success-light text-success' : 'bg-danger-light text-danger'">
                            @{{ selectedChambre.statut }}
                        </span>
                    </div>
                </div>

                <!-- Modern Stepper -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between stepper-modern">
                        <div class="step-item" :class="{ active: step >= 1 }">
                            <span class="step-num">1</span>
                            <span class="step-label">Informations</span>
                        </div>
                        <div class="step-line"></div>
                        <div class="step-item" :class="{ active: step >= 2 }">
                            <span class="step-num">2</span>
                            <span class="step-label">Paiement</span>
                        </div>
                    </div>
                </div>

                <!-- STEP 1 : RESERVATION -->
                <div v-if="step === 1" class="animate__animated animate__fadeIn">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-600">Type de pièce</label>
                            <select class="form-select rounded-3" v-model="form.client.identite_type" required>
                                <option hidden value="">--Sélectionnez--</option>
                                <option>Carte d'Identité</option>
                                <option>Passeport</option>
                                <option>Carte d'Electeur</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">N° de la pièce</label>
                            <input type="text" class="form-control rounded-3" v-model="form.client.identite" placeholder="NID..." required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-600">Nom complet du client</label>
                            <input type="text" class="form-control rounded-3" v-model="form.client.nom" placeholder="Ex. Jean Dupont" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Téléphone</label>
                            <input type="text" class="form-control rounded-3" v-model="form.client.telephone" placeholder="+243..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Type de séjour</label>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary flex-fill rounded-3 py-2" :class="{active: form.type_sejour==='nuit'}" @click="form.type_sejour='nuit'">Nuitée</button>
                                <button type="button" class="btn btn-outline-primary flex-fill rounded-3 py-2" :class="{active: form.type_sejour==='passage'}" @click="form.type_sejour='passage'">Passage</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Date d'arrivée</label>
                            <input type="date" class="form-control rounded-3" v-model="form.date_debut" required>
                        </div>
                        <div class="col-md-6" v-if="form.type_sejour === 'nuit'">
                            <label class="form-label fw-600">Date de départ</label>
                            <input type="date" class="form-control rounded-3" v-model="form.date_fin" required>
                        </div>
                    </div>
                </div>

                <!-- STEP 2 : PAIEMENT -->
                <div v-if="step === 2" class="animate__animated animate__fadeIn">
                    <div class="payment-summary mb-4 p-3 rounded-4 bg-primary-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 fw-700">Total à payer</h6>
                                <small class="text-primary fw-600" v-if="form.type_sejour === 'nuit'">Séjour de @{{ getDays(form.date_debut, form.date_fin) }} nuits</small>
                                <small class="text-primary fw-600" v-else>Tarif Passage</small>
                            </div>
                            <div class="text-end">
                                <h3 class="fw-900 text-primary mb-0">@{{ form.paiement.amount }} @{{ form.paiement.devise }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-600">Mode de paiement</label>
                            <div class="d-flex flex-wrap gap-2">
                                <div v-for="mode in ['cash', 'mobile', 'virement', 'card']" class="payment-mode-item flex-fill">
                                    <input type="radio" :id="'mode-'+mode" :value="mode" v-model="form.paiement.mode" class="btn-check">
                                    <label :for="'mode-'+mode" class="btn btn-outline-light text-dark w-100 py-3 rounded-3 text-capitalize fw-600 border">
                                        @{{ mode }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12" v-if="form.paiement.mode && form.paiement.mode !== 'cash'">
                            <label class="form-label fw-600">Référence de transaction</label>
                            <input type="text" class="form-control rounded-3" v-model="form.paiement.mode_ref" placeholder="ID Transaction, N° Reçu..." required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Annuler</button>
                <button type="button" v-if="step === 1" @click="step = 2" class="btn btn-primary rounded-pill px-4 shadow-sm">Suivant</button>
                <button type="submit" v-if="step === 2" class="btn btn-success rounded-pill px-4 shadow-sm" :disabled="isLoading">
                    <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span>
                    Confirmer la réservation
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Action Reservation -->
<div class="modal fade modal-reservation-action" tabindex="-1" role="dialog" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 28px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h4 class="modal-title fw-800">Gestion Chambre</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div v-if="selectedChambre">
                    <!-- Réservation Active Card -->
                    <div class="active-res-card mb-4 p-3 rounded-4" v-if="selectedReservation">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="badge bg-primary-light text-primary mb-2">Réservation Active</span>
                                <h5 class="fw-800 mb-0">@{{ selectedReservation.client?.nom }}</h5>
                            </div>
                            <span class="status-badge" :class="selectedReservation.statut">@{{ selectedReservation.statut }}</span>
                        </div>
                        <div class="row g-2 fs-14">
                            <div class="col-6">
                                <span class="text-muted d-block">Période</span>
                                <span class="fw-600">Du @{{ formateDate(selectedReservation.date_debut) }}</span>
                            </div>
                            <div class="col-6 text-end">
                                <span class="text-muted d-block">Type</span>
                                <span class="fw-600">@{{ selectedReservation.type_sejour }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid-actions">
                        <button @click="triggerOpenCreateReservationModal(selectedChambre)" class="action-btn-modern">
                            <div class="icon bg-primary-light text-primary"><i class="mdi mdi-plus-circle-outline"></i></div>
                            <span>Nouvelle Réservation</span>
                        </button>

                        <button @click="occuperChambre(selectedChambre)" class="action-btn-modern"
                                :class="{'text-danger': selectedChambre.statut==='réservée', 'text-success': selectedChambre.statut==='occupée'}">
                            <div class="icon" :class="selectedChambre.statut==='réservée' ? 'bg-danger-light' : 'bg-success-light'">
                                <span v-if="isLoading" class="spinner-border spinner-border-sm"></span>
                                <i v-else class="mdi" :class="selectedChambre.statut==='réservée' ? 'mdi-account-check' : 'mdi-door-open'"></i>
                            </div>
                            <span>@{{ selectedChambre.statut === 'réservée' ? 'Encaisser / Occuper' : 'Libérer la Chambre' }}</span>
                        </button>
                    </div>

                    <div class="d-flex gap-2 mt-4" v-if="selectedReservation">
                        <button class="btn btn-outline-info flex-fill rounded-pill py-2 fw-600" @click="triggerOpenUpdateReservationModal(selectedReservation)">
                            <i class="mdi mdi-pencil me-1"></i> Modifier
                        </button>
                        <button class="btn btn-outline-primary flex-fill rounded-pill py-2 fw-600" @click="triggerExtendDayModal(selectedReservation)">
                            <i class="mdi mdi-calendar-plus me-1"></i> Prolonger
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .stepper-modern { position: relative; }
    .step-item { position: relative; z-index: 2; display: flex; flex-direction: column; align-items: center; gap: 8px; }
    .step-num { width: 32px; height: 32px; border-radius: 50%; background: #f1f5f9; color: #94a3b8; display: flex; align-items: center; justify-content: center; font-weight: 700; transition: all 0.3s; }
    .step-item.active .step-num { background: #0d6efd; color: #fff; transform: scale(1.1); box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3); }
    .step-label { font-size: 12px; font-weight: 600; color: #94a3b8; }
    .step-item.active .step-label { color: #0d6efd; }
    .step-line { position: absolute; top: 16px; left: 0; width: 100%; height: 2px; background: #f1f5f9; z-index: 1; }

    .payment-mode-item .btn-check:checked + label { background-color: #0d6efd !important; color: white !important; border-color: #0d6efd !important; box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2); }

    .active-res-card { background: #f8fafc; border: 1px dashed #cbd5e1; }

    .grid-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .action-btn-modern { background: #fff; border: 1px solid #f1f5f9; border-radius: 20px; padding: 20px; display: flex; flex-direction: column; align-items: center; gap: 12px; transition: all 0.2s; }
    .action-btn-modern:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); background: #fafafa; }
    .action-btn-modern .icon { width: 50px; height: 50px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
    .action-btn-modern span { font-weight: 700; font-size: 13px; text-align: center; color: #334155; }
</style>
