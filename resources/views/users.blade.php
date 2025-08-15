

@extends("layouts.admin")

@section("content")
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
	  <div class="container-full">
		<!-- Content Header (Page header) -->
		<div class="content-header">
			<div class="d-flex align-items-center">
				<div class="me-auto">
					<h3 class="page-title">Liste des utilisateurs</h3>
					<div class="d-inline-block align-items-center">
						<nav>
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="{{ route("home") }}"><i class="mdi mdi-home-outline"></i></a></li>
								<li class="breadcrumb-item" aria-current="page">utilisateurs</li>
								<li class="breadcrumb-item active" aria-current="page">Liste</li>
							</ol>
						</nav>
					</div>
				</div>

			</div>
		</div>

		<!-- Main content -->
		<section class="content">
			<div class="row">
				<div class="col-lg-9 col-md-8">
					<div class="box">
                        <div class="box-header d-flex justify-content-between align-items-center" style="padding: 1.5rem;">
                            <h4 class="box-title align-items-start flex-column">
                                Les utilisateurs
                                <small class="subtitle">Actifs et non actifs</small>
                            </h4>
                            <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#myModal" class="btn btn-primary text-center">+ Créer nouveau utilisateur</a>
                        </div>
                        <div class="box-body py-0">
                            <div class="table-responsive">
                                <table class="table no-border">
                                    <thead>
                                        <tr class="text-start">
                                            <th style="width: 50px">Utilisateur</th>
                                            <th style="min-width: 200px"></th>
                                            <th style="min-width: 150px">Emplacement</th>
                                            <th style="min-width: 150px">Status</th>
                                            <th style="min-width: 150px">Dernière activité</th>
                                            <th class="text-end" style="min-width: 150px">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div class="bg-lightest h-50 w-50 l-h-50 rounded text-center overflow-hidden">
                                                    <div class="avatar avatar-lg status-warning">
                                                        <img src="assets/images/avatar/avatar-1.png" class="h-50 align-self-end " alt="">
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="#" class="text-dark fw-600 hover-primary fs-16">Vivamus consectetur</a>
                                                <span class="text-fade d-block">Pharetra, Nulla , Nec, Aliquet</span>
                                            </td>
                                            <td>
                                                <span class="text-dark fw-600 d-block fs-16">
                                                    Intertico
                                                </span>
                                                <span class="text-fade d-block">
                                                    Web, UI/UX Design
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-pill badge-danger-light">Hors ligne</span>
                                            </td>
                                            <td>14 April 2021,<span class="fs-12"> 03:13 AM</span></td>
                                            <td class="text-end">
                                                <a href="#" class="waves-effect waves-light btn btn-info-light btn-circle"><span class="icon-Settings-2 fs-18"><span class="path1"></span><span class="path2"></span></span></a>
                                                <a href="#" class="waves-effect waves-light btn btn-primary-light btn-circle mx-5"><span class="icon-Write"><span class="path1"></span><span class="path2"></span></span></a>
                                                <a href="#" class="waves-effect waves-light btn btn-danger-light btn-circle"><span class="icon-Trash1 fs-18"><span class="path1"></span><span class="path2"></span></span></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="bg-lightest h-50 w-50 l-h-50 rounded text-center overflow-hidden">
                                                    <img src="assets/images/avatar/avatar-2.png" class="h-50 align-self-end" alt="">
                                                </div>
                                            </td>
                                            <td>
                                                <a href="#" class="text-dark fw-600 hover-primary fs-16">Vivamus consectetur</a>
                                                <span class="text-fade d-block">Pharetra, Nulla , Nec, Aliquet</span>
                                            </td>
                                            <td>
                                                <span class="text-dark fw-600 d-block fs-16">
                                                    Intertico
                                                </span>
                                                <span class="text-fade d-block">
                                                    Web, UI/UX Design
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-pill badge-success-light">Connecté</span>
                                            </td>
                                            <td>14 April 2021,<span class="fs-12"> 03:13 AM</span></td>
                                            <td class="text-end">
                                                <a href="#" class="waves-effect waves-light btn btn-info-light btn-circle"><span class="icon-Settings-1 fs-18"><span class="path1"></span><span class="path2"></span></span></a>
                                                <a href="#" class="waves-effect waves-light btn btn-primary-light btn-circle mx-5"><span class="icon-Write"><span class="path1"></span><span class="path2"></span></span></a>
                                                <a href="#" class="waves-effect waves-light btn btn-danger-light btn-circle"><span class="icon-Trash1 fs-18"><span class="path1"></span><span class="path2"></span></span></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="bg-lightest h-50 w-50 l-h-50 rounded text-center overflow-hidden">
                                                    <img src="assets/images/avatar/avatar-3.png" class="h-50 align-self-end" alt="">
                                                </div>
                                            </td>
                                            <td>
                                                <a href="#" class="text-dark fw-600 hover-primary fs-16">Vivamus consectetur</a>
                                                <span class="text-fade d-block">Pharetra, Nulla , Nec, Aliquet</span>
                                            </td>
                                            <td>
                                                <span class="text-dark fw-600 d-block fs-16">
                                                    Intertico
                                                </span>
                                                <span class="text-fade d-block">
                                                    Web, UI/UX Design
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-pill badge-danger-light">Hors ligne</span>
                                            </td>
                                            <td>14 April 2021,<span class="fs-12"> 03:13 AM</span></td>
                                            <td class="text-end">
                                                <a href="#" class="waves-effect waves-light btn btn-info-light btn-circle"><span class="icon-Settings-1 fs-18"><span class="path1"></span><span class="path2"></span></span></a>
                                                <a href="#" class="waves-effect waves-light btn btn-primary-light btn-circle mx-5"><span class="icon-Write"><span class="path1"></span><span class="path2"></span></span></a>
                                                <a href="#" class="waves-effect waves-light btn btn-danger-light btn-circle"><span class="icon-Trash1 fs-18"><span class="path1"></span><span class="path2"></span></span></a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="bg-lightest h-50 w-50 l-h-50 rounded text-center overflow-hidden">
                                                    <img src="assets/images/avatar/avatar-4.png" class="h-50 align-self-end" alt="">
                                                </div>
                                            </td>
                                            <td>
                                                <a href="#" class="text-dark fw-600 hover-primary fs-16">Vivamus consectetur</a>
                                                <span class="text-fade d-block">Pharetra, Nulla , Nec, Aliquet</span>
                                            </td>
                                            <td>
                                                <span class="text-dark fw-600 d-block fs-16">
                                                    Intertico
                                                </span>
                                                <span class="text-fade d-block">
                                                    Web, UI/UX Design
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-pill badge-success-light">Connecté</span>
                                            </td>
                                            <td>14 April 2021,<span class="fs-12"> 03:13 AM</span></td>
                                            <td class="text-end">
                                                <a href="#" class="waves-effect waves-light btn btn-info-light btn-circle"><span class="icon-Settings-1 fs-18"><span class="path1"></span><span class="path2"></span></span></a>
                                                <a href="#" class="waves-effect waves-light btn btn-primary-light btn-circle mx-5"><span class="icon-Write"><span class="path1"></span><span class="path2"></span></span></a>
                                                <a href="#" class="waves-effect waves-light btn btn-danger-light btn-circle"><span class="icon-Trash1 fs-18"><span class="path1"></span><span class="path2"></span></span></a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
				</div>
				<div class="col-lg-3 col-md-4 d-none d-lg-block">
					<div class="box no-shadow">
						<div class="box-body">
						  <a class="btn btn-outline btn-success mb-5 d-flex justify-content-between" href="javascript:void(0)">Connecté <span class="pull-right">103</span></a>
						  <a class="btn btn-outline btn-danger mb-5 d-flex justify-content-between" href="javascript:void(0)">Non connecté <span class="pull-right">19</span></a>
					  </div>
					</div>
				</div>
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
