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
                            <h4 class="box-title">Rapport des Mouvements de Stock</h4>
                            <p class="text-muted">Historique complet des entrées, sorties et transferts</p>
                        </div>

                        <!-- Filtres -->
                        <div class="box-body">
                            <form method="GET" action="{{ route('reports.Mouvements') }}">
                                <div class="row mb-4">
                                    <div class="col-md-3">
                                        <label for="produit_id">Produit</label>
                                        <select name="produit_id" id="produit_id" class="form-control">
                                            <option value="">Tous les produits</option>
                                            @foreach($produits as $produit)
                                                <option value="{{ $produit->id }}" {{ request('produit_id') == $produit->id ? 'selected' : '' }}>
                                                    {{ $produit->libelle }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="categorie_id">Catégorie</label>
                                        <select name="categorie_id" id="categorie_id" class="form-control">
                                            <option value="">Toutes catégories</option>
                                            @foreach($categories as $categorie)
                                                <option value="{{ $categorie->id }}" {{ request('categorie_id') == $categorie->id ? 'selected' : '' }}>
                                                    {{ $categorie->libelle }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="type_mouvement">Type mouvement</label>
                                        <select name="type_mouvement" id="type_mouvement" class="form-control">
                                            <option value="">Tous types</option>
                                            <option value="entrée" {{ request('type_mouvement') == 'entrée' ? 'selected' : '' }}>Entrées</option>
                                            <option value="sortie" {{ request('type_mouvement') == 'sortie' ? 'selected' : '' }}>Sorties</option>
                                            <option value="vente" {{ request('type_mouvement') == 'vente' ? 'selected' : '' }}>Ventes</option>
                                            <option value="transfert" {{ request('type_mouvement') == 'transfert' ? 'selected' : '' }}>Transferts</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="emplacement_id">Emplacement</label>
                                        <select name="emplacement_id" id="emplacement_id" class="form-control">
                                            <option value="">Tous emplacements</option>
                                            @foreach($emplacements as $emplacement)
                                                <option value="{{ $emplacement->id }}" {{ request('emplacement_id') == $emplacement->id ? 'selected' : '' }}>
                                                    {{ $emplacement->libelle }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="date_debut">Période</label>
                                        <div class="input-group">
                                            <input type="date" name="date_debut" id="date_debut" class="form-control" 
                                                   value="{{ request('date_debut') }}" placeholder="Date début">
                                            <input type="date" name="date_fin" id="date_fin" class="form-control" 
                                                   value="{{ request('date_fin') }}" placeholder="Date fin">
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-md-12 d-flex justify-content-center">
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fa fa-filter"></i> Appliquer les filtres
                                            </button>
                                            <a href="{{ route('reports.Mouvements') }}" class="btn btn-secondary">
                                                <i class="fa fa-refresh"></i> Réinitialiser
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Statistiques -->
                        <div class="box-body">
                            <div class="row mb-4">
                                <div class="col-xl-2 col-md-4">
                                    <div class="box bg-primary text-white">
                                        <div class="box-body text-center">
                                            <h2 class="mb-0">{{ $stats['total_mouvements'] }}</h2>
                                            <p class="mb-0">Total Mouvements</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-2 col-md-4">
                                    <div class="box bg-success text-white">
                                        <div class="box-body text-center">
                                            <h2 class="mb-0">{{ $stats['total_entrees'] }}</h2>
                                            <p class="mb-0">Entrées</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-2 col-md-4">
                                    <div class="box bg-danger text-white">
                                        <div class="box-body text-center">
                                            <h2 class="mb-0">{{ $stats['total_sorties'] }}</h2>
                                            <p class="mb-0">Sorties</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="box bg-info text-white">
                                        <div class="box-body text-center">
                                            <h2 class="mb-0">{{ number_format($stats['valeur_entrees'], 0, ',', ' ') }} CDF</h2>
                                            <p class="mb-0">Valeur Entrées</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <div class="box bg-warning text-white">
                                        <div class="box-body text-center">
                                            <h2 class="mb-0">{{ number_format($stats['valeur_sorties'], 0, ',', ' ') }} CDF</h2>
                                            <p class="mb-0">Valeur Sorties</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tableau des mouvements -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="box">
                                        <div class="box-header">
                                            <h4 class="box-title">
                                                Historique des Mouvements
                                                <span class="badge bg-primary">{{ $mouvements->total() }} mouvements</span>
                                            </h4>
                                            <div class="box-tools">
                                                <button class="btn btn-sm btn-info" onclick="exporterExcel()">
                                                    <i class="fa fa-download"></i> Exporter
                                                </button>
                                            </div>
                                        </div>
                                        <div class="box-body">
                                            @if($mouvements->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-hover" id="table-mouvements">
                                                        <thead>
                                                            <tr>
                                                                <th>Date/Heure</th>
                                                                <th>Produit</th>
                                                                <th>Catégorie</th>
                                                                <th>Type</th>
                                                                <th class="text-center">Quantité</th>
                                                                <th class="text-end">Prix Unitaire</th>
                                                                <th class="text-end">Valeur</th>
                                                                <th>Numéro Doc</th>
                                                                <th>Utilisateur</th>
                                                                <th>Emplacement</th>
                                                                <th>Source/Destination</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($mouvements as $mouvement)
                                                                <tr class="@if($mouvement->type_mouvement == 'entrée') table-success @elseif(in_array($mouvement->type_mouvement, ['sortie', 'vente'])) table-danger @else table-info @endif">
                                                                    <td>
                                                                        {{ \Carbon\Carbon::parse($mouvement->date_mouvement)->format('d/m/Y H:i') }}

                                                                    </td>
                                                                    <td>
                                                                        <strong>{{ $mouvement->produit->libelle }}</strong>
                                                                        @if($mouvement->produit->code_barre)
                                                                            <br><small class="text-muted">{{ $mouvement->produit->code_barre }}</small>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge bg-secondary">{{ $mouvement->produit->categorie->libelle ?? 'N/A' }}</span>
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge 
                                                                            @if($mouvement->type_mouvement == 'entrée') bg-success
                                                                            @elseif(in_array($mouvement->type_mouvement, ['sortie', 'vente'])) bg-danger
                                                                            @else bg-info @endif">
                                                                            {{ $mouvement->type_mouvement }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <strong class="@if($mouvement->type_mouvement == 'entrée') text-success @else text-danger @endif">
                                                                            {{ $mouvement->quantite }} {{ $mouvement->produit->unite }}
                                                                        </strong>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        {{ number_format($mouvement->produit->prix_unitaire, 0, ',', ' ') }} CDF
                                                                    </td>
                                                                    <td class="text-end">
                                                                        @php
                                                                            $valeur = $mouvement->quantite * $mouvement->produit->prix_unitaire;
                                                                        @endphp
                                                                        <strong class="@if($mouvement->type_mouvement == 'entrée') text-success @else text-danger @endif">
                                                                            {{ number_format($valeur, 0, ',', ' ') }} CDF
                                                                        </strong>
                                                                    </td>
                                                                    <td>
                                                                        <small class="text-muted">{{ $mouvement->numdoc ?? '-' }}</small>
                                                                    </td>
                                                                    <td>
                                                                        <small>{{ $mouvement->user->name ?? 'N/A' }}</small>
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge bg-light text-dark">{{ $mouvement->emplacement->libelle ?? 'N/A' }}</span>
                                                                    </td>
                                                                    <td>
                                                                        @if($mouvement->source && $mouvement->destination)
                                                                            <small>
                                                                                De : {{ $mouvement->prov ? $mouvement->prov->libelle : '—' }}<br>
                                                                                Vers : {{ $mouvement->dest ? $mouvement->dest->libelle : '—' }}
                                                                            </small>
                                                                        @else
                                                                            <span class="text-muted">-</span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <!-- Pagination -->
                                                <div class="d-flex justify-content-center mt-4">
                                                    {{ $mouvements->links() }}
                                                </div>
                                            @else
                                                <div class="alert alert-info text-center">
                                                    <i class="fa fa-info-circle"></i> Aucun mouvement trouvé avec les critères sélectionnés.
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
    const table = document.getElementById('table-mouvements');
    const html = table.outerHTML;
    const url = 'data:application/vnd.ms-excel;charset=utf-8,' + encodeURIComponent(html);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'rapport_mouvements_' + new Date().toISOString().split('T')[0] + '.xls';
    link.click();
}

// Auto-submit when period is selected
document.getElementById('date_debut').addEventListener('change', function() {
    if(this.value && document.getElementById('date_fin').value) {
        this.form.submit();
    }
});

document.getElementById('date_fin').addEventListener('change', function() {
    if(this.value && document.getElementById('date_debut').value) {
        this.form.submit();
    }
});
</script>

<style>
.table-success { background-color: #d4edda !important; }
.table-danger { background-color: #f8d7da !important; }
.table-info { background-color: #d1ecf1 !important; }

.box.bg-primary, .box.bg-success, .box.bg-info, .box.bg-warning, .box.bg-danger {
    border-radius: 8px;
    border: none;
}

.box.bg-primary .box-body, 
.box.bg-success .box-body, 
.box.bg-info .box-body,
.box.bg-warning .box-body,
.box.bg-danger .box-body {
    padding: 15px;
}
</style>
@endpush