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
            <div class="row mt-25 d-flex justify-content-center">
                <div class="col-md-6 col-sm-3 col-lg-2 col-6" v-for="(chambre, i) in allChambres">
                    <a href="#" @click="triggerOpenCreateReservationModal(chambre)" class="box box-shadowed b-3 border-primary">
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
        </section>


         <div class="modal fade modal-reservation-create" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Réservation chambre</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" v-if="selectedChambre">
                        <div class="d-flex flex-wrap align-items-center">							
							<div class="me-25 bg-danger-light h-80 w-80 l-h-80 rounded text-center">
								  <img :src="selectedChambre.statut==='libre' ? 'assets/images/bed-empty.png' : 'assets/images/bed-2.png'" class="h-50 align-self-center" alt="">
							</div>
							<div class="d-flex flex-column flex-grow-1 my-lg-0 my-10 pe-15">
								<a href="#" class="text-dark fw-600 hover-danger fs-18">
									CH-@{{ selectedChambre.numero }} <br>
                                    Type : @{{ selectedChambre.type }} <br>
								</a>
								<div class="d-flex justify-content-between">
                                    <span class="text-fade fw-600 fs-16">
									Capacité : @{{ selectedChambre.capacite }} 
								    </span>
                                    <span class="fw-600 fs-16">Statut : <span class="badge badge-pill" :class="selectedChambre.statut==='libre' ? 'badge-primary-light' : 'badge-warning-light'">@{{ selectedChambre.statut }}</span></span> 
                                </div>
							</div>
						</div>
                        <div class="mt-3">
                            <h4 class="box-title text-info mb-0"><i class="ti-user me-15"></i> Nouvelle réservation</h4>
                            <hr class="my-15">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Type de pièce d'identité</label>
                                        <select class="form-select">
                                            <option hidden selected value="">--Sélectionnez un type de pièce--</option>
                                            <option>Carte d'Identité</option>
                                            <option>Carte de service</option>
                                            <option>Carte d'Electeur</option>
                                            <option>Passeport</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">N° pièce d'identité</label>
                                        <input type="text" class="form-control" placeholder="n° pièce...">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Nom complet du client</label>
                                        <input type="text" class="form-control" placeholder="ex. Gaston delimond">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">E-mail</label>
                                        <input type="text" class="form-control" placeholder="E-mail. ex:nom@domain">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Téléphone</label>
                                        <input type="text" class="form-control" placeholder="(+243) 8000000">
                                    </div>
                                </div>
                               
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Date début séjour</label>
                                        <input type="date" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Date fin séjour</label>
                                        <input type="date" class="form-control" >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex">
                        <button type="submit" :disabled="isLoading" class="btn btn-success btn-block"><span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span>Réserver</button>
                        <button type="button" class="btn btn-danger float-end" data-bs-dismiss="modal">Fermer</button>
                    </div>
                </div>
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
