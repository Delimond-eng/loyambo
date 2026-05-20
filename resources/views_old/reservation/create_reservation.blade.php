@extends('layouts.admin')


@section('content')
<div class="content-wrapper">
    <div class="container-full AppReservation">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="d-flex align-items-center justify-content-between">
                <div class="mr-auto">
                    <h4 class="page-title">Nouvelle Réservation</h4>
                    <div class="d-inline-block align-items-center">
                        <nav>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#"><i class="mdi mdi-home-outline"></i></a></li>
                                <li class="breadcrumb-item"><a href="{{ route('reservations') }}">Réservations</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Sélectionnez une chambre à reserver !</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-group mt-2">
                        <span class="input-group-text"><i
                                class="ti-search text-primary"></i></span>
                        <input type="number" v-model="search" class="form-control ps-15"
                            placeholder="Recherche n° Chambre...">
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content" v-cloak>
            <div class="box">
                <div class="box-body">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs justify-content-center" role="tablist">
                        <li class="nav-item"> <a class="nav-link active" @click="filter=''"  data-bs-toggle="tab" href="#home12" role="tab" aria-selected="true"><span><i class="fa fa-bed"></i></span> <span class="hidden-xs-down ms-15">Toutes les chambres</span></a> </li>
                        <li class="nav-item"> <a class="nav-link" @click="filter='libre'" data-bs-toggle="tab" href="#setting12" role="tab" aria-selected="false"><span><i class="fa fa-bed text-success"></i></span> <span class="hidden-xs-down ms-15">Chambres libres</span></a> </li>
                        <li class="nav-item"> <a class="nav-link" @click="filter='occupée'" data-bs-toggle="tab" href="#profile12" role="tab" aria-selected="false"><span><i class="fa fa-bed text-info"></i></span> <span class="hidden-xs-down ms-15">Chambres occupées</span></a> </li>
                        <li class="nav-item"> <a class="nav-link" @click="filter='réservée'" data-bs-toggle="tab" href="#messages12" role="tab" aria-selected="false"><span><i class="fa fa-bed text-warning"></i></span> <span class="hidden-xs-down ms-15">Chambres réservées</span></a> </li>
                    </ul>
                    <!-- Tab panes -->
                    <div class="tab-content tabcontent-border">
                        <div class="row d-flex justify-content-center pt-20" v-if="allChambres.length > 0">
                            <div class="col-md-6 col-sm-3 col-lg-2 col-6" v-for="(chambre, i) in allChambres">
                                <a href="#" @click="chambre.statut === 'réservée' || chambre.statut === 'occupée' ?  triggerActionReservationModal(chambre) : triggerOpenCreateReservationModal(chambre)"class="box box-shadowed b-3 border-primary">
                                    <div class="box-body ribbon-box">
                                        <div class="ribbon-two" :class="{'ribbon-two-danger': chambre.statut==='occupée', 'ribbon-two-success':chambre.statut==='libre','ribbon-two-warning':chambre.statut==='réservée' }"><span>@{{ chambre.statut }}</span></div>
                                        <img :src="chambre.statut==='libre' ? 'assets/images/bed-empty.png' : 'assets/images/bed-2.png'" class="img-fluid">
                                        <div style="position:absolute; left: 20px; bottom: 20px;" class="bg-primary fw-900 rounded-circle w-40 h-40 l-h-40 text-center">
                                            @{{ chambre.numero }}
                                        </div>
                                        <div style="position:absolute; right: 20px; bottom: 20px;" class="text-center">
                                            <span class="fw-900 border p-2 rounded border-secondary">@{{ chambre.prix }} <small> @{{ chambre.prix_devise }}</small></span>
                                        </div>
                                        <span style="position:absolute; right: 20px; top: 20px;" class="badge badge-pill badge-primary-light">@{{ chambre.type }}</span>
                                    </div> <!-- end box-body-->
                                </a>
                            </div>
                        </div>

                        <div v-else  class="row d-flex justify-content-center align-items-center py-80">
                            <div class="col-md-12 text-center" v-if="isDataLoading">
                                <span class="spinner-border"></span>
                            </div>
                            <div class="col-md-12" v-else>
                                <div class="text-muted text-center">
                                    <i class="fa fa-bed fa-3x mb-2"></i>
                                    <p v-if="filter">
                                        Aucune chambre @{{ filter }} trouvée !
                                    </p>
                                    <p v-else>
                                        Aucune chambre trouvée !
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </section>

        <div class="modal fade modal-reservation-action" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Sélectionnez une action !</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="flexbox flex-justified text-center" v-if="selectedChambre">
                            <button @click="triggerOpenCreateReservationModal(selectedChambre)" class="b-2 btn rounded border-primary text-primary py-20">
                                <p class="mb-0 fa-3x"><i class="mdi mdi-plus"></i></p>
                                <p class="mb-0 fw-300">Nouvelle reservation</p>
                            </button>
                            <button @click="occuperChambre(selectedChambre)" :class="{'border-danger text-danger':selectedChambre.statut === 'réservée', 'border-success text-success':selectedChambre.statut === 'occupée',}" class="b-2 rounded  btn py-20">
                                <p class="mb-0 fa-3x">
                                    <span v-if="isLoading" class="spinner-border"></span>
                                    <i v-else class="fa fa-bed"></i>
                                </p>
                                <p class="mb-0 fw-300" v-if="selectedChambre.statut === 'réservée'">Occuper chambre</p>
                                <p class="mb-0 fw-300" v-if="selectedChambre.statut === 'occupée'">Libérer chambre</p>
                            </button>
                        </div>
                    </div>
                </div>
                
            </div>
            <!-- /.modal-dialog -->
        </div>

        <div class="modal fade modal-reservation-create" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" @submit.prevent="createReservation">
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
                                            :class="{'badge-primary' : selectedChambre.statut==='libre','badge-danger':selectedChambre.statut==='réservée', 'badge-danger':selectedChambre.statut==='occupée'}">
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

                                <div class="col-md-12">
                                    <label class="form-label">Nom complet <sup>*</sup></label>
                                    <input type="text" class="form-control" v-model="form.client.nom" placeholder="Ex. Gaston Delimond ...">
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
                                class="btn"  :class="{ 'btn-success': step===2, 'btn-primary': step===1 }" :disabled="isLoading">
                            @{{ step=== 1 ? 'Suivant' : 'Valider et soumettre' }} <span v-if="isLoading" class="spinner-border spinner-border-sm ms-2"></span>
                        </button>
                    </div>
                </form>

                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>

        @include('components.modals.facture')
    </div>
</div>
@endsection

@push('scripts')
<script type="module" src="{{ asset("assets/js/scripts/reservation.js") }}"></script>	
@endpush
