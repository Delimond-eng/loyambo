@php
$serveurs = [
    "Gaston Delimond",
    "Lionnel nawej",
    "Djo Perkins",
    "Isaac Lebo",
    "Kasanda Ilain",
]
@endphp

@extends("layouts.admin")

@section("content")
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
	  <div class="container-full">
		<!-- Content Header (Page header) -->

		<section class="content">

		  <div class="row">
			  <div class="col-12 col-lg-8">
				<div class="box">
				  <div class="box-header" style="padding:1.5rem">
					<h4 class="box-title">SERVEURS EN SERVICE</h4>
				  </div>

				  <div class="box-body">
					<div class="table-responsive">
						<table class="table product-overview">
							<thead>
								<tr>
									<th>Serveur</th>
									<th style="text-align:center">Total facturé</th>
									<th style="text-align:center">Action</th>
								</tr>
							</thead>
							<tbody>
								@foreach ($serveurs as $sa)
									<tr>
										<td>
											<div class="d-flex align-items-center">
												<img src="assets/images/service.jpg" class="me-2" alt="" width="80">
												<div>
													<h5 class="fw-500">{{ $sa }}</h5>
													<p>14 April 2021,<span class="fs-12"> 03:13 AM</span></p>
												</div>
											</div>
										</td>
										<td align="center" class="fw-900">$270</td>
										<td align="center"><a href="javascript:void(0)" class="btn btn-circle btn-danger btn-xs" title="" data-bs-toggle="tooltip" data-bs-original-title="Delete"><i class="fa fa-sign-out"></i></a></td>
									</tr>
								@endforeach
								
							</tbody>
						</table>
						<a href="{{ route('home') }}" class="btn btn-info"><i class="fa fa-arrow-left"></i> Retour</a>
					</div>

				  </div>
				</div>
			  </div>
		  </div>

		</section>
	  </div>
  </div>
  <!-- /.content-wrapper -->

    <!-- Popup Model Plase Here -->
	<div id="myModal" class="modal fade in" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="myModalLabel">Add Contact</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<form class="form-horizontal">
						<div class="form-group">
							<label class="col-md-12 form-label">Name</label>
							<div class="col-md-12">
								<input type="text" class="form-control" placeholder="Name">
							</div>
							<label class="col-md-12 form-label">Email</label>
							<div class="col-md-12">
								<input type="email" class="form-control" placeholder="Email">
							</div>
							<label class="col-md-12 form-label">Phone</label>
							<div class="col-md-12">
								<input type="tel" class="form-control" placeholder="Phone">
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-12 form-label">Address</label>
							<div class="col-md-12">
								<textarea class="form-control" placeholder=""></textarea>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-success" data-bs-dismiss="modal">Add</button>
					<button type="button" class="btn btn-danger float-end" data-bs-dismiss="modal">Cancel</button>
				</div>
			</div>
			<!-- /.modal-content -->
		</div>
		<!-- /.modal-dialog -->
	</div>
  <!-- /Popup Model Plase Here -->
@endsection
