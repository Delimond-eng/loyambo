@extends('layouts.admin')

@section('content')
<div class="content-wrapper">
    <div class="container-full">
        <div class="content-header">
            <div class="d-flex align-items-center">
                <div class="mr-auto">
                    <h3 class="page-title">Nouvel Inventaire</h3>
                    <div class="d-inline-block align-items-center">
                        <nav>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#"><i class="mdi mdi-home-outline"></i></a></li>
                                <li class="breadcrumb-item" aria-current="page">Inventaires</li>
                                <li class="breadcrumb-item active" aria-current="page">Nouvel Inventaire</li>
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
                        <div class="box-header with-border text-center p-5">
                            <h4 class="box-title p-5">Saisie de l'inventaire</h4>
                        </div>
                        <form action="{{ route('inventaire.store') }}" method="POST" id="form-inventaire">
                            @csrf
                            <div class="box-body">
                                <!-- Date de l'inventaire -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="date_inventaire">Date de l'inventaire *</label>
                                            <input type="date" 
                                                   class="form-control" 
                                                   id="date_inventaire" 
                                                   name="date_inventaire" 
                                                   value="{{ old('date_inventaire', date('Y-m-d')) }}"
                                                   required>
                                            @error('date_inventaire')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Liste des produits -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th width="5%">#</th>
                                                        <th width="20%">Produit</th>
                                                        <th width="10%">Catégorie</th>
                                                        <th width="10%">Unité</th>
                                                        <th width="15%">Stock Théorique</th>
                                                        <th width="15%">Stock Physique *</th>
                                                        <th width="10%">Écart</th>
                                                        <th width="15%">Observation</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($produits as $index => $produit)
                                                        @php
                                                            $stockTheorique = $produit->stock_theorique;
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $index + 1 }}</td>
                                                            <td>
                                                                <strong>{{ $produit->libelle }}</strong>
                                                                @if($produit->reference)
                                                                    <br><small class="text-muted">Ref: {{ $produit->reference }}</small>
                                                                @endif
                                                            </td>
                                                            <td>{{ $produit->categorie->libelle ?? '-' }}</td>
                                                            <td>{{ $produit->unite }}</td>
                                                            <td>
                                                                <span class="badge badge-info">{{ $stockTheorique }}</span>
                                                            </td>
                                                            <td>
                                                                <input type="number" 
                                                                       name="produits[{{ $produit->id }}][quantite_physique]" 
                                                                       class="form-control stock-physique" 
                                                                       value="{{ old('produits.'.$produit->id.'.quantite_physique', $stockTheorique) }}"
                                                                       min="0"
                                                                       data-theorique="{{ $stockTheorique }}"
                                                                       onchange="calculerEcart(this)">
                                                            </td>
                                                            <td>
                                                                <span class="ecart-display badge" id="ecart-{{ $produit->id }}">0</span>
                                                            </td>
                                                            <td>
                                                                <input type="text" 
                                                                       name="produits[{{ $produit->id }}][observation]" 
                                                                       class="form-control" 
                                                                       value="{{ old('produits.'.$produit->id.'.observation') }}"
                                                                       placeholder="Observation...">
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="box-footer">
                                <div class="row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary btn-rounded">
                                            <i class="fa fa-save"></i> Enregistrer l'Inventaire
                                        </button>
                                        <a href="{{ route('reports.inventaires') }}" class="btn btn-secondary btn-rounded">
                                            <i class="fa fa-times"></i> Annuler
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
function calculerEcart(input) {
    const produitId = input.name.match(/\[(\d+)\]/)[1];
    const stockPhysique = parseInt(input.value) || 0;
    const stockTheorique = parseInt(input.dataset.theorique) || 0;
    const ecart = stockPhysique - stockTheorique;
    
    const ecartDisplay = document.getElementById('ecart-' + produitId);
    ecartDisplay.textContent = ecart;
    
    // Colorer l'écart
    if (ecart > 0) {
        ecartDisplay.className = 'ecart-display badge badge-success';
    } else if (ecart < 0) {
        ecartDisplay.className = 'ecart-display badge badge-danger';
    } else {
        ecartDisplay.className = 'ecart-display badge badge-info';
    }
}

// Calculer les écarts au chargement
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.stock-physique');
    inputs.forEach(input => {
        calculerEcart(input);
    });
});

// Validation du formulaire
document.getElementById('form-inventaire').addEventListener('submit', function(e) {
    const dateInventaire = document.getElementById('date_inventaire').value;
    if (!dateInventaire) {
        e.preventDefault();
        alert('Veuillez sélectionner une date d\'inventaire');
        return false;
    }
    
    // Vérifier qu'au moins un produit a été saisi
    const produitsSaisis = document.querySelectorAll('.stock-physique');
    let auMoinsUnProduit = false;
    produitsSaisis.forEach(input => {
        if (input.value && parseInt(input.value) >= 0) {
            auMoinsUnProduit = true;
        }
    });
    
    if (!auMoinsUnProduit) {
        e.preventDefault();
        alert('Veuillez saisir au moins un stock physique');
        return false;
    }
});
</script>

<style>
.ecart-display {
    font-size: 0.9em;
    padding: 0.25rem 0.5rem;
}
.table th {
    background-color: #f8f9fa;
    font-weight: 600;
}
.stock-physique {
    min-width: 100px;
}
</style>
@endpush