@extends('layouts.admin')


@push('styles')
@include('components.reservations.styles')
@endpush

@section('content')
<div class="content-wrapper">
    <div class="container-full AppReservation">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="room-header">
                <div class="room-header__title">
                    <h4 class="page-title mb-0">Nouvelle Réservation</h4>
                    <div class="d-inline-block align-items-center">
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="#"><i class="mdi mdi-home-outline"></i></a></li>
                                <li class="breadcrumb-item"><a href="{{ route('reservations') }}">Réservations</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Sélectionnez une chambre à réserver !</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="room-tools">
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti-search text-primary"></i></span>
                        <input type="number" v-model="search" class="form-control ps-15" placeholder="Recherche n° Chambre...">
                    </div>
                    <div class="room-filters">
                        <button type="button" class="room-filter" :class="{ active: filter === '' }" @click="setRoomFilter('')">
                            Toutes <span class="count">@{{ chambreStats.total }}</span>
                        </button>
                        <button type="button" class="room-filter" :class="{ active: filter === 'libre' }" @click="setRoomFilter('libre')">
                            Libres <span class="count">@{{ chambreStats.libres }}</span>
                        </button>
                        <button type="button" class="room-filter" :class="{ active: filter === 'réservée' }" @click="setRoomFilter('réservée')">
                            Réservées <span class="count">@{{ chambreStats.reservees }}</span>
                        </button>
                        <button type="button" class="room-filter" :class="{ active: filter === 'occupée' }" @click="setRoomFilter('occupée')">
                            Occupées <span class="count">@{{ chambreStats.occupees }}</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="room-legend mt-2">
                <span><span class="dot status-libre-dot"></span> Libre</span>
                <span><span class="dot status-reservee-dot"></span> Réservée</span>
                <span><span class="dot status-occupee-dot"></span> Occupée</span>
            </div>
        </div>

        <!-- Main content -->
        <section class="content" v-cloak>
            <div class="box">
                <div class="box-body">
                    <div class="room-grid" v-if="allChambres.length > 0">
                        <a href="#" class="room-card text-decoration-none" v-for="(chambre, i) in allChambres" :key="chambre.id" @click.prevent="chambre.statut === 'réservée' || chambre.statut === 'occupée' ? triggerActionReservationModal(chambre) : triggerOpenCreateReservationModal(chambre)">
                            <div class="room-card__media">
                                <span class="room-card__status" :class="statusClass(chambre.statut)">@{{ chambre.statut }}</span>
                                <div class="room-card__number">CH-@{{ chambre.numero }}</div>
                                <div class="room-card__type">@{{ chambre.type }}</div>
                                <img :src="chambre.statut === 'libre' ? '{{ asset('assets/images/bed-empty.png') }}' : '{{ asset('assets/images/bed-2.png') }}'" alt="Chambre" class="room-card__icon">
                            </div>
                            <div class="room-card__body">
                                <div class="room-card__meta">
                                    <span><i class="fa fa-users"></i> @{{ chambre.capacite }} pers</span>
                                    <span class="room-pill"><i class="fa fa-tag"></i> Négociable</span>
                                </div>
                                <div class="room-card__prices">
                                    <div class="price-line">
                                        <span>Nuitée</span>
                                        <strong>@{{ chambre.prix_nuit }} @{{ chambre.prix_devise }}</strong>
                                    </div>
                                    <div class="price-line">
                                        <span>Passage</span>
                                        <strong>@{{ chambre.prix_passage }} @{{ chambre.prix_devise }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="room-card__footer">
                                <span>@{{ roomActionLabel(chambre) }}</span>
                                <span class="room-pill"><i class="fa fa-arrow-right"></i> Ouvrir</span>
                            </div>
                        </a>
                    </div>

                    <div v-else class="row d-flex justify-content-center align-items-center py-80">
                        <div class="col-md-12 text-center" v-if="isDataLoading">
                            <span class="spinner-border"></span>
                        </div>
                        <div class="col-md-12" v-else>
                            <div class="text-muted text-center">
                                <i class="fa fa-bed fa-3x mb-2"></i>
                                <p v-if="filter">Aucune chambre @{{ filter }} trouvée !</p>
                                <p v-else>Aucune chambre trouvée !</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal action chambre -->
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
                                    <p class="mb-0 fw-300">Nouvelle réservation</p>
                                </button>
                                <button @click="occuperChambre(selectedChambre)" :class="{'border-danger text-danger':selectedChambre.statut === 'réservée', 'border-success text-success':selectedChambre.statut === 'occupée',}" class="b-2 rounded btn py-20">
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
            </div>

            <!-- Modal réservation -->
            <div class="modal fade modal-reservation-create" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
                <div class="modal-dialog modal-lg">
                    <form class="modal-content" @submit.prevent="createReservation">
                        <div class="modal-header">
                            <h4 class="modal-title">Réservation chambre</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" v-if="selectedChambre">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-7">
                                    <div class="d-flex flex-wrap align-items-center">
                                        <div class="me-25 bg-danger-light h-80 w-80 l-h-80 rounded text-center">
                                            <img v-if="selectedChambre.statut==='libre'" src="{{ asset('assets/images/bed-empty.png') }}" class="h-50" alt="">
                                            <img v-else src="{{ asset('assets/images/bed-2.png') }}" class="h-50" alt="">
                                        </div>
                                        <div class="d-flex flex-column flex-grow-1 my-lg-0 my-10 pe-15">
                                            <a href="#" class="text-dark fw-600 fs-18">
                                                CH-@{{ selectedChambre.numero }} <br>
                                                Type : @{{ selectedChambre.type }}
                                            </a>
                                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                                <span class="text-fade fw-600 fs-16">Capacité : @{{ selectedChambre.capacite }} pers</span>
                                                <span class="room-pill" :class="statusClass(selectedChambre.statut)">
                                                    <i class="fa fa-circle"></i> @{{ selectedChambre.statut }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 mt-2 flex-wrap">
                                        <span class="badge badge-pill badge-primary-light">Nuitée : @{{ selectedChambre.prix_nuit }} @{{ selectedChambre.prix_devise }}</span>
                                        <span class="badge badge-pill badge-info-light">Passage : @{{ selectedChambre.prix_passage }} @{{ selectedChambre.prix_devise }}</span>
                                        <span class="badge badge-pill badge-light">Prix négociable</span>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="room-summary">
                                        <div class="room-summary__title">Aperçu chambre</div>
                                        <div class="room-summary__row">
                                            <span>Capacité</span>
                                            <strong>@{{ selectedChambre.capacite }} pers</strong>
                                        </div>
                                        <div class="room-summary__row">
                                            <span>Tarif nuitée</span>
                                            <strong>@{{ selectedChambre.prix_nuit }} @{{ selectedChambre.prix_devise }}</strong>
                                        </div>
                                        <div class="room-summary__row">
                                            <span>Tarif passage</span>
                                            <strong>@{{ selectedChambre.prix_passage }} @{{ selectedChambre.prix_devise }}</strong>
                                        </div>
                                        <div class="room-summary__row">
                                            <span>Statut</span>
                                            <span class="room-pill" :class="statusClass(selectedChambre.statut)">@{{ selectedChambre.statut }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <ol class="c-progress-steps">
                                    <li @click="goToStep(1)" class="c-progress-steps__step" :class="{ current: step===1, done: step>1 }">
                                        <span>Réservation</span>
                                    </li>
                                    <li class="c-progress-steps__step" :class="{ current: step===2, done: step>2 }">
                                        <span>Paiement <small>(facultatif)</small></span>
                                    </li>
                                </ol>
                            </div>

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
                                        <input type="text" class="form-control" v-model="form.client.nom" placeholder="Ex. Nom complet ..." required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">E-mail <sup class="text-danger">(Facultatif)</sup></label>
                                        <input type="text" class="form-control" v-model="form.client.email" placeholder="Ex. exemple@domain ...">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Téléphone <sup class="text-danger">*</sup></label>
                                        <input type="text" class="form-control" v-model="form.client.telephone" placeholder="Ex. +243 ..." required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Type de séjour <sup class="text-danger">*</sup></label>
                                        <div class="d-flex gap-2">
                                            <label class="btn btn-outline-primary btn-sm flex-fill" :class="form.type_sejour==='nuit' ? 'active' : ''">
                                                <input type="radio" class="d-none" value="nuit" v-model="form.type_sejour"> Nuitée
                                            </label>
                                            <label class="btn btn-outline-info btn-sm flex-fill" :class="form.type_sejour==='passage' ? 'active' : ''">
                                                <input type="radio" class="d-none" value="passage" v-model="form.type_sejour"> Passage
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="reservation-info">
                                            <div class="d-flex align-items-start gap-2">
                                                <i class="fa fa-info-circle text-primary mt-1"></i>
                                                <div>
                                                    <strong>Passage :</strong> entrée et sortie le même jour (1 jour).
                                                    <br>
                                                    <strong>Nuitée :</strong> séjour avec date de fin, facturé par nuit.
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Date début  <sup class="text-danger">*</sup></label>
                                        <input type="date" class="form-control" v-model="form.date_debut" required>
                                    </div>

                                    <div class="col-md-6" v-if="form.type_sejour === 'nuit'">
                                        <label class="form-label">Date fin  <sup class="text-danger">*</sup></label>
                                        <input type="date" class="form-control" v-model="form.date_fin" required>
                                    </div>
                                </div>
                            </div>

                            <div v-if="step === 2">
                                <div class="row mt-3 g-3">
                                    <div class="col-md-12" v-if="pricingSummary">
                                        <div class="room-summary">
                                            <div class="room-summary__title">Résumé du séjour</div>
                                            <div class="room-summary__row">
                                                <span>Type</span>
                                                <strong>@{{ form.type_sejour === 'passage' ? 'Passage' : 'Nuitée' }}</strong>
                                            </div>
                                            <div class="room-summary__row">
                                                <span>Durée</span>
                                                <strong>@{{ pricingSummary.days }} j</strong>
                                            </div>
                                            <div class="room-summary__row">
                                                <span>Tarif unitaire</span>
                                                <strong>@{{ pricingSummary.unit }} @{{ selectedChambre.prix_devise }}</strong>
                                            </div>
                                            <div class="room-summary__row">
                                                <span>Total catalogue</span>
                                                <strong>@{{ pricingSummary.baseTotal }} @{{ selectedChambre.prix_devise }}</strong>
                                            </div>
                                            <div class="room-summary__row" v-if="pricingSummary.discount > 0">
                                                <span>Remise</span>
                                                <strong class="text-success">-@{{ pricingSummary.discount }} @{{ selectedChambre.prix_devise }}</strong>
                                            </div>
                                            <div class="room-summary__row room-summary__total">
                                                <span>Total à payer</span>
                                                <span>@{{ pricingSummary.applied }} @{{ selectedChambre.prix_devise }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Prix négocié / Remise (facultatif)</label>
                                        <input type="number" class="form-control" v-model.number="form.prix_negocie" placeholder="Laisser vide pour appliquer le prix catalogue">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Mode de paiement</label>
                                        <select class="form-select" v-model="form.paiement.mode">
                                            <option value="">Plus tard (En attente)</option>
                                            <option value="cash">Cash</option>
                                            <option value="mobile">Mobile Money</option>
                                            <option value="virement">Banque</option>
                                            <option value="card">Carte bancaire</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Montant</label>
                                        <input type="number" class="form-control" v-model="form.paiement.amount" :required="form.paiement.mode !== ''" readonly>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Devise</label>
                                        <input type="text" class="form-control" v-model="form.paiement.devise" :required="form.paiement.mode !== ''" readonly>
                                    </div>

                                    <div class="col-md-12" v-if="form.paiement.mode !== '' && form.paiement.mode !== 'cash'">
                                        <label class="form-label">Référence paiement <sup class="text-danger">*</sup></label>
                                        <input type="text" class="form-control" v-model="form.paiement.mode_ref" placeholder="Ex: ID Mobile Money, n° reçu, transaction..." :required="form.paiement.mode !== '' && form.paiement.mode !== 'cash'">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer d-flex">
                            <button type="button" class="btn btn-danger me-2" data-bs-dismiss="modal">Fermer</button>
                            <button type="submit" class="btn" :class="{ 'btn-success': step===2, 'btn-primary': step===1 }" :disabled="isLoading">
                                @{{ step=== 1 ? 'Suivant' : 'Valider et soumettre' }}
                                <span v-if="isLoading" class="spinner-border spinner-border-sm ms-2"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @include('components.modals.facture')
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script type="module" src="{{ asset("assets/js/scripts/reservation.js") }}"></script>
@endpush
