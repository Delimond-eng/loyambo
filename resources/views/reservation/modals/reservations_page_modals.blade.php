<!-- Modal de paiement (Encaisser) -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h4 class="modal-title fw-800">Encaisser le paiement</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body p-4">
                <div class="payment-summary mb-4 p-3 rounded-4 bg-primary-light text-center">
                    <span class="text-muted fw-600 fs-13 d-block mb-1">Montant total attendu</span>
                    <h2 class="fw-900 text-primary mb-0">@{{ formPay.amount }} <small>@{{ formPay.devise }}</small></h2>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-700 mb-3">Sélectionnez le mode de règlement</label>
                    <div class="row g-2">
                        <div class="col-6" v-for="(data, mode) in modes" :key="mode">
                            <input type="radio" :id="'pay-mode-'+mode" :value="mode" v-model="selectedMode" class="btn-check">
                            <label :for="'pay-mode-'+mode" class="btn btn-outline-light text-dark w-100 py-3 rounded-4 d-flex flex-column align-items-center border">
                                <i :class="data.icon + ' fs-24 mb-2'"></i>
                                <span class="fw-700 fs-13">@{{ data.label }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="animate__animated animate__fadeIn" v-if="selectedMode && selectedMode !== 'cash'">
                    <label class="form-label fw-700">Référence de transaction</label>
                    <input type="text" v-model="formPay.mode_ref" class="form-control rounded-3"
                           placeholder="ID Mobile Money, N° chèque, etc..." required>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-success rounded-pill px-4 shadow-sm fw-700" @click="payReservation" :disabled="isLoading || !selectedMode">
                    <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span>
                    Confirmer l'encaissement
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Extension Séjour -->
<div class="modal fade modal-reservation-create" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content border-0 shadow-lg" @submit.prevent="extendReservation" style="border-radius: 24px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h4 class="modal-title fw-800">Prolonger le séjour</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" v-if="selectedChambre">
                <div class="alert bg-primary-light border-0 rounded-4 d-flex align-items-center mb-4">
                    <div class="icon-circle bg-white me-3">
                        <i class="mdi mdi-calendar-plus text-primary fs-20"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-700 text-primary">CH-@{{ selectedChambre.numero }} | @{{ form.client.nom }}</h6>
                        <small class="text-primary fw-600 opacity-75">Date de fin actuelle : @{{ formateDate(form.date_debut) }}</small>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fw-700">Nouvelle date de départ</label>
                        <input type="date" class="form-control rounded-3 form-control-lg fw-700" v-model="form.date_fin" required>
                    </div>
                </div>

                <div class="mt-4 p-3 rounded-4 bg-light" v-if="getDays(form.date_debut, form.date_fin) > 0">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted fw-600">Nouveau total à prévoir</span>
                        <h4 class="mb-0 fw-900">@{{ (getDays(form.date_debut, form.date_fin) * selectedChambre.prix_nuit) }} @{{ selectedChambre.prix_devise }}</h4>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Fermer</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-700" :disabled="isLoading">
                    <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span>
                    Mettre à jour le séjour
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .bg-primary-light { background-color: #eef2ff !important; }
    .payment-mode-item label { transition: all 0.2s; border: 1.5px solid #f1f5f9; }
    .btn-check:checked + label {
        background-color: #fff !important;
        border-color: #0d6efd !important;
        color: #0d6efd !important;
        box-shadow: 0 8px 20px rgba(13, 110, 253, 0.1);
        transform: translateY(-2px);
    }
    .icon-circle { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
</style>
