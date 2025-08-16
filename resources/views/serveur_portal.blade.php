@extends("layouts.admin")

@section("content")
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<div class="container-full">
	<!-- Content Header (Page header) -->
	 	<div class="content-header">
            <div class="d-flex align-items-center">
                <div class="me-auto">
                    <h3 class="page-title">Bienvenu à la session de Gaston</h3>
                </div>
            </div>
        </div>

		<section class="content">
		  	<div>
			  	<div class="box bg-transparent no-shadow b-0">
				<div class="box-body text-center p-0">
					<div class="btn-group">
					  <button class="btn btn-success gallery-header-center-right-links-current" id="filter-all"> <i class="fa fa-exchange me-2"></i> Transferer Table</button>
					  <button class="btn btn-warning gallery-header-center-right-links-current" id="filter-studio"><i class="mdi mdi-link me-2"></i>Combiner Table</button>
					  <button class="btn btn-info gallery-header-center-right-links-current" id="filter-landscape"><i class="mdi mdi-lock-plus me-2"></i>Reservation</button>
					  <button class="btn btn-danger gallery-header-center-right-links-current" id="filter-landscape"><i class="mdi mdi-close-box me-2"></i>Annuler</button>
					</div>
				</div>
			  </div>
			  <!-- Default box -->
			  	<div class="box bg-transparent no-shadow b-0">
					<div class="box-body">
						<div class="row">
							@for($i=0; $i<5; $i++)
							<div class="col-md-6 col-sm-3 col-lg-2 col-6">
								<a href="#" class="box">
									<div class="box-body ribbon-box">
										<div class="ribbon {{  $i%2 ? 'ribbon-danger' : 'ribbon-success' }}"><span>{{ $i%2 ? "Occupée" : "Libre" }}</span></div>
										<img src="{{ $i%2 ? 'assets/images/table5.png' : 'assets/images/table4.avif' }}" class="img-fluid">
										<div style="position:absolute; left: 20px; bottom: 20px;" class="bg-primary fw-900 rounded-circle w-40 h-40 l-h-40 text-center">
											0{{ $i+1 }}
										</div>
									</div> <!-- end box-body-->
								</a>
							</div>
							@endfor
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
