@php
  $catgs = [
    "Boisson sucré",
    "Accompagnement",
    "Grillades",
    "Compositions",
    "Liboke",
    "Sauces"
  ];
@endphp

@extends("layouts.admin")

@section("content")
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
	  <div class="container-full">
		<!-- Content Header (Page header) -->
		<div class="content-header">
		</div>
		<!-- Main content -->
		<section class="content">
			<div class="row">
			  <div class="col-xl-6">
				<div class="box">
					<div class="box-header d-flex justify-content-between align-items-center" style="padding : 1.5rem">
						<h4 class="box-title">Les catégories
							<small class="subtitle">Listes des catégories de produit</small>
						</h4>
                        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#myModal" class="btn btn-primary text-center btn-rounded">+ Nouvelle catégorie</a>					
					</div>
					<div class="box-body p-0">
						<div class="table-responsive">
							<table class="table no-border table-vertical-center">
								<thead>
									<tr>
										<th class="p-0" style="width: 50px"></th>
										<th class="p-0" style="min-width: 150px"></th>
										<th class="p-0" style="min-width: 200px"></th>
									</tr>
								</thead>
								<tbody>
									@foreach ($catgs as $ca)
                                    <tr class="border-bottom">
										<td>
											<div class="h-50 w-50 l-h-50 rounded text-center">
												  <img src="assets/images/service.jpg" class="h-50" alt="">
											</div>
										</td>
										<td>
											<a href="#" class="text-dark fw-600 hover-primary fs-16 fw-700">{{ $ca }}</a>
										</td>

										<td class="d-flex align-items-center justify-content-end">
											<a href="#" class="btn btn-primary-light btn-sm me-1"><span class="icon-Dinner1 fs-18"><span class="path1"></span><span class="path2"></span></span></a>
											<a href="#" class="btn btn-info-light btn-sm me-1"><span class="icon-Write fs-18"><span class="path1"></span><span class="path2"></span></span></a>
											<a href="#" class="btn btn-danger-light btn-sm"><span class="icon-Trash1 fs-18"><span class="path1"></span><span class="path2"></span></span></a>
										</td>
									</tr>
                                    @endforeach
								</tbody>
							</table>
						</div>
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
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-transparent"><i
                                        class="ti-write text-primary"></i></span>
                                <input type="text" class="form-control ps-15 bg-transparent"
                                    placeholder="Libellé">
                            </div>
                        </div>
						<div class="form-group">
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-transparent"><i
                                        class="ti-panel text-primary"></i></span>
                                <input type="text" class="form-control ps-15 bg-transparent"
                                    placeholder="Code">
                            </div>
                        </div>
						<div class="form-group">
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-transparent"><i
                                        class="ti-layout-accordion-separated text-primary"></i></span>
                                <select class="form-control ps-15 bg-transparent">
                                    <option value="" selected hidden label="Sélectionnez service"></option>
                                    <option value="cuisine">Cuisine</option>
                                    <option value="boisson">Boisson</option>
                                </select>
                            </div>
                        </div>
						<div class="form-group">
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-transparent"><i
                                        class="ti-brush text-primary"></i></span>
                                <input type="color" class="form-control ps-15 bg-transparent"
                                    placeholder="Username">
                            </div>
                        </div>
					</form>
				</div>
				<div class="modal-footer d-flex">
					<button type="button" class="btn btn-success btn-block" data-bs-dismiss="modal">Enregistrer</button>
					<button type="button" class="btn btn-danger float-end" data-bs-dismiss="modal">Fermer</button>
				</div>
			</div>
			<!-- /.modal-content -->
		</div>
		<!-- /.modal-dialog -->
	</div>
  <!-- /Popup Model Plase Here -->
@endsection
