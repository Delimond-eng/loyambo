@extends('layouts.admin')
@push('styles')
@include('components.reservations.styles')
@endpush

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
                                <div class="col-md-4">
                                    <label class="form-label">Recherche</label>
                                    <input type="text" class="form-control" v-model="search" placeholder="Client, Chambre, Facture...">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Statut</label>
                                    <select v-model="filter" class="form-control">
                                        <option value="">Tous les statuts</option>
                                        <option value="en_attente">En attente</option>
                                        <option value="confirmée">Confirmée</option>
                                        <option value="terminée">Terminée</option>
                                        <option value="annulée">Annulée</option>
                                    </select>
                                </div>

                                <div class="col-md-5">
                                    <label class="form-label">Aperçu</label>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <span class="badge badge-pill badge-primary-light">Total : @{{ reservationStats.total }}</span>
                                        <span class="badge badge-pill badge-warning-light">En attente : @{{ reservationStats.en_attente }}</span>
                                        <span class="badge badge-pill badge-success-light">Confirmées : @{{ reservationStats.confirmee }}</span>
                                        <span class="badge badge-pill badge-info-light">Terminées : @{{ reservationStats.terminee }}</span>
                                        <span class="badge badge-pill badge-danger-light">Annulées : @{{ reservationStats.annulee }}</span>
                                        <span class="badge badge-pill badge-danger">Expirées : @{{ reservationStats.expiree }}</span>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <button type="button" class="btn btn-secondary btn-sm" @click="search=''; filter=''">
                                        <i class="fa fa-refresh"></i> Réinitialiser
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="box-body" v-cloak>
                            <div class="table-responsive">
                                <table class="table table-striped no-border">
                                <thead>
                                    <tr class="bb-3 border-primary">
                                        <th>Date Réservation</th>
                                        <th>Client</th>
                                        <th>N° Chambre</th>
                                        <th>Type séjour</th>
                                        <th>Période</th>
                                        <th>Durée</th>
                                        <th>Tarif</th>
                                        <th>Total</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                   <tr :class="{'be-3 border-warning':data.statut === 'en_attente', 'be-3 border-success':data.statut === 'confirmée', 'be-3 border-primary':data.statut === 'terminée', 'bs-3 border-danger':data.statut === 'annulée'}" v-for="(data, index) in allReservations" :key="data.id">
                                        <th scope="row">@{{ formateDate(data.created_at) }}</th>
                                        <td>@{{ data.client.nom }}</td>
                                        <td>CH-@{{ data.chambre.numero }}</td>
                                        <td>
                                            <span class="badge badge-pill" :class="data.type_sejour === 'passage' ? 'badge-info' : 'badge-primary'">
                                                @{{ data.type_sejour === 'passage' ? 'Passage' : 'Nuitée' }}
                                            </span>
                                        </td>
                                        <td>
                                            @{{ formateDate(data.date_debut) }}
                                            <span v-if="data.type_sejour !== 'passage'"> - @{{ formateDate(data.date_fin) }}</span>
                                        </td>
                                        <td>@{{ data.type_sejour === 'passage' ? '1 jour' : (getDays(data.date_debut, data.date_fin) + ' nuit(s)') }}</td>
                                        <td>
                                            <span class="fw-600">@{{ data.type_sejour === 'passage' ? data.chambre.prix_passage : data.chambre.prix_nuit }}</span>
                                            <small>@{{ data.chambre.prix_devise }}</small>
                                        </td>
                                        <td>
                                            <span class="fw-700">@{{ data.prix_facture || data.prix_base }}</span>
                                            <small>@{{ data.chambre.prix_devise }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-pill" :class="{'badge-warning': data.statut === 'en_attente', 'badge-success': data.statut === 'confirmée', 'badge-primary': data.statut === 'terminée', 'badge-danger': data.statut === 'annulée'}">@{{ data.statut.replaceAll('_', ' ') }}</span>
                                            <span class="badge badge-danger ms-1" v-if="isExpired(data)">Expirée</span>
                                        </td>
                                        <td>
                                            <div class="d-flex">
                                                <button type="button" v-if="data.statut === 'confirmée' && data.chambre.statut !== 'occupée' && isDateAvailable(data.date_debut, data.date_fin)" @click="occuperChambre(data.chambre)" class="btn btn-warning btn-xs me-1">
                                                    <span v-if="bed_id === data.chambre.id" class="spinner-border spinner-border-sm"></span>
                                                    <i v-else class="fa fa-bed"></i>
                                                </button>
                                                <button type="button" v-if="data.statut !== 'confirmée' && data.statut !== 'terminée' && data.statut !== 'annulée'" @click="triggerOpenPaymentModal(data)" class="btn btn-success btn-xs me-1"><i class="fa fa-money"></i></button>
                                                <button type="button" v-if="data.statut !== 'terminée' && data.statut !== 'annulée'" class="btn btn-info btn-xs me-1" @click="triggerOpenUpdateReservationModal(data)"><i class="mdi mdi-pencil"></i></button>
                                                <button type="button" v-if="data.statut !== 'terminée' && data.statut !== 'annulée'" @click="triggerExtendDayModal(data)" class="btn btn-primary-light btn-xs me-1"><i class="mdi mdi-plus"></i></button>
                                                <button type="button" v-if="data.statut === 'confirmée' || data.statut === 'terminée'" class="btn btn-primary btn-xs me-1" @click="viewReservation(data)"><i class="mdi mdi-eye"></i></button>
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
                                            <div v-else class="text-muted">
                                                <i class="fa fa-bed fa-2x mb-2"></i>
                                                <p>Aucune réservation trouvée</p>
                                                <a href="{{ route('reservation.created') }}" class="btn btn-primary btn-xs">+ Créer une réservation</a>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                                </table>
                            </div>

                            <Paginator
                                :current-page="pagination.current_page"
                                :last-page="pagination.last_page"
                                :total-items="pagination.total"
                                :per-page="pagination.per_page"
                                @page-changed="changePage"
                                @per-page-changed="onPerPageChange">
                            </Paginator>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @include('components.modals.facture')
        @include('components.modals.reservation')

        <!-- MODAL UPDATE RESERVATION -->
        <div class="modal fade modal-reservation-update" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" @submit.prevent="updateReservation">
                    <div class="modal-header">
                        <h4 class="modal-title">Modification Réservation chambre</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" v-if="selectedChambre">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Chambre<sup>*</sup></label>
                                <select class="form-select" v-model="form.chambre_id" required>
                                    <option hidden value="">--Sélectionnez--</option>
                                    <option v-for="(data, index) in allChambres" :value="data.id">CH-@{{ data.numero}}</option>
                                </select>
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
                            <div class="col-md-6">
                                <label class="form-label">Date début <sup class="text-danger">*</sup></label>
                                <input type="date" class="form-control" v-model="form.date_debut" required>
                            </div>
                            <div class="col-md-6" v-if="form.type_sejour === 'nuit'">
                                <label class="form-label">Date fin <sup class="text-danger">*</sup></label>
                                <input type="date" class="form-control" v-model="form.date_fin" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Prix négocié / Remise (facultatif)</label>
                                <input type="number" class="form-control" v-model.number="form.prix_negocie" placeholder="Laisser vide pour appliquer le prix catalogue">
                            </div>
                            <div class="col-md-6" v-if="pricingSummary">
                                <div class="room-summary">
                                    <div class="room-summary__title">Résumé tarifaire</div>
                                    <div class="room-summary__row">
                                        <span>Durée</span>
                                        <strong>@{{ pricingSummary.days }} j</strong>
                                    </div>
                                    <div class="room-summary__row" v-if="pricingSummary.discount > 0">
                                        <span>Remise</span>
                                        <strong class="text-success">-@{{ pricingSummary.discount }} @{{ selectedChambre.prix_devise }}</strong>
                                    </div>
                                    <div class="room-summary__row room-summary__total">
                                        <span>Total</span>
                                        <span>@{{ pricingSummary.applied }} @{{ selectedChambre.prix_devise }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12" v-if="selectedReservation">
                                <div class="reservation-info d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div>
                                        <strong>Facture :</strong>
                                        <span v-if="hasFactureInfo(selectedReservation)">
                                            <span v-if="selectedReservation.facture">Associée</span>
                                            <span v-else>Non créée</span>
                                        </span>
                                        <span v-else>Non chargée</span>
                                    </div>
                                    <button type="button" class="btn btn-outline-danger btn-sm" @click="requestRemovePayment" :disabled="hasFactureInfo(selectedReservation) && !selectedReservation.facture">
                                        <i class="fa fa-times-circle"></i> Retirer paiement
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex">
                        <button type="button" class="btn btn-danger me-2" data-bs-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-info" :disabled="isLoading">
                            Enregistrer les modifications
                            <span v-if="isLoading" class="spinner-border spinner-border-sm ms-2"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="module" src="{{ asset("assets/js/scripts/reservation.js") }}"></script>
@endpush
