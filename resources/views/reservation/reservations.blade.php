@extends('layouts.admin')
@section('content')
   <div class="content-wrapper">
    <div class="container-full AppReservation">
        <!-- Content Header (Page header) -->
        <div class="content-header">
        </div>
        <!-- Main content -->
        <section class="content">
            <div class="row d-flex justify-content-center align-items-center">
                <div class="col-xl-12">
                    @include("components.menus.reservations")
                </div>
                <div class="col-xl-12">
                    <div class="box">
                        <div class="d-flex justify-content-between align-items-center p-3">
                            <h4 class="mb-0">
                                Liste des toutes les réservations 
                                <small class="text-muted d-block">Gestion des réservations de chambres</small>
                            </h4>
                            <a href="{{ route('reservation.created') }}" class="btn btn-primary">
                                + Nouvelle Réservation
                            </a>
                        </div>

                        <!-- Filtres de recherche -->
                        <div class="box-body border-bottom">
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <label class="form-label">Recherche</label>
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Client, Chambre, Facture..." 
                                           value="{{ request('search') }}">
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label">Date début</label>
                                    <input type="date" name="date_debut" class="form-control" 
                                           value="{{ request('date_debut') }}">
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label">Date fin</label>
                                    <input type="date" name="date_fin" class="form-control" 
                                           value="{{ request('date_fin') }}">
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label">Statut</label>
                                    <select name="statut" v-model="filter" class="form-control">
                                        <option value="">Tous les statuts</option>
                                        <option value="en_attente">En attente</option>
                                        <option value="confirmée">Confirmée</option>
                                        <option value="terminée">Terminée</option>
                                        <option value="annulée">Annulée</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Actions</label>
                                    <div class="d-flex">
                                        <button type="submit" class="btn btn-primary btn-sm me-2">
                                            <i class="fa fa-search"></i> Filtrer
                                        </button>
                                        <a href="#" class="btn btn-secondary btn-sm">
                                            <i class="fa fa-refresh"></i> Réinitialiser
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Statistiques -->
                        <div class="box-body" v-cloak>
                            <!-- Liste des réservations -->
                            <div class="table-responsive">
                                <table class="table table-striped no-border">
                                <thead>
                                    <tr class="bb-3 border-primary">
                                        <th>Date Reservation</th>
                                        <th>Client</th>
                                        <th>N° Chambre</th>
                                        <th>Période</th>
                                        <th>Durée</th>
                                        <th>Tarif jour</th>
                                        <th>Total</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                   <tr :class="{'be-3 border-warning':data.statut === 'en_attente', 'be-3 border-success':data.statut === 'confirmée', 'be-3 border-primary':data.statut === 'terminée', 'bs-3 border-danger':data.statut === 'annulée'}" v-for="(data, index) in allReservations">
                                        <th scope="row">@{{ data.created_at }}</th>
                                        <td>@{{data.client.nom }}</td>
                                        <td>CH-@{{data.chambre.numero }}</td>
                                        <td>@{{ formateDate(data.date_debut) }} - @{{formateDate(data.date_fin) }}</td>
                                        <td>@{{ getDays(data.date_debut, data.date_fin)}} j</td>
                                        <td>@{{data.chambre.prix }} <small>@{{data.chambre.prix_devise }}</small></td>
                                        <td>@{{parseFloat(data.chambre.prix) * getDays(data.date_debut, data.date_fin) }} <small>@{{data.chambre.prix_devise }}</small></td>
                                        <td><span class="badge badge-pill" :class="{'badge-warning': data.statut === 'en_attente', 'badge-success': data.statut === 'confirmée', 'badge-primary': data.statut === 'terminée', 'badge-danger': data.statut === 'annulée'}" >@{{data.statut.replaceAll("_", " ") }}</span></td>
                                        <td>
                                            <div class="d-flex">
                                                <button type="button" v-if="data.statut !== 'confirmée' && data.statut !== 'terminée' && data.statut !== 'annulée'" @click="triggerOpenPaymentModal(data)" class="btn btn-success btn-xs me-1"><i class="fa fa-money"></i></button>
                                                <button type="button" v-if="data.statut !== 'terminée' && data.statut !== 'annulée'" class="btn btn-info btn-xs me-1" @click="triggerOpenUpdateReservationModal(data)"><i class="mdi mdi-pencil"></i></button>
                                                <button type="button" v-if="data.statut !== 'terminée' && data.statut !== 'annulée'" @click="triggerExtendDayModal(data)" class="btn btn-primary-light btn-xs me-1"><i class="mdi mdi-plus"></i></button>
                                                <button type="button" class="btn btn-primary btn-xs me-1"><i class="mdi mdi-eye"></i></button>
                                                <button type="button" v-if="data.statut !== 'terminée' && data.statut !== 'annulée'" @click="cancelReservation(data.id)" class="btn btn-danger-light btn-xs">
                                                    <span v-if="cancel_id === data.id" class="spinner-border spinner-border-sm"></span>
                                                    <i v-else class="mdi mdi-cancel"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="allReservations.length === 0">
                                        <td colspan="10" class="text-center py-4">
                                            <div class="py-50" v-if="isDataLoading">
                                                <span class="spinner-border"></span>
                                            </div>
                                            <div v-else class="text-muted" >
                                                <i class="fa fa-bed fa-2x mb-2"></i>
                                                <p>
                                                    Aucune réservation trouvée
                                                </p>
                                                <a href="{{ route('reservation.created') }}" class="btn btn-primary btn-xs">+ Créer une réservation</a>
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
        </section>


        <!-- Modal de paiement -->
        <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="paymentModalLabel">Choisir un mode de paiement</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2 text-center">
                            <div class="col-4 mb-3" v-for="(data, mode) in modes" :key="mode">
                                <button @click="selectedMode = mode"
                                        class="btn btn-outline-primary payment-mode-btn w-100 h-100 py-3"
                                        :data-mode="mode">
                                    <i :class="data.icon + ' fa-2x mb-2'"></i><br>
                                    <span class="small">@{{ data.label }}</span>
                                </button>
                            </div>

                            <div class="col-12 col-md-12" v-if="selectedMode">
                                <label class="mb-2 fw-500 text-left">Montant à payer <span v-if="selectedMode !== 'cash'">& Réference de paiement</span></label>
                                <div class="d-flex justify-content-center align-items-center">
                                    <input type="number" v-model="formPay.amount" readonly class="form-control fw-900 text-primary me-2" placeholder="Montant...">
                                    <input type="text" v-model="formPay.devise" readonly class="form-control me-2  w-50" placeholder="$">
                                    <!-- afficher le champ uniquement si le mode n'est pas cash -->
                                    <input type="text" v-model="formPay.mode_ref" v-if="selectedMode !== 'cash'" class="form-control me-2"
                                        placeholder="Saisir la référence de paiement...">
                                    <button class="btn btn-success btn-sm" @click="payReservation"  :disabled="isLoading">Confirmer <span v-if="isLoading" class="spinner-border spinner-border-sm ms-2"></span></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="modal fade modal-reservation-create" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" @submit.prevent="extendReservation">
                    <div class="modal-header">
                        <h4 class="modal-title">Réservation chambre</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" v-if="selectedChambre">
                        <!-- Info Chambre -->
                        <div class="d-flex flex-wrap align-items-center">
                            <div class="me-25 bg-danger-light h-80 w-80 l-h-80 rounded text-center">
                                <img :src="selectedChambre.statut==='libre' ? 'assets/images/bed-empty.png' : 'assets/images/bed-2.png'"
                                    class="h-50" alt="">
                            </div>
                            <div class="d-flex flex-column flex-grow-1 my-lg-0 my-10 pe-15">
                                <a href="#" class="text-dark fw-600 fs-18">
                                    CH-@{{ selectedChambre.numero }} <br>
                                    Type : @{{ selectedChambre.type }}
                                </a>
                                <div class="d-flex justify-content-between">
                                    <span class="text-fade fw-600 fs-16">
                                        Capacité : @{{ selectedChambre.capacite }}
                                    </span>
                                    <span class="fw-600 fs-16">
                                        Statut :
                                        <span class="badge badge-pill"
                                            :class="selectedChambre.statut==='libre' ? 'badge-primary' : 'badge-warning'">
                                            @{{ selectedChambre.statut }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Stepper -->
                        <div class="mt-3">
                            <ol class="c-progress-steps">
                                <li @click="goToStep(1)" class="c-progress-steps__step"
                                    :class="{ current: step===1, done: step>1 }">
                                    <span>Réservation</span>
                                </li>

                                <li class="c-progress-steps__step"
                                    :class="{ current: step===2, done: step>2 }">
                                    <span>Paiement <small>(facultatif)</small></span>
                                </li>
                            </ol>
                        </div>

                        <!-- STEP 1 : RESERVATION -->
                        <div v-if="step === 1">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Type pièce <sup class="text-danger">*</sup></label>
                                    <input type="text" class="form-control" v-model="form.client.identite_type" readonly>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">N° pièce <sup class="text-danger">*</sup></label>
                                    <input type="text" class="form-control" v-model="form.client.identite" readonly>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Nom complet <sup>*</sup></label>
                                    <input type="text" class="form-control" v-model="form.client.nom" readonly >
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">E-mail <sup class="text-danger">(Facultatif)</sup></label>
                                    <input type="text" class="form-control" v-model="form.client.email"  placeholder="Ex. exemple@domain ..." readonly>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Téléphone <sup class="text-danger">*</sup></label>
                                    <input type="text" class="form-control" v-model="form.client.telephone"  placeholder="Ex. +243 ..." readonly>
                                </div>

                                <div class="col-md-6">
                                    
                                    <label class="form-label">Date début <sup class="text-danger">*</sup></label>
                                    <input type="date" class="form-control" v-model="form.date_debut" required readonly>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Date fin   <sup class="text-danger">*</sup></label>
                                    <input type="date" class="form-control" v-model="form.date_fin" required>
                                </div>
                            </div>
                        </div>
                        <!-- STEP 2 : PAIEMENT -->
                        <div v-if="step === 2">
                            
                            <div class="row mt-3 g-3">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col b-r text-center">
                                            <h6>Nombre des jours</h6>
                                            <h2 class="font-light">@{{ getDays(form.date_debut, form.date_fin) }} Jrs</h2>
                                        </div>
                                        <div class="col b-r text-center">
                                            <h6>Prix journalier</h6>
                                            <h2 class="font-light" v-if="selectedChambre"> @{{ selectedChambre.prix }} <small>@{{ selectedChambre.prix_devise }}</small></h2>
                                        </div>
                                    </div>
                                </div>
                                <!-- Mode de paiement -->
                                <div class="col-md-6">
                                    <label class="form-label">Mode de paiement</label>
                                    <select class="form-select" v-model="form.paiement.mode">
                                        <option hidden value="">--Choisissez un mode--</option>
                                        <option value="cash">Cash</option>
                                        <option value="mobile">Mobile Money</option>
                                        <option value="virement">Banque</option>
                                        <option value="card">Carte bancaire</option>
                                    </select>
                                </div>
                                <!-- Montant -->
                                <div class="col-md-4">
                                    <label class="form-label">Montant</label>
                                    <input type="number" class="form-control" v-model="form.paiement.amount"  placeholder="Ex. 0.00 ..." 
                                    :required="form.paiement.mode !== ''" readonly>
                                </div>
                                <!-- Devise -->
                                <div class="col-md-2">
                                    <label class="form-label">Devise</label>
                                    <input type="text" class="form-control" v-model="form.paiement.devise"  placeholder="Ex. USD." 
                                    :required="form.paiement.mode !== ''" readonly>
                                </div>

                                <!-- Référence du paiement -->
                                <div class="col-md-12" v-if="form.paiement.mode !== '' && form.paiement.mode !== 'cash'">
                                    <label class="form-label">Référence paiement <sup class="text-danger">*</sup></label>
                                    <input type="text" class="form-control" v-model="form.paiement.mode_ref"
                                        placeholder="Ex: ID Mobile Money, n° reçu, transaction..." 
                                        :required ="form.paiement.mode !== '' && form.paiement.mode !== 'cash'">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FOOTER -->
                    <div class="modal-footer d-flex">
                        <button type="button" class="btn btn-danger me-2"
                                data-bs-dismiss="modal">Fermer</button>
                        <button type="submit"
                                class="btn"  :class="{ 'btn-success': step===2, 'btn-primary': step===1 }" :disabled="isLoading">
                            @{{ step=== 1 ? 'Suivant' : 'Valider et soumettre' }} <span v-if="isLoading" class="spinner-border spinner-border-sm ms-2"></span>
                        </button>
                    </div>
                </form>

                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>


        <div class="modal fade modal-reservation-update" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" @submit.prevent="updateReservation">
                    <div class="modal-header">
                        <h4 class="modal-title">Modification Réservation chambre</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" v-if="selectedChambre">
                        <!-- Info Chambre -->
                        <div class="d-flex flex-wrap align-items-center">
                            <div class="me-25 bg-danger-light h-80 w-80 l-h-80 rounded text-center">
                                <img :src="selectedChambre.statut==='libre' ? 'assets/images/bed-empty.png' : 'assets/images/bed-2.png'"
                                    class="h-50" alt="">
                            </div>
                            <div class="d-flex flex-column flex-grow-1 my-lg-0 my-10 pe-15">
                                <a href="#" class="text-dark fw-600 fs-18">
                                    CH-@{{ selectedChambre.numero }} <br>
                                    Type : @{{ selectedChambre.type }}
                                </a>
                                <div class="d-flex justify-content-between">
                                    <span class="text-fade fw-600 fs-16">
                                        Capacité : @{{ selectedChambre.capacite }}
                                    </span>
                                    <span class="fw-600 fs-16">
                                        Statut :
                                        <span class="badge badge-pill"
                                            :class="selectedChambre.statut==='libre' ? 'badge-primary' : 'badge-warning'">
                                            @{{ selectedChambre.statut }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Stepper -->
                        <div class="mt-3">
                            <ol class="c-progress-steps">
                                <li @click="goToStep(1)" class="c-progress-steps__step"
                                    :class="{ current: step===1, done: step>1 }">
                                    <span>Réservation</span>
                                </li>

                                <li class="c-progress-steps__step"
                                    :class="{ current: step===2, done: step>2 }">
                                    <span>Paiement <small>(facultatif)</small></span>
                                </li>
                            </ol>
                        </div>

                        <!-- STEP 1 : RESERVATION -->
                        <div v-if="step === 1">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Type pièce <sup class="text-danger">*</sup></label>
                                    <select class="form-select" v-model="form.client.identite_type" required>
                                        <option hidden value="">--Sélectionnez--</option>
                                        <option>Carte d'Identité</option>
                                        <option>Carte de service</option>
                                        <option>Carte d'Electeur</option>
                                        <option>Passeport</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">N° pièce <sup class="text-danger">*</sup></label>
                                    <input type="text" class="form-control" v-model="form.client.identite" placeholder="NID....." required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Chambre<sup>*</sup></label>
                                    <select class="form-select" v-model="form.chambre_id" required>
                                        <option hidden value="">--Sélectionnez--</option>
                                        <option v-for="(data, index) in allChambres" :value="data.id">CH-@{{ data.numero}}</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Nom complet <sup>*</sup></label>
                                    <input type="text" class="form-control" v-model="form.client.nom" placeholder="Ex. Gaston Delimond ..." required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">E-mail <sup class="text-danger">(Facultatif)</sup></label>
                                    <input type="text" class="form-control" v-model="form.client.email"  placeholder="Ex. exemple@domain ...">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Téléphone <sup class="text-danger">*</sup></label>
                                    <input type="text" class="form-control" v-model="form.client.telephone"  placeholder="Ex. +243 ..." required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Date début  <sup class="text-danger">*</sup></label>
                                    <input type="date" class="form-control" v-model="form.date_debut" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Date fin  <sup class="text-danger">*</sup></label>
                                    <input type="date" class="form-control" v-model="form.date_fin" required>
                                </div>
                            </div>
                        </div>
                        <!-- STEP 2 : PAIEMENT -->
                        <div v-if="step === 2">
                            
                            <div class="row mt-3 g-3">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col b-r text-center">
                                            <h6>Nombre des jours</h6>
                                            <h2 class="font-light">@{{ getDays(form.date_debut, form.date_fin) }} Jrs</h2>
                                        </div>
                                        <div class="col b-r text-center">
                                            <h6>Prix journalier</h6>
                                            <h2 class="font-light" v-if="selectedChambre"> @{{ selectedChambre.prix }} <small>@{{ selectedChambre.prix_devise }}</small></h2>
                                        </div>
                                    </div>
                                </div>
                                <!-- Mode de paiement -->
                                <div class="col-md-6">
                                    <label class="form-label">Mode de paiement</label>
                                    <select class="form-select" v-model="form.paiement.mode">
                                        <option hidden value="">--Choisissez un mode--</option>
                                        <option value="cash">Cash</option>
                                        <option value="mobile">Mobile Money</option>
                                        <option value="virement">Banque</option>
                                        <option value="card">Carte bancaire</option>
                                    </select>
                                </div>
                                <!-- Montant -->
                                <div class="col-md-4">
                                    <label class="form-label">Montant</label>
                                    <input type="number" class="form-control" v-model="form.paiement.amount"  placeholder="Ex. 0.00 ..." 
                                    :required="form.paiement.mode !== ''" readonly>
                                </div>
                                <!-- Devise -->
                                <div class="col-md-2">
                                    <label class="form-label">Devise</label>
                                    <input type="text" class="form-control" v-model="form.paiement.devise"  placeholder="Ex. USD." 
                                    :required="form.paiement.mode !== ''" readonly>
                                </div>

                                <!-- Référence du paiement -->
                                <div class="col-md-12" v-if="form.paiement.mode !== '' && form.paiement.mode !== 'cash'">
                                    <label class="form-label">Référence paiement <sup class="text-danger">*</sup></label>
                                    <input type="text" class="form-control" v-model="form.paiement.mode_ref"
                                        placeholder="Ex: ID Mobile Money, n° reçu, transaction..." 
                                        :required ="form.paiement.mode !== '' && form.paiement.mode !== 'cash'">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FOOTER -->
                    <div class="modal-footer d-flex">
                        <button type="button" class="btn btn-danger me-2"
                                data-bs-dismiss="modal">Fermer</button>
                        <button type="submit"
                                class="btn"  :class="{ 'btn-info': step===2, 'btn-primary': step===1 }" :disabled="isLoading">
                            @{{ step=== 1 ? 'Suivant' : 'Enregistrer les modifications' }} <span v-if="isLoading" class="spinner-border spinner-border-sm ms-2"></span>
                        </button>
                    </div>
                </form>

                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script type="module" src="{{ asset("assets/js/scripts/reservation.js") }}"></script>	
@endpush


