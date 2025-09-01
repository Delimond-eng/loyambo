

@extends("layouts.admin")

@section("content")
<div class="content-wrapper">
    <div class="container-full">
        <!-- Content Header (Page header) -->	  
        <div class="content-header">
            <div class="d-flex align-items-center justify-content-between">
                <div class="me-auto">
                    <h3 class="page-title">Liste des commandes</h3>
                    <!-- <div class="d-inline-block align-items-center">
                        <nav>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="mdi mdi-home-outline"></i></a></li>
                                <li class="breadcrumb-item" aria-current="page">Commandes</li>
                                <li class="breadcrumb-item active" aria-current="page">Listes des commandes</li>
                            </ol>
                        </nav>
                    </div> -->
                </div>
                <a href="{{ route("serveurs") }}" class="waves-effect waves-light btn btn-danger text-center btn-rounded">+ Nouvelle commande</a>					
            </div>
        </div>

        <!-- Main content -->
        <section class="content AppFacture" v-cloak>
            <div class="row">
                <div class="col-12">
                    <div class="box">
                        <div class="box-body">
                            <div class="table-responsive rounded card-table">
                                <table class="table border-no" id="example1">
                                    <thead>
                                        <tr>
                                            <th>N° Cmde</th>
                                            <th>N° FACTURE</th>
                                            <th>N° Table</th>
                                            <th>Montant</th>
                                            <th>Emplacement</th>
                                            <th>Serveur</th>
                                            <th>Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(data, index) in allFactures" class="hover-primary">
                                            <td>N°@{{ data.id }}</td>
                                            <td>@{{ data.numero_facture }}</td>
                                            <td>@{{ data.table.numero}}</td>
                                            <td>@{{ data.total_ttc }}</td>
                                            <td>@{{ data.table.emplacement.libelle}}</td>
                                            <td><span class="fw-600">@{{ data.user.name}}</span></td>
                                            <td>												
                                                <span class="badge badge-pill" :class="{'badge-warning-light':data.statut==='en_attente', 'badge-success-light':data.statut==='payée', 'badge-danger-light':data.statut==='annulée'}">@{{ data.statut.replaceAll('_', ' ') }}</span>
                                            </td>
                                            <td>												
                                                <div class="d-flex">
                                                    <button type="button" class="btn btn-success btn-xs me-1"><i class="mdi mdi-printer"></i></button>
                                                    <button type="button" class="btn btn-primary btn-xs me-1" @click="selectedFacture = data" data-bs-toggle="modal" data-bs-target=".modal-invoice-detail"><i class="mdi mdi-eye"></i></button>
                                                    <button type="button" class="btn btn-danger-light btn-xs"><i class="mdi mdi-cancel"></i></button>
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

            <div class="modal fade modal-invoice-detail" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button class="btn btn-success btn-sm me-2 rounded-3"> <i class="mdi mdi-printer"></i></button>
                            <button class="btn btn-primary btn-sm me-2 rounded-3"> <i class="mdi mdi-pencil"></i></button>
                            <h4 class="modal-title" id="myModalLabel">Facture détails</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <section v-if="selectedFacture" class="invoice border-0 p-0 printableArea">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="page-header">
                                            <h2 class="d-inline"><span class="fs-30">@{{ selectedFacture.numero_facture }}</span></h2>
                                            <div class="pull-right text-end">
                                                <h3>@{{ formateDate(selectedFacture.date_facture) }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                <!-- /.col -->
                                </div>
                            
                                <div class="row" v-if="selectedFacture.details">
                                    <div class="col-12 table-responsive">
                                        <table class="table table-bordered">
                                        <tbody>
                                        <tr>
                                            <th>#</th>
                                            <th>Designation</th>
                                            <th class="text-end">Quantité</th>
                                            <th class="text-end">Prix unitaire</th>
                                            <th class="text-end">Sous total</th>
                                        </tr>
                                        <tr v-for="(detail, index) in selectedFacture.details" :key="index">
                                            <td>@{{ index+1 }}</td>
                                            <td>@{{ detail.produit.libelle }}</td>
                                            <td class="text-end">@{{ detail.quantite }}</td>
                                            <td class="text-end">@{{ detail.prix_unitaire }}</td>
                                            <td class="text-end">@{{ detail.total_ligne }}</td>
                                        </tr>
                                        </tbody>
                                        </table>
                                    </div>
                                    <!-- /.col -->
                                </div>
                                <div class="row">
                                    <div class="col-12 text-end">
                                        <p class="lead d-print-none"><b>Statut : </b><span class="badge badge-pill" :class="{'badge-warning-light':selectedFacture.statut==='en_attente', 'badge-success-light':selectedFacture.statut==='payée', 'badge-danger-light':selectedFacture.statut==='annulée'}">@{{ selectedFacture.statut.replaceAll('_', ' ') }}</span></p>

                                        <div>
                                            <p>Total HT  :  @{{ selectedFacture.total_ht }}</p>
                                            <p>Remise (@{{ selectedFacture.remise }}%)  :  0</p>
                                            <p>TVA  :  0</p>
                                        </div>
                                        <div class="total-payment">
                                            <h3><b>Total TTC :</b> @{{ selectedFacture.total_ttc }}</h3>
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
        </section>
        <!-- /.content -->
    
    </div>
</div>
@endsection

@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/facture.js") }}"></script>
@endpush
