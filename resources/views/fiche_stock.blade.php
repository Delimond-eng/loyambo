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
			<div class="row d-flex justify-content-center g-4">
                <div class="col-xl-12">
                    @include("components.menus.products")
                </div>

                <div class="col-md-12">
                    <div class="box">
                        <div class="box-header d-flex justify-content-center align-items-center" style="padding : 1.5rem">
                            <h4 class="box-title text-uppercase fw-600">Fiche de stock</h4>
                        </div>
                        <div class="box-body">
                            <table class="table-stock">
                                <thead>
                                    <tr>
                                        <th rowspan="2">Produit</th>
                                        <th rowspan="2">Emplacement</th>
                                        <th colspan="6" class="section-init">Quantité Initiale</th>
                                        <th colspan="6" class="section-entree">Entrée</th>
                                        <th colspan="6" class="section-sortie">Sortie</th>
                                        <th colspan="6" class="section-solde">Solde</th>
                                    </tr>
                                    <tr>
                                        @for ($i = 0; $i < 4; $i++)
                                            <th>Pce</th>
                                            <th>Can.</th>
                                            <th>Kg</th>
                                            <th>Btl</th>
                                            <th>Carton</th>
                                            <th>Scht</th>
                                        @endfor
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($produits as $produit)
                                    <tr>
                                        <td>{{ $produit->libelle }}</td>
                                        <td>{{ $produit->emplacement ?? '-' }}</td>
                                        {{-- Quantité initiale --}}
                                        <td colspan="6" class="section-init">{{ $produit->qte_init }}</td>

                                        {{-- Entrées --}}
                                        <td colspan="6" class="section-entree">{{ $produit->total_entree }}</td>

                                        {{-- Sorties --}}
                                        <td colspan="6" class="section-sortie">{{ $produit->total_sortie }}</td>

                                        {{-- Solde --}}
                                        <td colspan="6" class="section-solde">{{ $produit->solde }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
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

@push("styles")
    <style>
        .table-stock {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            font-size: 13px;
        }
        .table-stock th, .table-stock td {
            border: 1px solid #ccc;
            padding: 5px;
        }
        th {
            background-color: #f0f0f0;
        }
        .section-init { background-color: #aee1ff; }
        .section-entree { background-color: #f4b183; }
        .section-sortie { background-color: #ff9999; }
        .section-solde { background-color: #a7f0a7; }
    </style>
@endpush
