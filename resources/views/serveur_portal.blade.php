@extends("layouts.admin")

@section("content")
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<div class="container-full AppService" v-cloak>
	<!-- Content Header (Page header) -->
	 	<div class="content-header">
            <div class="d-sm-block d-md-flex d-lg-flex d-xl-flex align-items-center justify-content-between">
				<a href="{{ route("serveurs") }}" class="btn btn-xs btn-dark me-2"><i class="mdi mdi-arrow-left me-1"></i> Retour</a>
                <div class="me-auto">
                    <h3 class="page-title">Bienvenu à la session de <span class="fw-800 text-primary" v-if="userSession">@{{ userSession.name }}</span> <span v-else>{{ Auth::user()->name }}</span></h3>
                </div>

				<!-- <div class="btn-group">
					<button class="btn btn-success gallery-header-center-right-links-current" id="filter-all"> <i class="fa fa-exchange me-2"></i> Transferer Table</button>
					<button class="btn btn-warning gallery-header-center-right-links-current" id="filter-studio"><i class="mdi mdi-link me-2"></i>Combiner Table</button>
					<button class="btn btn-info gallery-header-center-right-links-current" id="filter-landscape"><i class="mdi mdi-lock-plus me-2"></i>Reservation</button>
					<button class="btn btn-danger gallery-header-center-right-links-current" id="filter-landscape"><i class="mdi mdi-close-box me-2"></i>Annuler</button>
				</div> -->

				<div class="clearfix">
					<button type="button" class="waves-effect waves-light btn btn-rounded btn-primary mb-2">Transferer Table <i class="mdi mdi-arrow-expand-left ms-2"></i></button>
					<button type="button" class="waves-effect waves-light btn btn-rounded btn-success mb-2">Combiner Table <i class="mdi mdi-link ms-2"></i></button>
					<button type="button" class="waves-effect waves-light btn btn-rounded btn-info mb-2">Reservation <i class="mdi mdi-lock-outline ms-2"></i></button>
					<button type="button" class="waves-effect waves-light btn btn-rounded btn-danger mb-2">Annuler <i class="mdi mdi-cancel ms-2"></i></button>
				</div>
            </div>
        </div>

		<section class="content">
		  	<div>
			  <!-- Default box -->
			  	<div class="box bg-transparent no-shadow b-0">
					<div class="box-body">
						<div class="row">
							<div class="col-md-6 col-sm-3 col-lg-2 col-6" v-for="(table, i) in allTables">
								<a href="#" @click="goToOrderPannel(table)" class="box">
									<div class="box-body ribbon-box">
										<div class="ribbon-two" :class="{'ribbon-two-danger': table.statut==='occupée', 'ribbon-two-success':table.statut==='libre','ribbon-two-warning':table.statut==='réservée' }"><span>@{{ table.statut }}</span></div>
										<img v-if="table.emplacement.type !== 'hôtel'" :src="table.statut==='libre' ? 'assets/images/table4.png' : 'assets/images/table-reseved.png'" class="img-fluid">
                                        <img v-else :src="table.statut==='libre' ? 'assets/images/bed-empty.png' : 'assets/images/bed-2.png'" class="img-fluid">
										<div style="position:absolute; left: 20px; bottom: 20px;" class="bg-primary fw-900 rounded-circle w-40 h-40 l-h-40 text-center">
											@{{ table.numero }}
										</div>
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

		<div class="modal fade modal-commande" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
			<div class="modal-dialog modal-lg modal-dialog-centered">
				<div class="modal-content" v-if="selectedPendingTable">
					<div class="modal-header">
						<h4 class="modal-title" id="myModalLabel">Bons de commande Table @{{ selectedPendingTable.numero }}</h4>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<button @click="goToOrderPannel(selectedPendingTable, true)" class="btn btn-primary mb-20">+ Nouveau bon de commande</button>

						<div class="row g-3">
							<div class="col-12 col-lg-6" v-for="(cmd, index) in selectedPendingTable.commandes">
								<div class="btn-group">
									<button class="btn btn-primary-light btn-block">Bon de Commande N°@{{ cmd.id }}</button>
									<button class="btn btn-success"><i class="mdi mdi-printer"></i></button>
									<button class="btn btn-warning"><i class="mdi mdi-glass-tulip"></i></button>	
									<button class="btn btn-primary"><i class="mdi mdi-eye"></i></button>	
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- /.modal-content -->
			</div>
			<!-- /.modal-dialog -->
		</div>

	</div>
</div>
<!-- /.content-wrapper -->

@endsection
@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/service.js") }}"></script>
@endpush
