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

		<!-- Main content -->
		<section class="content">
			<div class="row">
			  <div class="col-12">
                    <div class="box">
                        <div class="box-header no-border" style="padding: 1.5rem">
                            <h4 class="box-title">Liste des serveurs</h4>
                        </div>
                    </div>
			    </div>
                @foreach ($serveurs as $sa)
                    <div class="col-md-6 col-sm-3 col-lg-2 col-6">
                        <div class="box box-body text-center py-30 box-inverse bg-primary">
                            <a href="#">
                                <img class="avatar avatar-xxl" src="assets/images/serveur.png" alt="">
                            </a>
                            <h5 class="mt-10 mb-1"><a class="text-white hover-danger fw-700" href="#">{{ $sa }}</a></h5>
                            <p class="text-white">--- serveur ---</p>
                        </div>
                    </div>
                @endforeach
		    </div>
		</section>
		<!-- /.content -->
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
