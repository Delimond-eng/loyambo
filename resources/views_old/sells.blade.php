@extends("layouts.admin")

@section("content")
<div class="content-wrapper">
    <div class="container-full AppFacture">

        <div class="row d-flex justify-content-center align-items-center g-4 mt-2">
            <div class="col-xl-12">
                @include("components.menus.serveurs")
            </div>
            <div class="col-xl-8">
                <!-- Content Header (Page header) -->	  
                <div class="content-header">
                    <div class="d-lg-flex d-sm-grid d-grid align-items-start justify-content-between">
                        <div class="me-auto">
                            <h3 class="page-title">Liste des produits vendus</h3>
                            <div class="d-inline-block align-items-center">
                                <nav>
                                    <ol class="breadcrumb">
                                    <li class="breadcrumb-item ms-1" aria-current="page">Journée de vente du {{ $saleDay->sale_date->locale('fr')->translatedFormat('d M Y') }}</span></li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                        <div class="form-group me-2">
                            <label class="form-label">Filtrez par dates:</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" class="form-control pull-right" id="reservation">
                                <button onclick="location.reload()" class="btn btn-outline btn-info btn-sm">
                                    <i class="mdi mdi-refresh"></i>
                                </button>
                            </div>
                            <!-- /.input group -->
                        </div>
                        <!-- <div class="form-group">
                            <label class="form-label">Filtrez par serveur:</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </div>
                                <select v-model="filterByServeur" @change="viewAllSells" class="form-control select2" id="servSelect">
                                    <option value="" label="Sélectionnez un serveur" selected hidden></option>
                                    @foreach ($serveurs as $serv)
                                    <option value="{{ $serv->id }}">{{ $serv->name }}</option>
                                    @endforeach
                                </select>
                                <button onclick="location.reload()" class="btn btn-outline btn-info btn-sm">
                                    <i class="mdi mdi-refresh"></i>
                                </button>
                            </div>
                        </div> -->
                    </div>
                </div>
                <!-- Main content -->
                <section class="content" v-cloak>
                    <div class="row">
                        <div class="col-12">
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
                                                        <button @click="viewSellDetails(data)" class="btn btn-xs btn-outline btn-google">Voir détails</button>
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

        <div class="modal fade modal-sell-detail" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Details ventes</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <section v-if="selectedProduct" class="invoice border-0 p-0 printableArea">
                            <div class="row">
                                <div class="col-12">
                                    <div class="page-header mt-1">
                                        <h2 class="d-inline"><span class="fs-30 text-primary">@{{ selectedProduct.produit.libelle }}</span></h2>
                                    </div>
                                </div>
                            <!-- /.col -->
                            </div>
                        
                            <div class="row" v-if="selectedProduct && selectedProduct.byUsers">
                                <div class="col-12 table-responsive">
                                    <table class="table table-bordered">
                                    <tbody>
                                    <tr>
                                        <th>#</th>
                                        <th>Serveur</th>
                                        <th class="text-end">Quantité</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                    <tr v-for="(detail, index) in selectedProduct.byUsers" :key="index">
                                        <td>@{{ index+1 }}</td>
                                        <td>@{{ detail.nom }}</td>
                                        <td class="text-end">@{{ detail.quantite }}</td>
                                        <td class="text-end">@{{ detail.montant }}</td>
                                    </tr>
                                    </tbody>
                                    </table>
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
    
    </div>
</div>
@endsection
@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/facture.js") }}"></script>
@endpush
