@extends("layouts.admin")

@section("content")
<div class="content-wrapper">
    <div class="container-full">
        <div class="content-header">
            <div class="d-flex align-items-center">
               <div class="col-xl-12">
                    @include("components.menus.reports")
                </div>
            </div>
        </div>

        <section class="content">
            <div class="row">
                <div class="col-12">
                    <div class="box">
                        <div class="box-header with-border p-5 text-center">
                            <h4 class="box-title">Rapport des Stocks</h4>
                            <p class="text-muted">État des quantités disponibles et alertes de stock</p>
                        </div>

                        <!-- Filtres -->
                        <div class="box-body">
                            <form method="GET" action="{{ route('reports.stocks') }}">
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <label for="categorie_id">Catégorie</label>
                                        <select name="categorie_id" id="categorie_id" class="form-control">
                                            <option value="">Toutes les catégories</option>
                                            @foreach($categories as $categorie)
                                                <option value="{{ $categorie->id }}" {{ request('categorie_id') == $categorie->id ? 'selected' : '' }}>
                                                    {{ $categorie->libelle }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="statut_stock">Statut du stock</label>
                                        <select name="statut_stock" id="statut_stock" class="form-control">
                                            <option value="">Tous les statuts</option>
                                            <option value="rupture" {{ request('statut_stock') == 'rupture' ? 'selected' : '' }}>En rupture</option>
                                            <option value="alerte" {{ request('statut_stock') == 'alerte' ? 'selected' : '' }}>Stock d'alerte</option>
                                            <option value="normal" {{ request('statut_stock') == 'normal' ? 'selected' : '' }}>Stock normal</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="emplacement_id">Emplacement</label>
                                        <select name="emplacement_id" id="emplacement_id" class="form-control">
                                            <option value="">Tous les emplacements</option>
                                            @foreach($emplacements as $emplacement)
                                                <option value="{{ $emplacement->id }}" {{ request('emplacement_id') == $emplacement->id ? 'selected' : '' }}>
                                                    {{ $emplacement->libelle }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-md-12 d-flex justify-content-center">
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fa fa-filter"></i> Appliquer les filtres
                                            </button>
                                            <a href="{{ route('reports.stocks') }}" class="btn btn-secondary">
                                                <i class="fa fa-refresh"></i> Réinitialiser
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Alertes de stock -->
                        <div class="box-body">
                            @php
                                $produitsRupture = $produitsFiltres->where('statut_stock', 'rupture');
                                $produitsAlerte = $produitsFiltres->where('statut_stock', 'alerte');
                            @endphp

                            @if($produitsRupture->count() > 0)
                            <div class="alert alert-danger">
                                <h4><i class="fa fa-exclamation-triangle"></i> Rupture de Stock</h4>
                                <p class="mb-0"><strong>{{ $produitsRupture->count() }} produit(s) en rupture :</strong> 
                                    @foreach($produitsRupture as $produit)
                                        <span class="badge bg-danger">{{ $produit->libelle }}</span>
                                    @endforeach
                                </p>
                            </div>
                            @endif

                            @if($produitsAlerte->count() > 0)
                            <div class="alert alert-warning">
                                <h4><i class="fa fa-exclamation-circle"></i> Stock Bas</h4>
                                <p class="mb-0"><strong>{{ $produitsAlerte->count() }} produit(s) avec stock bas :</strong> 
                                    @foreach($produitsAlerte as $produit)
                                        <span class="badge bg-warning text-dark">{{ $produit->libelle }} ({{ $produit->stock_actuel }})</span>
                                    @endforeach
                                </p>
                            </div>
                            @endif
                        </div>

                        <!-- Statistiques -->
                        <div class="box-body">
                            <div class="row mb-4">
                                <div class="col-xl-3 col-md-6">
                                    <div class="box bg-primary text-white">
                                        <div class="box-body text-center">
                                            <h2 class="mb-0">{{ $stats['total_produits'] }}</h2>
                                            <p class="mb-0">Total Produits</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="box bg-success text-white">
                                        <div class="box-body text-center">
                                            <h2 class="mb-0">{{ $stats['produits_normal'] }}</h2>
                                            <p class="mb-0">Stock Normal</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="box bg-warning text-white">
                                        <div class="box-body text-center">
                                            <h2 class="mb-0">{{ $stats['produits_alerte'] }}</h2>
                                            <p class="mb-0">Stock Alerte</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="box bg-danger text-white">
                                        <div class="box-body text-center">
                                            <h2 class="mb-0">{{ $stats['produits_rupture'] }}</h2>
                                            <p class="mb-0">En Rupture</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Valorisation du stock -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="box bg-info text-white">
                                        <div class="box-body text-center">
                                            <h2 class="mb-0">{{ number_format($stats['valeur_stock_total'], 0, ',', ' ') }} CDF</h2>
                                            <p class="mb-0">Valeur Totale du Stock</p>
                                            <small>{{ $stats['quantite_stock_total'] }} unités en stock</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tableau des stocks -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="box">
                                        <div class="box-header">
                                            <h4 class="box-title">
                                                Liste des Produits en Stock
                                                <span class="badge bg-primary">{{ $produitsFiltres->count() }} produits</span>
                                            </h4>
                                            <div class="box-tools">
                                                <button class="btn btn-sm btn-info" onclick="exporterExcel()">
                                                    <i class="fa fa-download"></i> Exporter
                                                </button>
                                            </div>
                                        </div>
                                        <div class="box-body">
                                            @if($produitsFiltres->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-hover" id="table-stocks">
                                                        <thead>
                                                            <tr>
                                                                <th>Produit</th>
                                                                <th>Catégorie</th>
                                                                <th class="text-center">Stock Actuel</th>
                                                                <th class="text-center">Seuil Alerte</th>
                                                                <th class="text-center">Entrées</th>
                                                                <th class="text-center">Sorties</th>
                                                                <th class="text-end">Prix Unitaire</th>
                                                                <th class="text-end">Valeur Stock</th>
                                                                <th class="text-center">Statut</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($produitsFiltres as $produit)
                                                                <tr>
                                                                    <td>
                                                                        <strong>{{ $produit->libelle }}</strong>
                                                                        @if($produit->code_barre)
                                                                            <br><small class="text-muted">{{ $produit->code_barre }}</small>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge bg-secondary">{{ $produit->categorie->libelle ?? 'N/A' }}</span>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <span class="badge bg-{{ $produit->couleur_statut }}">
                                                                            <strong>{{ $produit->stock_actuel }} {{ $produit->unite }}</strong>
                                                                        </span>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <small class="text-muted">{{ $produit->seuil_reappro ?? 5 }} {{ $produit->unite }}</small>
                                                                    </td>
                                                                    <td class="text-center text-success">
                                                                        <strong>{{ $produit->total_entrees }}</strong>
                                                                    </td>
                                                                    <td class="text-center text-danger">
                                                                        <strong>{{ $produit->total_sorties }}</strong>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        {{ number_format($produit->prix_unitaire, 0, ',', ' ') }} CDF
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <strong class="text-primary">{{ number_format($produit->valeur_stock, 0, ',', ' ') }} CDF</strong>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        @if($produit->statut_stock == 'rupture')
                                                                            <span class="badge bg-danger">RUPTURE</span>
                                                                        @elseif($produit->statut_stock == 'alerte')
                                                                            <span class="badge bg-warning">ALERTE</span>
                                                                        @else
                                                                            <span class="badge bg-success">NORMAL</span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                        <tfoot>
                                                            <tr class="bg-light">
                                                                <td colspan="7" class="text-end"><strong>Total valeur stock :</strong></td>
                                                                <td class="text-end">
                                                                    <strong class="text-primary">{{ number_format($stats['valeur_stock_total'], 0, ',', ' ') }} CDF</strong>
                                                                </td>
                                                                <td></td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="alert alert-info text-center">
                                                    <i class="fa fa-info-circle"></i> Aucun produit trouvé avec les critères sélectionnés.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
function exporterExcel() {
    const table = document.getElementById('table-stocks');
    const html = table.outerHTML;
    const url = 'data:application/vnd.ms-excel;charset=utf-8,' + encodeURIComponent(html);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'rapport_stocks_' + new Date().toISOString().split('T')[0] + '.xls';
    link.click();
}
</script>

<style>
.alert {
    border-left: 4px solid;
}
.alert-danger { border-left-color: #dc3545; }
.alert-warning { border-left-color: #ffc107; }
</style>
@endpush