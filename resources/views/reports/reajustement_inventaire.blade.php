@extends('layouts.admin')

@section('content')
<div class="content-wrapper">
    <div class="container-full">
        <div class="content-header">
            <div class="d-flex align-items-center">
                <div class="mr-auto">
                    <h3 class="page-title">Réajustement d'Inventaire</h3>
                    <div class="d-inline-block align-items-center">
                        <nav>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#"><i class="mdi mdi-home-outline"></i></a></li>
                                <li class="breadcrumb-item"><a href="{{ route('inventaire.historiques') }}">Inventaires</a></li>
                                <li class="breadcrumb-item active">Réajustement</li>
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
                        <div class="box-header with-border">
                            <h4 class="box-title">Confirmer le réajustement</h4>
                        </div>
                        <div class="box-body">
                            <!-- Informations sur l'inventaire -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h5>Détails de l'inventaire</h5>
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Produit:</th>
                                            <td>{{ $inventaire->produit->libelle }}</td>
                                        </tr>
                                        <tr>
                                            <th>Référence:</th>
                                            <td>{{ $inventaire->produit->reference ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Date inventaire:</th>
                                            <td>{{ $inventaire->date_inventaire->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Stock théorique:</th>
                                            <td><span class="badge badge-info">{{ $inventaire->quantite_theorique }}</span></td>
                                        </tr>
                                        <tr>
                                            <th>Stock physique:</th>
                                            <td><span class="badge badge-primary">{{ $inventaire->quantite_physique }}</span></td>
                                        </tr>
                                        <tr>
                                            <th>Écart:</th>
                                            <td>
                                                <span class="badge badge-{{ $inventaire->ecart > 0 ? 'success' : 'danger' }}">
                                                    {{ $inventaire->ecart > 0 ? '+' : '' }}{{ $inventaire->ecart }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h5>Action de réajustement</h5>
                                    <div class="alert alert-{{ $inventaire->ecart > 0 ? 'success' : 'warning' }}">
                                        <h4>
                                            <i class="fa fa-{{ $inventaire->ecart > 0 ? 'plus' : 'minus' }}"></i>
                                            {{ $inventaire->ecart > 0 ? 'Ajout au stock' : 'Retrait du stock' }}
                                        </h4>
                                        <p class="mb-1">
                                            <strong>Quantité:</strong> {{ abs($inventaire->ecart) }} unités
                                        </p>
                                        <p class="mb-1">
                                            <strong>Type de mouvement:</strong> 
                                            {{ $inventaire->ecart > 0 ? 'Entrée' : 'Sortie' }}
                                        </p>
                                        <p class="mb-0">
                                            <strong>Valeur:</strong> 
                                            {{ number_format(abs($inventaire->ecart) * $inventaire->produit->prix_unitaire, 0, ',', ' ') }} CDF
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Formulaire de confirmation -->
                            <form method="POST" action="{{ route('inventaire.process-reajustement', $inventaire->id) }}">
                                @csrf
                                
                                <div class="form-group">
                                    <label for="observation">Observation (optionnel)</label>
                                    <textarea name="observation" class="form-control" rows="3" 
                                              placeholder="Note concernant ce réajustement...">{{ old('observation') }}</textarea>
                                </div>

                                <div class="form-group">
                                    <div class="checkbox">
                                        <input type="checkbox" name="confirmation" id="confirmation" value="1" required>
                                        <label for="confirmation">
                                            Je confirme le {{ $inventaire->ecart > 0 ? 'ajout' : 'retrait' }} de 
                                            <strong>{{ abs($inventaire->ecart) }} unités</strong> au stock du produit 
                                            <strong>"{{ $inventaire->produit->libelle }}"</strong>
                                        </label>
                                    </div>
                                    @error('confirmation')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-{{ $inventaire->ecart > 0 ? 'success' : 'warning' }}">
                                        <i class="fa fa-check"></i> 
                                        Confirmer le Réajustement
                                    </button>
                                    <a href="{{ route('inventaire.historiques') }}" class="btn btn-secondary">
                                        <i class="fa fa-times"></i> Annuler
                                    </a>
                                </div>
                            </form>
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
    // Validation côté client
    const form = document.querySelector('form');
    const checkbox = document.getElementById('confirmation');
    
    form.addEventListener('submit', function(e) {
        if (!checkbox.checked) {
            e.preventDefault();
            alert('Veuillez confirmer le réajustement en cochant la case de confirmation.');
            return false;
        }
    });
});
</script>
@endpush