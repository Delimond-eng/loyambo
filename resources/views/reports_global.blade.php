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
						<h4 class="box-title">Rapports journaliers des ventes
							<small class="subtitle">Regroupés par journées de ventes</small>
						</h4>
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
                                            <button type="button" class="waves-effect waves-light btn btn-sm btn-outline btn-rounded btn-danger">Afficher</button>
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
@endsection

@push("scripts")
    <script src="assets/js/pages/data-table.js"></script>
@endpush
