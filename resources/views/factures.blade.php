@extends("layouts.admin")

@section("content")
<div class="content-wrapper">
    <div class="container-full">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="d-flex align-items-center">
            <div class="me-auto">
                <h3 class="page-title">Liste des Factures</h3>
                <!-- <div class="d-inline-block align-items-center">
                    <nav>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#"><i class="mdi mdi-home-outline"></i></a></li>
                            <li class="breadcrumb-item" aria-current="page">Factures</li>
                            <li class="breadcrumb-item active" aria-current="page">Liste des factures</li>
                        </ol>
                    </nav>
                </div> -->
            </div>

        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-12">
                <div class="box">
                    <div class="box-header with-border" style="padding: 1.5rem;">
                        <h4 class="box-title">Invoice List</h4>
                        <h6 class="box-subtitle">Exportez La liste des factures à CSV, Excel, Copy, PDF & Print</h6>
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

<div class="modal fade modal-invoice-detail" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">Facture détails</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <section class="invoice border-0 p-0 printableArea">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-header">
                                <h2 class="d-inline"><span class="fs-30">Facture NO.60</span></h2>
                                <div class="pull-right text-end">
                                    <h3>22 April 2018</h3>
                                </div>
                            </div>
                        </div>
                    <!-- /.col -->
                    </div>
                   
                    <div class="row">
                        <div class="col-12 table-responsive">
                            <table class="table table-bordered">
                            <tbody>
                            <tr>
                                <th>#</th>
                                <th>Description</th>
                                <th>Serial #</th>
                                <th class="text-end">Quantity</th>
                                <th class="text-end">Unit Cost</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                            <tr>
                                <td>1</td>
                                <td>Milk Powder</td>
                                <td>12345678912514</td>
                                <td class="text-end">2</td>
                                <td class="text-end">$26.00</td>
                                <td class="text-end">$52.00</td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>Air Conditioner</td>
                                <td>12345678912514</td>
                                <td class="text-end">1</td>
                                <td class="text-end">$1500.00</td>
                                <td class="text-end">$1500.00</td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>TV</td>
                                <td>12345678912514</td>
                                <td class="text-end">2</td>
                                <td class="text-end">$540.00</td>
                                <td class="text-end">$1080.00</td>
                            </tr>
                            <tr>
                                <td>4</td>
                                <td>Mobile</td>
                                <td>12345678912514</td>
                                <td class="text-end">3</td>
                                <td class="text-end">$320.00</td>
                                <td class="text-end">$960.00</td>
                            </tr>
                            </tbody>
                            </table>
                        </div>
                        <!-- /.col -->
                    </div>
                    <div class="row">
                        <div class="col-12 text-end">
                            <p class="lead"><b>Payment Due</b><span class="text-danger"> 14/08/2018 </span></p>

                            <div>
                                <p>Sub - Total amount  :  $3,592.00</p>
                                <p>Tax (18%)  :  $646.56</p>
                                <p>Shipping  :  $110.44</p>
                            </div>
                            <div class="total-payment">
                                <h3><b>Total :</b> $4,349.00</h3>
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
@endsection

@push("scripts")
    <script src="assets/js/pages/data-table.js"></script>
@endpush
