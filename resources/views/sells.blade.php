@extends("layouts.admin")

@section("content")
<div class="content-wrapper">
    <div class="container-full">
        <!-- Content Header (Page header) -->	  
        <div class="content-header">
            <div class="d-flex align-items-center">
                <div class="me-auto">
                    <h3 class="page-title">Liste des produits vendus</h3>
                    <!-- <div class="d-inline-block align-items-center">
                        <nav>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="mdi mdi-home-outline"></i></a></li>
                                <li class="breadcrumb-item" aria-current="page">Ventes</li>
                                <li class="breadcrumb-item active" aria-current="page">Listes des ventes</li>
                            </ol>
                        </nav>
                    </div> -->
                </div>
                
            </div>
        </div>

        <!-- Main content -->
        <section class="content AppFacture" v-cloak>
            <div class="row">
                <div class="col-lg-8">
                    <div class="box">
                        <div class="box-body">
                            <div class="table-responsive rounded card-table">
                                <table class="table border-no" id="example1">
                                    <thead>
                                        <tr>
                                            <th>Produit</th>
                                            <th>Quantité vendu</th>
                                            <th>Somme vendue</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="hover-primary" v-for="(data, index) in allSells">
                                            <td>@{{ data.produit.libelle }}</td>
                                            <td>@{{ data.total_vendu }}</td>
                                            <td>@{{ data.total_vendu * data.produit.prix_unitaire }}</td>
                                            <td>												
                                                <div class="btn-group">
                                                <a class="hover-primary dropdown-toggle no-caret" data-bs-toggle="dropdown"><i class="fa fa-ellipsis-h"></i></a>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="#">Accept Order</a>
                                                    <a class="dropdown-item" href="#">Reject Order</a>
                                                </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="fs-20 fw-800">
                                            <td class="fw-600">Total</td>
                                            <td>@{{ totalQuantite }}</td>
                                            <td>@{{ totalMontant }}</td>
                                            <td>												
                                            
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
@endsection
@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/facture.js") }}"></script>
@endpush
