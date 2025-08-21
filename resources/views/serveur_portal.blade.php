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
                    <h3 class="page-title">Bienvenu à la session de <span class="fw-800 text-primary" v-if="userSession">@{{ userSession.name }}</span> <span v-else>Inconnu</span></h3>
                </div>

				<div class="btn-group">
					<button class="btn btn-success gallery-header-center-right-links-current" id="filter-all"> <i class="fa fa-exchange me-2"></i> Transferer Table</button>
					<button class="btn btn-warning gallery-header-center-right-links-current" id="filter-studio"><i class="mdi mdi-link me-2"></i>Combiner Table</button>
					<button class="btn btn-info gallery-header-center-right-links-current" id="filter-landscape"><i class="mdi mdi-lock-plus me-2"></i>Reservation</button>
					<button class="btn btn-danger gallery-header-center-right-links-current" id="filter-landscape"><i class="mdi mdi-close-box me-2"></i>Annuler</button>
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
										<div class="ribbon" :class="{'ribbon-danger': table.statut==='occupée', 'ribbon-success':table.statut==='libre','ribbon-warning':table.statut==='réservée' }"><span>@{{ table.statut }}</span></div>
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

	</div>
</div>
<!-- /.content-wrapper -->

@endsection
@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/service.js") }}"></script>
@endpush
