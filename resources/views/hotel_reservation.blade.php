@extends("layouts.admin")

@section("content")
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<div class="container-full AppHotel" v-cloak>
	<!-- Content Header (Page header) -->
	 	<div class="content-header">
            <div class="d-sm-block d-md-flex d-lg-flex d-xl-flex align-items-center justify-content-between">
                <div class="me-auto">
					<h3 class="page-title">Reservation chambre</h3>
					<div class="d-inline-block align-items-center">
						<nav>
							<ol class="breadcrumb">
								<li class="breadcrumb-item active" aria-current="page">Reservez en toute sécurité les chambres d'hôtel pour vos clients.</li>
							</ol>
						</nav>
					</div>
                </div>

				<div class="clearfix mt-3 mt-lg-0 mt-xl-0">
					<button type="button" @click="setOperation('transfert')" class="waves-effect waves-light btn-sm btn btn-info mb-2">Transferer chambre <i class="mdi mdi-arrow-expand-left ms-2"></i></button>
					<!-- <button type="button" @click="setOperation('combiner')" class="waves-effect waves-light btn btn-rounded btn-success mb-2">Combiner Table <i class="mdi mdi-link ms-2"></i></button> -->
					<!-- <button type="button" @click="setOperation('')" class="waves-effect waves-light btn btn-rounded btn-info mb-2">Reservation <i class="mdi mdi-lock-outline ms-2"></i></button> -->
					<button type="button" @click="setOperation('')" class="waves-effect waves-light btn btn-sm btn-danger mb-2">Annuler <i class="mdi mdi-cancel ms-2"></i></button>
				</div>
            </div>
        </div>

		<section class="content">
		  	<div>
			  <!-- Default box -->
			  	<div class="box bg-transparent no-shadow b-0">
					<div class="box-body">
						<div class="row">
							<div class="col-md-6 col-sm-3 col-lg-2 col-6" v-for="(chambre, i) in allChambres">
								<a href="#" @click="reserverChambreView(chambre)" class="box box-shadowed b-3" :class="getTableOperationColorClass">
									<div class="box-body ribbon-box">
										<div class="ribbon-two" :class="{'ribbon-two-danger': chambre.statut==='occupée', 'ribbon-two-success':chambre.statut==='libre','ribbon-two-warning':chambre.statut==='réservée' }"><span>@{{ chambre.statut }}</span></div>
                                        <img :src="chambre.statut==='libre' ? 'assets/images/bed-empty.png' : 'assets/images/bed-2.png'" class="img-fluid img-hov-fadein">
										<div style="position:absolute; left: 20px; bottom: 20px;" class="bg-primary fw-900 rounded-circle w-40 h-40 l-h-40 text-center">
											@{{ chambre.numero }}
										</div>
										<span style="position:absolute; right: 20px; top: 20px;" class="badge badge-pill badge-primary-light">@{{ chambre.type }}</span>
									</div> <!-- end box-body-->
								</a>
							</div>
						</div>
					</div>
				</div>
			<!-- /.box-body -->
		  </div>
		  <!-- /.box -->
		</section>

		<!-- Modal mode de paiement -->
		<div class="modal fade modal-reservation" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content" v-if="selectedBed">
					<div class="modal-header">
						<h4 class="modal-title" id="myModalLabel">Reservation chambre n°@{{ selectedBed.numero }}</h4>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label class="form-label">Prix de la chambre</label>
							<div class="d-flex">
								<input v-model="selectedBed.prix" type="number" class="form-control me-2"
									 readonly>
								<input style="width: 100px;" v-model="selectedBed.prix_devise" type="text" class="form-control"
									 readonly>
							</div>
						</div>
						<div class="form-group">
							<label class="form-label">Type de chambre & capacité</label>
							<div class="d-flex">
								<input v-model="selectedBed.type" type="text" class="form-control me-2"
									 readonly>
								<input v-model="selectedBed.capacite" type="text" class="form-control"
									 readonly>
							</div>
						</div>
						<div class="row g-2">
							<div class="col-md-6">
								<div class="form-group">
									<label class="form-label">Date début sejour <sup class="text-danger">*</sup></label>
									<input  type="date" class="form-control me-2" required>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="form-label">Date Fin <sup class="text-danger">*</sup></label>
									<input  type="date" class="form-control me-2" required>
								</div>
							</div>
						</div>
						<p class="text-danger">Sélectionnez un mode de paiement.</p>
						<div class="flexbox flex-justified text-center">
							<a href="#"
								v-for="mode in modes" 
								@click="selectedMode=mode.value; selectedModeRef=''"
								class="b-1 border-primary text-decoration-none rounded py-10 cursor-pointer"
								:class="selectedMode && selectedMode === mode.value ? 'bg-primary text-white' :'text-primary bg-white'"
							>
								<p class="mb-0 fa-3x">
									<i :class="mode.icon"></i>
								</p>
								<p class="mb-2 fw-300">@{{ mode.label }}</p>
							</a>
						</div>
						<div class="form-group mt-2" v-if="selectedMode && selectedMode !== 'cash'">
							<input  
								type="text" 
								v-model="selectedModeRef"
								placeholder="Réference du mode de paiement ..." 
								class="form-control mt-2 mb-2"
							>
						</div>
					</div>
					<div class="modal-footer justify-content-end" v-if="selectedMode">
						<button class="btn btn-success mt-5">
							Valider <i class="mdi mdi-check-all"></i>
						</button>
						<button class="btn btn-danger-light">
							Annuler et fermer
						</button>
					</div>
				</div>
			</div>
		</div>
		<!-- Modal Commandes -->
		<!-- <div class="modal fade modal-commande" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
			<div class="modal-dialog modal-lg modal-dialog-centered">
				<div class="modal-content" v-if="selectedPendingTable">
					<div class="modal-header">
						<h4 class="modal-title" id="myModalLabel">Bons de commande Table @{{ selectedPendingTable.numero }}</h4>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<div class="d-flex">
							<button @click="goToOrderPannel(selectedPendingTable, true)" class="btn btn-primary mb-20 me-2">+ Nouveau bon de commande</button>
							<button v-if="selectedPendingTable.commandes.length === 0" @click="libererTable(selectedPendingTable)" class="btn btn-danger mb-20">Liberer table <i class="mdi mdi-arrange-bring-forward"></i></button>
						</div>
						<div class="row g-3">
							<div class="col-12 col-lg-6" v-for="(cmd, index) in selectedPendingTable.commandes">
								<div class="btn-group">
									<button class="btn btn-primary-light btn-block"><i class="mdi mdi-file-document me-2"></i> Bon de Commande N°@{{ cmd.id }}</button>
									<button class="btn btn-success" @click="printInvoiceFromJson(cmd, selectedPendingTable.emplacement)"><i class="mdi mdi-printer"></i></button>
									<button @click="selectedFacture=cmd" data-bs-toggle="modal" data-bs-target=".modal-pay-trigger" class="btn btn-info"> <span v-if="load_id===cmd.id" class="spinner-border spinner-border-sm"></span> <i v-else class="mdi mdi-glass-tulip"></i> </button>	
									<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target=".modal-invoice-detail" @click="selectedFacture = cmd"><i class="mdi mdi-eye"></i></button>	
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div> -->

		<!-- Modal mode de paiement -->
		<!-- <div class="modal fade modal-pay-trigger" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content" v-if="selectedFacture">
					<div class="modal-header">
						<h4 class="modal-title" id="myModalLabel">Servir le bon de commande n°@{{ selectedFacture.id }}</h4>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<p class="text-danger">Sélectionnez un mode de paiement.</p>
						<div class="flexbox flex-justified text-center">
							<a href="#"
								v-for="mode in modes" 
								@click="selectedMode=mode.value; selectedModeRef=''"
								class="b-1 border-primary text-decoration-none rounded py-20 cursor-pointer"
								:class="selectedMode && selectedMode === mode.value ? 'bg-primary text-white' :'text-primary bg-white'"
							>
								<p class="mb-0 fa-3x">
									<i :class="mode.icon"></i>
								</p>
								<p class="mb-2 fw-300">@{{ mode.label }}</p>
							</a>
						</div>
						<input 
							v-if="selectedMode && selectedMode !== 'cash'" 
							type="text" 
							v-model="selectedModeRef"
							placeholder="Réference du mode de paiement ..." 
							class="form-control mt-2 mb-2"
						>

						<div v-if="selectedMode" class="d-flex justify-content-center align-items-center">
							<button class="btn btn-success mt-5" @click="triggerPayment">
								Valider <i class="mdi mdi-check-all"></i>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div> -->

		<!-- Modal Facture Details -->
		<!-- <div class="modal fade modal-invoice-detail" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button class="btn btn-success btn-sm me-2 rounded-3" @click="printInvoiceFromJson(selectedFacture, selectedPendingTable.emplacement)"> <i class="mdi mdi-printer"></i></button>
						<button class="btn btn-primary btn-sm me-2 rounded-3"> <i class="mdi mdi-pencil"></i></button>
						<h4 class="modal-title" id="myModalLabel">Facture détails</h4>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<section v-if="selectedFacture" class="invoice border-0 p-0 printableArea">
							<div class="row">
								<div class="col-12">
									<div class="page-header">
										<h2 class="d-inline"><span class="fs-30">@{{ selectedFacture.numero_facture }}</span></h2>
										<div class="pull-right text-end">
											<h3>@{{ formateDate2(selectedFacture.date_facture) }}</h3>
										</div>
									</div>
								</div>
							</div>
							<div class="row" v-if="selectedFacture.details">
								<div class="col-12 table-responsive">
									<table class="table table-bordered">
									<tbody>
									<tr>
										<th>#</th>
										<th>Designation</th>
										<th class="text-end">Quantité</th>
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
							</div>
							<div class="row">
								<div class="col-12 text-end">
									<p class="lead d-print-none"><b>Statut : </b><span class="badge badge-pill" :class="{'badge-warning-light':selectedFacture.statut==='en_attente', 'badge-success-light':selectedFacture.statut==='payée', 'badge-danger-light':selectedFacture.statut==='annulée'}">@{{ selectedFacture.statut.replaceAll('_', ' ') }}</span></p>

									<div>
										<p>Total HT  :  @{{ selectedFacture.total_ht }}</p>
										<p>Remise (@{{ selectedFacture.remise }}%)  :  0</p>
										<p>TVA  :  0</p>
									</div>
									<div class="total-payment">
										<h3><b>Total TTC :</b> @{{ selectedFacture.total_ttc }}</h3>
									</div>
								</div>
							</div>
						</section>
					</div>
				</div>
			</div>
		</div> -->

	</div>
</div>



@endsection
@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/hotel.js") }}"></script>	
@endpush
