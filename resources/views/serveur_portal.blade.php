@extends("layouts.admin")

@section("content")
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<div class="container-full AppService" v-cloak>
		<div class="data-loading" v-if="isDataLoading">
			<img src="{{ asset("assets/images/loading.gif") }}" alt="loading">
			<h4 class="mt-2">Chargement...</h4>
		</div>
	<!-- Content Header (Page header) -->
	 	<div class="content-header"  v-if="!isDataLoading">
            <div class="d-sm-block d-md-flex d-lg-flex d-xl-flex align-items-center justify-content-between">
                <!-- <a href="{{ route("serveurs") }}" class="btn btn-xs btn-dark me-2"><i class="mdi mdi-arrow-left me-1"></i> Retour</a> -->
                <div class="me-auto">
					@if (Auth::user()->role === "serveur")
                    <h3 class="page-title">Bienvenue, <span class="fw-800 text-primary">{{ Auth::user()->name }}</span> </h3>
					@else
                    <h3 class="page-title">Bienvenue à la session de <span class="fw-800 text-primary" v-if="userSession">@{{ userSession.name }}</span> </h3>
					@endif
					<div class="d-inline-block align-items-center">
						<nav>
							<ol class="breadcrumb">
								<li class="breadcrumb-item active" aria-current="page">Vous êtes au portail de vente du serveur.</li>
							</ol>
						</nav>
					</div>
                </div>

				<div class="clearfix mt-3 mt-lg-0 mt-xl-0">
					<button type="button" @click="setOperation('transfert')" class="waves-effect waves-light btn-sm btn btn-info mb-2 rounded-2">Transferer Table <i class="mdi mdi-swap-horizontal ms-2"></i></button>
					<button type="button" @click="setOperation('combiner')" class="waves-effect waves-light btn-sm btn btn-success mb-2 rounded-2">Combiner Table <i class="mdi mdi-link ms-2"></i></button>
					<!-- <button type="button" @click="setOperation('')" class="waves-effect waves-light btn btn-rounded btn-info mb-2">Reservation <i class="mdi mdi-lock-outline ms-2"></i></button> -->
					<button type="button" @click="setOperation('')" class="waves-effect waves-light btn btn-sm btn-danger rounded-2 mb-2">Annuler <i class="mdi mdi-cancel ms-2"></i></button>
				</div>
            </div>
        </div>

		<section class="content" v-if="!isDataLoading">
		  	<div>
			  <!-- Default box -->
			  	<div class="box bg-transparent no-shadow b-0">
					<div class="box-body">
						<div class="row d-flex justify-content-center">
							<div class="col-md-6 col-sm-3 col-lg-2 col-6" v-for="(table, i) in allTables">
								<a href="#" @click="goToOrderPannel(table)" class="box box-shadowed b-3" :class="getTableOperationColorClass">
									<div class="box-body ribbon-box">
										<div class="ribbon-two" :class="{'ribbon-two-danger': table.statut==='occupée', 'ribbon-two-success':table.statut==='libre','ribbon-two-warning':table.statut==='réservée' }"><span>@{{ table.statut }}</span></div>
										<img v-if="table.emplacement.type !== 'hôtel'" :src="table.statut==='libre' ? 'assets/images/table4.png' : 'assets/images/table-reseved.png'" class="img-fluid img-hov-fadein">
                                        <img v-else :src="table.statut==='libre' ? 'assets/images/bed-empty.png' : 'assets/images/bed-2.png'" class="img-fluid img-hov-fadein">
										<div style="position:absolute; left: 20px; bottom: 20px;" class="bg-primary fw-900 rounded-circle w-40 h-40 l-h-40 text-center">
											@{{ table.numero }}
										</div>
									</div> 
								</a>
							</div>
						</div>
					</div>
				</div>
			<!-- /.box-body -->
		  	</div>
		  <!-- /.box -->
		</section>

		<!-- Modal Commandes -->
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
							<button v-if="selectedPendingTable.commandes.length === 0" @click="libererTable(selectedPendingTable)" class="btn btn-danger btn-xs mb-20">Liberer table <i class="mdi mdi-arrange-bring-forward"></i></button>
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
											<button class="btn btn-circle btn-sm btn-primary">
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
											<button @click="selectedFacture=cmd" 
													data-bs-toggle="modal" 
													data-bs-target=".modal-pay-trigger" 
													class="btn btn-circle btn-sm btn-dark">
												<span v-if="load_id===cmd.id" class="spinner-border spinner-border-sm"></span>
												<i v-else class="fa fa-money"></i>
											</button>
											@endif

											<!-- Voir facture -->
											<button class="btn btn-circle btn-sm btn-info" 
													data-bs-toggle="modal" 
													data-bs-target=".modal-invoice-detail" 
													@click="selectedFacture = cmd">
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

		<!-- Modal mode de paiement -->
		<div class="modal fade modal-pay-trigger" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
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
						<!-- Input de référence uniquement si le mode n'est pas CASH et qu'un mode est sélectionné -->
						<input 
							v-if="selectedMode && selectedMode !== 'cash'" 
							type="text" 
							v-model="selectedModeRef"
							placeholder="Réference du mode de paiement ..." 
							class="form-control rounded-2 mt-2 mb-2"
						>

						<div v-if="selectedMode" class="d-flex justify-content-center align-items-center">
							<button class="btn btn-success rounded-2 mt-5" style="width:100%" @click="triggerPayment">
								Valider <i class="mdi mdi-check-all"></i>
							</button>
						</div>
					</div>
				</div>
				<!-- /.modal-content -->
			</div>
			<!-- /.modal-dialog -->
		</div>

		<!-- Modal Facture Details -->
		<div class="modal fade modal-invoice-detail" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<!-- <button class="btn btn-success btn-sm me-2 rounded-3" @click="printInvoiceFromJson(selectedFacture, selectedPendingTable.emplacement)"> <i class="mdi mdi-printer"></i></button>
						<button class="btn btn-primary btn-sm me-2 rounded-3"> <i class="mdi mdi-pencil"></i></button> -->
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
							<!-- /.col -->
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
								<!-- /.col -->
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
								<!-- /.col -->
							</div>
						</section>
					</div>
				</div>
				<!-- /.modal-content -->
			</div>
			<!-- /.modal-dialog -->
		</div>
	</div>
