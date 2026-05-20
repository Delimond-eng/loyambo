@extends("layouts.admin")

@section("content")
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <div class="container-full">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="d-flex align-items-center">
               <div class="col-xl-12">
                    @include("components.menus.reports")
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="row">
                <div class="col-12">
                    <div class="box">
                        <div class="box-header with-border p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="box-title">Produits les plus vendus - {{ $emplacement->libelle }}</h4>
                                    <p class="text-muted mb-0">Classement des produits par quantité vendue</p>
                                </div>
                                <a href="{{ route('reports.produits') }}" 
                                   class="btn btn-secondary">
                                    <i class="fa fa-arrow-left"></i> Retour
                                </a>
                            </div>
                        </div>

                        <div class="box-body">
<div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="date_debut">Date début</label>
                                    <input type="date" id="date_debut" class="form-control" value="{{ request('date_debut') }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="date_fin">Date fin</label>
                                    <input type="date" id="date_fin" class="form-control" value="{{ request('date_fin') }}">
                                </div>
                                <div class="col-md-6 d-flex align-items-end justify-content-end">
                                    <button type="button" class="btn btn-primary me-2" id="appliquerFiltres">
                                        <i class="fa fa-filter"></i> Appliquer
                                    </button>
                                    <a href="{{ route('reports.produits.plusVendus.details', ['emplacement_id' => $emplacement->id] + request()->except(['date_debut','date_fin'])) }}" class="btn btn-secondary me-2">
                                        <i class="fa fa-refresh"></i> Réinitialiser
                                    </a>
                                    <a href="{{ route('reports.produits.plusVendus.export.pdf', ['emplacement_id' => $emplacement->id] + request()->query()) }}" class="btn btn-outline-danger me-2">
                                        <i class="fa fa-file-pdf"></i> PDF
                                    </a>
                                    <a href="{{ route('reports.produits.plusVendus.export.excel', ['emplacement_id' => $emplacement->id] + request()->query()) }}" class="btn btn-outline-success">
                                        <i class="fa fa-file-excel"></i> Excel
                                    </a>
                                </div>
                            </div>
                            <!-- Statistiques globales -->
                            <div class="row mb-4">
                                <div class="col-xl-3 col-md-6">
                                    <div class="box bg-primary text-white">
                                        <div class="box-body text-center">
                                            <h2 class="mb-0">{{ count($produitsVendus['produits']) }}</h2>
                                            <p class="mb-0">Produits Vendus</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="box bg-success text-white">
                                        <div class="box-body text-center">
                                            <h2 class="mb-0">{{ $produitsVendus['total_factures'] }}</h2>
                                            <p class="mb-0">Factures Total</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="box bg-info text-white">
                                        <div class="box-body text-center">
                                            <h2 class="mb-0">{{ $produitsVendus['total_produits_vendus'] }}</h2>
                                            <p class="mb-0">Quantité Total Vendue</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="box bg-warning text-white">
                                        <div class="box-body text-center">
                                            <h2 class="mb-0">{{ number_format($produitsVendus['chiffre_affaires_total'], 0, ',', ' ') }}</h2>
                                            <p class="mb-0">CA Total ({{ $produitsVendus['devise'] }})</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tableau des produits -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="box">
                                        <div class="box-header">
                                            <h4 class="box-title">Classement des Produits</h4>
                                        </div>
                                        <div class="box-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Produit</th>
                                                            <th>Catégorie</th>
                                                            <th>Référence</th>
                                                            <th>Quantité Vendue</th>
                                                            <th>Chiffre d'Affaires</th>
                                                            <th>Nombre de Factures</th>
                                                            <th>Prix Unitaire Moyen</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($produitsVendus['produits'] as $index => $produitData)
                                                            @php
                                                                // Version avec requête optimisée
                                                                if (isset($produitData->quantite_vendue)) {
                                                                    $quantite = $produitData->quantite_vendue;
                                                                    $chiffreAffaires = $produitData->chiffre_affaires;
                                                                    $nombreFactures = $produitData->nombre_factures;
                                                                    $prixUnitaireMoyen = $quantite > 0 ? $chiffreAffaires / $quantite : 0;
                                                                } else {
                                                                    // Version avec collection
                                                                    $quantite = $produitData['quantite_vendue'];
                                                                    $chiffreAffaires = $produitData['chiffre_affaires'];
                                                                    $nombreFactures = $produitData['nombre_factures'];
                                                                    $prixUnitaireMoyen = $quantite > 0 ? $chiffreAffaires / $quantite : 0;
                                                                }
                                                            @endphp
                                                            <tr>
                                                                <td class="text-center">
                                                                    <span class="badge 
                                                                        @if($index == 0) badge-danger
                                                                        @elseif($index < 3) badge-warning
                                                                        @elseif($index < 10) badge-info
                                                                        @else badge-secondary @endif">
                                                                        {{ $index + 1 }}
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    @if(isset($produitData->produit_libelle))
                                                                        {{-- Version optimisée --}}
                                                                        <strong>{{ $produitData->produit_libelle }}</strong>
                                                                        @if($produitData->produit_code_barre)
                                                                            <br>
                                                                            <small class="text-muted">Code: {{ $produitData->produit_code_barre }}</small>
                                                                        @endif
                                                                    @else
                                                                        {{-- Version collection --}}
                                                                        <strong>{{ $produitData['produit']->libelle }}</strong>
                                                                        @if($produitData['produit']->code_barre)
                                                                            <br>
                                                                            <small class="text-muted">Code: {{ $produitData['produit']->code_barre }}</small>
                                                                        @endif
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if(isset($produitData->categorie_libelle))
                                                                        {{-- Version optimisée --}}
                                                                        @if($produitData->categorie_libelle)
                                                                            <span class="badge badge-primary">
                                                                                {{ $produitData->categorie_libelle }}
                                                                            </span>
                                                                        @else
                                                                            <span class="text-muted">-</span>
                                                                        @endif
                                                                    @else
                                                                        {{-- Version collection --}}
                                                                        @if($produitData['produit']->categorie)
                                                                            <span class="badge badge-primary">
                                                                                {{ $produitData['produit']->categorie->libelle }}
                                                                            </span>
                                                                        @else
                                                                            <span class="text-muted">-</span>
                                                                        @endif
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if(isset($produitData->produit_reference))
                                                                        <small class="text-muted">{{ $produitData->produit_reference ?? '-' }}</small>
                                                                    @else
                                                                        <small class="text-muted">{{ $produitData['produit']->reference ?? '-' }}</small>
                                                                    @endif
                                                                </td>
                                                                <td class="text-center">
                                                                    <span class="badge badge-success" style="font-size: 14px;">
                                                                        {{ $quantite }}
                                                                    </span>
                                                                </td>
                                                                <td class="text-end">
                                                                    <strong class="text-success">
                                                                        {{ number_format($chiffreAffaires, 0, ',', ' ') }} 
                                                                        <span class="text-muted" style="font-size: 12px;">{{ $produitsVendus['devise'] }}</span>
                                                                    </strong>
                                                                </td>
                                                                <td class="text-center">
                                                                    {{ $nombreFactures }}
                                                                </td>
                                                                <td class="text-end">
                                                                    {{ number_format($prixUnitaireMoyen, 0, ',', ' ') }} {{ $produitsVendus['devise'] }}
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateDebut = document.getElementById('date_debut');
    const dateFin = document.getElementById('date_fin');
    const appliquerFiltres = document.getElementById('appliquerFiltres');

    if (appliquerFiltres) {
        appliquerFiltres.addEventListener('click', function() {
            const params = new URLSearchParams(window.location.search);
            if (dateDebut && dateDebut.value) {
                params.set('date_debut', dateDebut.value);
            } else {
                params.delete('date_debut');
            }
            if (dateFin && dateFin.value) {
                params.set('date_fin', dateFin.value);
            } else {
                params.delete('date_fin');
            }
            const baseUrl = '{{ route("reports.produits.plusVendus.details", ["emplacement_id" => $emplacement->id]) }}';
            const query = params.toString();
            window.location.href = query ? `${baseUrl}?${query}` : baseUrl;
        });
    }
});
</script>
@endpush
