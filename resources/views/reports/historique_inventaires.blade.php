@extends('layouts.admin')

@section('content')
<div class="content-wrapper">
    <div class="container-full">
        <div class="content-header">
            <div class="d-flex align-items-center">
                <div class="mr-auto">
                    <h3 class="page-title">Historique des Inventaires</h3>
                    <div class="d-inline-block align-items-center">
                        <nav>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#"><i class="mdi mdi-home-outline"></i></a></li>
                                <li class="breadcrumb-item" aria-current="page">Inventaires</li>
                                <li class="breadcrumb-item active" aria-current="page">Historique</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="row">
                <div class="col-12">
                    <div class="box">
                        
                        <div class="box-body">
                            <form method="GET" action="{{ route('inventaire.historiques') }}">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Produit</label>
                                            <select name="produit_id" class="form-control">
                                                <option value="">Tous les produits</option>
                                                @foreach($produits as $produit)
                                                    <option value="{{ $produit->id }}" {{ request('produit_id') == $produit->id ? 'selected' : '' }}>
                                                        {{ $produit->libelle }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Catégorie</label>
                                            <select name="categorie_id" class="form-control">
                                                <option value="">Toutes les catégories</option>
                                                @foreach($categories as $categorie)
                                                    <option value="{{ $categorie->id }}" {{ request('categorie_id') == $categorie->id ? 'selected' : '' }}>
                                                        {{ $categorie->libelle }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Type d'écart</label>
                                            <select name="type_ecart" class="form-control">
                                                <option value="">Tous les écarts</option>
                                                <option value="negatif" {{ request('type_ecart') == 'negatif' ? 'selected' : '' }}>Écarts négatifs</option>
                                                <option value="positif" {{ request('type_ecart') == 'positif' ? 'selected' : '' }}>Écarts positifs</option>
                                                <option value="zero" {{ request('type_ecart') == 'zero' ? 'selected' : '' }}>Sans écart</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Date début</label>
                                            <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Date fin</label>
                                            <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="submit" class="btn btn-primary btn-block">Filtrer</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Statistiques -->
                    <div class="row">
                        <div class="col-xl-3 col-12">
                            <div class="box bg-primary">
                                <div class="box-body">
                                    <div class="d-flex align-items-center">
                                        <div class="me-15">
                                            <i class="fa fa-list-alt fa-2x"></i>
                                        </div>
                                        <div>
                                            <h2 class="my-0 text-white">{{ $stats['total_inventaires'] }}</h2>
                                            <p class="mb-0 text-white-50">Total inventaires</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-12">
                            <div class="box bg-danger">
                                <div class="box-body">
                                    <div class="d-flex align-items-center">
                                        <div class="me-15">
                                            <i class="fa fa-arrow-down fa-2x"></i>
                                        </div>
                                        <div>
                                            <h2 class="my-0 text-white">{{ $stats['total_ecarts_negatifs'] }}</h2>
                                            <p class="mb-0 text-white-50">Écarts négatifs</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-12">
                            <div class="box bg-success">
                                <div class="box-body">
                                    <div class="d-flex align-items-center">
                                        <div class="me-15">
                                            <i class="fa fa-arrow-up fa-2x"></i>
                                        </div>
                                        <div>
                                            <h2 class="my-0 text-white">{{ $stats['total_ecarts_positifs'] }}</h2>
                                            <p class="mb-0 text-white-50">Écarts positifs</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-12">
                            <div class="box bg-info">
                                <div class="box-body">
                                    <div class="d-flex align-items-center">
                                        <div class="me-15">
                                            <i class="fa fa-cube fa-2x"></i>
                                        </div>
                                        <div>
                                            <h2 class="my-0 text-white">{{ $stats['produits_inventories'] }}</h2>
                                            <p class="mb-0 text-white-50">Produits inventoriés</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Liste des inventaires -->
                    <div class="box">
                        <div class="box-header with-border text-center p-5">
                            <h4 class="box-title p-5">Détail des inventaires</h4>
                        </div>
                        <div class="box-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Produit</th>
                                            <th>Catégorie</th>
                                            <th>Stock Théorique</th>
                                            <th>Stock Physique</th>
                                            <th>Écart</th>
                                            <th>Valeur Écart</th>
                                            <th>Utilisateur</th>
                                            <th>Observation</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($inventaires as $inventaire)
                                            @php
                                                $ecartColor = $inventaire->ecart > 0 ? 'success' : ($inventaire->ecart < 0 ? 'danger' : 'info');
                                                $valeurEcart = $inventaire->ecart * ($inventaire->produit->prix_unitaire ?? 0);
                                            @endphp
                                            <tr>
                                                <td class="text-nowrap">
                                                   {{ \Carbon\Carbon::parse($inventaire->date_inventaire)->format('Y-m-d') }}
                                                    <br>
                                                    <small class="text-muted">{{ $inventaire->created_at->format('H:i') }}</small>
                                                </td>
                                                <td>
                                                    <strong>{{ $inventaire->produit->libelle }}</strong>
                                                    @if($inventaire->produit->reference)
                                                        <br><small class="text-muted">Ref: {{ $inventaire->produit->reference }}</small>
                                                    @endif
                                                </td>
                                                <td>{{ $inventaire->produit->categorie->libelle ?? '-' }}</td>
                                                <td class="text-center">
                                                    <span class="badge badge-info">{{ $inventaire->quantite_theorique }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-primary">{{ $inventaire->quantite_physique }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-{{ $ecartColor }}">
                                                        {{ $inventaire->ecart > 0 ? '+' : '' }}{{ $inventaire->ecart }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-{{ $ecartColor }}">
                                                        {{ number_format($valeurEcart, 0, ',', ' ') }} CDF
                                                    </span>
                                                </td>
                                                <td>{{ $inventaire->user->name ?? 'N/A' }}</td>
                                                <td>{{ $inventaire->observation ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="fa fa-clipboard-list fa-2x mb-2"></i>
                                                        <p>Aucun inventaire trouvé</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-center mt-3">
                                {{ $inventaires->links() }}
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
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<style>
.table th {
    background-color: #f8f9fa;
    font-weight: 600;
}
.badge {
    font-size: 0.85em;
    padding: 0.4em 0.6em;
}
</style>
@endpush