</div>

@if (Auth::user()->role === 'serveur')
     <button class="fixed-btn" onclick="location.href='/orders'">
		<div class="btn-badge AppDashboard" v-cloak>@{{ counts.pendings ?? 0 }}</div>
		<i class="mdi mdi-basket fs-18"></i>
	</button>
@endif
@endsection
@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/service.js") }}"></script>
	<script type="module" src="{{ asset("assets/js/scripts/dashboard.js") }}"></script>	
@endpush

@push("styles")
	<style>
        .fixed-btn {
            position: fixed;
            bottom: 50px;
            right: 20px;
            background: #4c95dd;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 0 15px rgba(1, 9, 18, 0.3);
            animation: glow 1.5s infinite alternate;
            color: #FFFFFF;
        }

        @keyframes glow {
            from {
            box-shadow: 0 0 10px rgba(76, 95, 221, 0.5),
                        0 0 20px rgba(76, 149, 221, 0.3);
            }
            to {
            box-shadow: 0 0 25px rgba(76, 149, 221, 0.9),
                        0 0 50px rgba(76, 149, 221, 0.7);
            }
        }

        .btn-badge {
            position: absolute;
			top: -5px;
			left: 50%;
			transform: translateX(-50%);
			min-width: 20px;
			height: 20px;
			padding: 0 3px;
			display: flex;
			align-items: center;
			justify-content: center;
			background: #ef4444;
			color: #fff;
			font-weight: 500;
			border: 2px solid #FFF;
			font-size: 10px;
			border-radius: 5px;
			box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
        }

        .fixed-btn-container {
            position: relative;
            width: 70px;
            height: 70px;
        }
    </style>
@endpush
