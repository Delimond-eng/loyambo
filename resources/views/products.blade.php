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
			  <div class="col-xl-10">
				<div class="box">
					<div class="box-header d-flex justify-content-between align-items-center" style="padding : 1.5rem">
						<h4 class="box-title">Les produits
							<small class="subtitle">Listes des produits</small>
						</h4>
                        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#myModal" class="btn btn-primary text-center btn-rounded">+ Nouveau produit</a>					
					</div>
					<div class="box-body">
						<div class="table-responsive">

                            <table id="example" class="table table-lg invoice-archive">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Period</th>
                                        <th>Issued to</th>
                                        <th>Status</th>
                                        <th>Issue date</th>
                                        <th>Due date</th>
                                        <th>Amount</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                 <tbody>
                                    <tr>
                                        <td>#0025</td>
                                        <td>February 2018</td>
                                        <td>
                                            <h6 class="mb-0">
                                                <a href="#">Jacob</a>
                                                <span class="d-block text-muted">Payment method: Skrill</span>
                                            </h6>
                                        </td>
                                        <td>
                                            <select name="status" class="form-select" data-placeholder="Select status">
                                                <option value="overdue">Overdue</option>
                                                <option value="hold" selected>On hold</option>
                                                <option value="pending">Pending</option>
                                                <option value="paid">Paid</option>
                                                <option value="invalid">Invalid</option>
                                                <option value="cancel">Canceled</option>
                                            </select>
                                        </td>
                                        <td>
                                            April 18, 2018
                                        </td>
                                        <td>
                                            <span class="badge badge-pill badge-success">Paid on Mar 16, 2018</span>
                                        </td>
                                        <td>
                                            <h6 class="mb-0 fw-bold">$36,890 <span class="d-block text-muted fw-normal">VAT $4,859</span></h6>
                                        </td>
                                        <td class="text-center">
                                            <div class="list-icons d-inline-flex">
                                                <a href="#" data-bs-toggle="modal" data-bs-target=".modal-invoice-detail" class="list-icons-item me-10"><i class="fa fa-eye-slash"></i></a>
                                                <div class="list-icons-item dropdown">
                                                    <a href="#" class="list-icons-item dropdown-toggle" data-bs-toggle="dropdown"><i class="fa fa-file-text"></i></a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a href="#" class="dropdown-item"><i class="fa fa-download"></i> Télécharger</a>
                                                        <a href="#" class="dropdown-item"><i class="fa fa-print"></i> Imprimer</a>
                                                        <div class="dropdown-divider"></div>
                                                        <a href="#" class="dropdown-item"><i class="fa fa-pencil"></i> Editer</a>
                                                        <a href="#" class="dropdown-item"><i class="fa fa-remove"></i> Supprimer</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
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
					<h4 class="modal-title" id="myModalLabel">Création nouveau produit</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<form class="form-horizontal">
						<div class="form-group">
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-transparent"><i
                                        class="ti-bookmark-alt text-primary"></i></span>
                                <input type="text" class="form-control ps-15 bg-transparent"
                                    placeholder="BARCODE">
                            </div>
                        </div>
						<div class="form-group">
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-transparent"><i
                                        class=" ti-harddrive text-primary"></i></span>
                                <input type="text" class="form-control ps-15 bg-transparent"
                                    placeholder="Réference">
                            </div>
                        </div>
						<div class="form-group">
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-transparent"><i
                                        class="ti-panel text-primary"></i></span>
                                <input type="text" class="form-control ps-15 bg-transparent"
                                    placeholder="Libellé">
                            </div>
                        </div>

						<div class="form-group">
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-transparent"><i
                                        class="ti-layout-accordion-separated text-primary"></i></span>
                                <select class="form-control ps-15 bg-transparent">
                                    <option value="" selected hidden label="Catégorie"></option>
                                    <option value="cuisine">Cuisine</option>
                                    <option value="boisson">Boisson</option>
                                </select>
                            </div>
                        </div>

						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<div class="input-group mb-3">
										<span class="input-group-text bg-transparent"><i
												class="ti-shopping-cart text-primary"></i></span>
										<input type="number" class="form-control ps-15 bg-transparent"
											placeholder="Seuil réappro.">
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<div class="input-group mb-3">
										<span class="input-group-text bg-transparent"><i
												class="ti-shopping-cart-full text-primary"></i></span>
										<input type="number" class="form-control ps-15 bg-transparent"
											placeholder="Qté initial">
									</div>
								</div>
							</div>
						</div>

						<div class="form-group">
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-transparent"><i
                                        class="ti-layout-accordion-separated text-primary"></i></span>
                                <select class="form-control ps-15 bg-transparent">
                                    <option value="" selected hidden label="Unité"></option>
                                    <option value="boite">Boite</option>
                                    <option value="bouteille">Bouteille</option>
                                    <option value="canette">Canette</option>
                                </select>
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

@push("scripts")
    <script src="assets/js/pages/data-table.js"></script>
@endpush
