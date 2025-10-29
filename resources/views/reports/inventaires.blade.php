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
                        <div class="box-header with-border p-5 text-center">
                            <h4 class="box-title">Rapport des Inventaires</h4>
                            <p class="text-muted">Comparaison stock théorique vs physique et analyse des écarts</p>
                        </div>

                        <!-- Filtres -->
                        <div class="box-body">
                            <form method="GET" action="{{ route('reports.inventaires') }}">
                                <div class="row mb-4">
                                    <div class="col-md-3">
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
                                    <div class="col-md-3">
                                        <label for="seuil_ecart">Seuil d'écart</label>
                                        <input type="number" name="seuil_ecart" id="seuil_ecart" class="form-control" 
                                               value="{{ request('seuil_ecart', 0) }}" placeholder="Écart minimum" min="0">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="type_ecart">Type d'écart</label>
                                        <select name="type_ecart" id="type_ecart" class="form-control">
                                            <option value="">Tous les écarts</option>
                                            <option value="negatif" {{ request('type_ecart') == 'negatif' ? 'selected' : '' }}>Déficits seulement</option>
                                            <option value="positif" {{ request('type_ecart') == 'positif' ? 'selected' : '' }}>Surplus seulement</option>
                                            <option value="zero" {{ request('type_ecart') == 'zero' ? 'selected' : '' }}>Sans écart</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
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
                                <!-- Centrer les boutons de filtre -->
                                <div class="row mb-4">
                                    <div class="col-md-12 d-flex justify-content-center">
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fa fa-filter"></i> Appliquer les filtres
                                            </button>
                                            <a href="{{ route('reports.inventaires') }}" class="btn btn-secondary">
                                                <i class="fa fa-refresh"></i> Réinitialiser
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Statistiques -->
                        <div class="box-body">
                            

                            <!-- Tableau des écarts d'inventaire -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="box">
                                        <div class="box-header">
                                            <h4 class="box-title">
                                                Écarts d'Inventaire 
                                                <span class="badge bg-primary">{{ $produitsAvecStock->count() }} produits</span>
                                            </h4>
                                            <div class="box-tools">
                                                <a href="{{ route('inventaires.create') }}" class="btn btn-sm btn-success" >
                                                     Faire l'inventaire
                                                </a>
                                                <a href="{{ route('inventaire.historiques') }}" class="btn btn-sm btn-info" >
                                                    <i class="bi bi-eyes"></i> Voir l'historique
                                                </a>
                                            </div>
                                        </div>
                                        <div class="box-body">
                                            @if($produitsAvecStock->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-hover" id="table-inventaires">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Produit</th>
                                                                <th>Catégorie</th>
                                                                <th>Référence</th>
                                                                <th class="text-center">Stock Théorique</th>
                                                                <th class="text-center">Stock Physique</th>
                                                                <th class="text-center">Écart</th>
                                                                <th class="text-end">Prix Unitaire</th>
                                                                <th class="text-end">Valeur Écart</th>
                                                                <th class="text-end">Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($produitsAvecStock as $index => $produit)
                                                                <tr class="@if($produit->ecart < 0) table-danger @elseif($produit->ecart > 0) table-success @else table-light @endif">
                                                                    <td>{{ $index + 1 }}</td>
                                                                    <td>
                                                                        <strong>{{ $produit->libelle }}</strong>
                                                                        @if($produit->code_barre)
                                                                            <br><small class="text-muted">Code: {{ $produit->code_barre }}</small>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge bg-secondary">{{ $produit->categorie->libelle ?? 'N/A' }}</span>
                                                                    </td>
                                                                    <td>
                                                                        {{ $produit->reference ?? '-' }}
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <span class="badge bg-primary">{{ $produit->stock_theorique }} {{ $produit->unite }}</span>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <span class="badge bg-info">{{ $produit->stock_physique }} {{ $produit->unite }}</span>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <span class="badge @if($produit->ecart < 0) bg-danger @elseif($produit->ecart > 0) bg-success @else bg-secondary @endif">
                                                                            {{ $produit->ecart }} {{ $produit->unite }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        {{ number_format($produit->prix_unitaire, 0, ',', ' ') }} CDF
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <strong class="@if($produit->valeur_ecart < 0) text-danger @elseif($produit->valeur_ecart > 0) text-success @else text-muted @endif">
                                                                            {{ number_format($produit->valeur_ecart, 0, ',', ' ') }} CDF
                                                                        </strong>
                                                                    </td>
                                                                    <td>
                                                                      @if ($produit->ecart != 0)
                                                                          @php
                                                                              $btnClass = $produit->ecart > 0 ? 'btn-success' : 'btn-warning';
                                                                              $btnText = $produit->ecart > 0 ? 'Ajuster (+) ' : 'Ajuster (-) ';
                                                                          @endphp
                                                                          <a href="{{ route('inventaire.reajuster', $produit->id) }}" 
                                                                             class="btn {{ $btnClass }} btn-sm" 
                                                                             title="Réajuster le stock de {{ abs($produit->ecart) }} unités">
                                                                              <i class="fa fa-adjust"></i> {{ $btnText }}
                                                                          </a>
                                                                      @else
                                                                          <span class="badge badge-success">Conforme</span>
                                                                      @endif
                                                                  </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                        <tfoot>
                                                            <tr class="bg-light">
                                                                <td colspan="8" class="text-end"><strong>Solde total des écarts:</strong></td>
                                                                <td class="text-end">
                                                                    @php
                                                                        $soldeTotal = $stats['valeur_total_surplus'] - $stats['valeur_total_perte'];
                                                                    @endphp
                                                                    <strong class="@if($soldeTotal < 0) text-danger @elseif($soldeTotal > 0) text-success @else text-muted @endif">
                                                                        {{ number_format($soldeTotal, 0, ',', ' ') }} CDF
                                                                    </strong>
                                                                </td>
                                                               
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="alert alert-warning text-center">
                                                    <i class="fa fa-exclamation-triangle"></i> Aucun produit trouvé avec les critères de filtrage sélectionnés.
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
        <!-- /.content -->
    </div>
</div>
<!-- /.content-wrapper -->
@endsection

@push('scripts')
<script>
function exporterExcel() {
    // Implémentation basique de l'export Excel
    const table = document.getElementById('table-inventaires');
    const html = table.outerHTML;
    const url = 'data:application/vnd.ms-excel;charset=utf-8,' + encodeURIComponent(html);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'rapport_inventaires_' + new Date().toISOString().split('T')[0] + '.xls';
    link.click();
}

function imprimerRapport() {
    window.print();
}

document.addEventListener('DOMContentLoaded', function() {
    // Gestion de l'historique des mouvements
    document.querySelectorAll('.btn-details').forEach(button => {
        button.addEventListener('click', function() {
            const produitId = this.getAttribute('data-produit-id');
            const produitLibelle = this.getAttribute('data-produit-libelle');
            chargerHistoriqueProduit(produitId, produitLibelle);
        });
    });

    // Gestion des ajustements
    document.querySelectorAll('.btn-ajuster').forEach(button => {
        button.addEventListener('click', function() {
            const produitId = this.getAttribute('data-produit-id');
            const produitLibelle = this.getAttribute('data-produit-libelle');
            const ecart = this.getAttribute('data-ecart');
            ajusterInventaire(produitId, produitLibelle, ecart);
        });
    });

    function chargerHistoriqueProduit(produitId, produitLibelle) {
        // Implémentation pour charger l'historique détaillé des mouvements
        alert('Historique des mouvements pour: ' + produitLibelle + '\n(ID: ' + produitId + ')');
        // Ici vous pouvez faire un appel AJAX pour récupérer l'historique complet
    }

    function ajusterInventaire(produitId, produitLibelle, ecart) {
        const quantite = Math.abs(ecart);
        const type = ecart > 0 ? 'inventaire_plus' : 'inventaire_moins';
        const message = ecart > 0 ? 
            `Ajustement positif: ${quantite} unités à ajouter pour ${produitLibelle}` :
            `Ajustement négatif: ${quantite} unités à retirer pour ${produitLibelle}`;
        
        if (confirm(message + '\n\nConfirmer l\'ajustement?')) {
            // Ici vous pouvez faire un appel AJAX pour enregistrer l'ajustement
            alert('Ajustement enregistré pour: ' + produitLibelle);
        }
    }
});
</script>

<style>
@media print {
    .box-tools, .btn, .content-header, .box-header .box-title .badge {
        display: none !important;
    }
    
    .box {
        border: none !important;
        box-shadow: none !important;
    }
}

.box.bg-primary, .box.bg-success, .box.bg-info, .box.bg-warning, .box.bg-secondary, .box.bg-danger {
    border-radius: 8px;
    border: none;
}

.box.bg-primary .box-body, 
.box.bg-success .box-body, 
.box.bg-info .box-body,
.box.bg-warning .box-body,
.box.bg-secondary .box-body,
.box.bg-danger .box-body {
    padding: 15px;
}

.badge {
    font-size: 12px;
    padding: 6px 10px;
}

.table-responsive {
    border-radius: 8px;
    overflow: hidden;
}

.card {
    border-radius: 8px;
}

.card-header {
    border-radius: 8px 8px 0 0 !important;
}

/* Centrage amélioré pour les boutons de filtre */
.d-flex.justify-content-center .text-center {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: center;
}

.table-danger {
    background-color: #f8d7da !important;
}

.table-success {
    background-color: #d1edff !important;
}

.table-light {
    background-color: #f8f9fa !important;
}
</style>
@endpush