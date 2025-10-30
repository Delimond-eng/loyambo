@extends('layouts.admin')

@section('content')
@include('components.alert.sweet-alert-corner')

<div class="content-wrapper">
    <div class="container-full">
        <!-- Content Header -->
        <div class="content-header">
            <div class="d-flex align-items-center">
                <div class="mr-auto">
                    <h4 class="page-title">Modifier la Commande #{{ $facture->numero }}</h4>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="row">
                <!-- Panneau 1: Liste des produits -->
                <div class="col-3">
                    <div class="box" style="background: transparent; box-shadow: none;">
                        <div class="box-body p-0">
                            <!-- Barre de recherche -->
                            <div class="form-group mb-3">
                                <input type="text" class="form-control" id="searchProduit" placeholder="Rechercher un produit...">
                            </div>
                            
                            <!-- Liste des produits -->
                            <div class="produits-container" style="max-height: 500px; overflow-y: auto;">
                                <div class="row" id="produitsList">
                                    @foreach($produits as $produit)
                                    @if(is_object($produit) && $produit->id)
                                    <div class="col-12 mb-2 produit-item" 
                                         data-categorie-id="{{ $produit->categorie->id ?? '' }}"
                                         data-produit-nom="{{ strtolower($produit->libelle) }}">
                                        <button type="button" 
                                                class="btn btn-light btn-block produit-btn text-left"
                                                data-produit-id="{{ $produit->id }}"
                                                data-produit-nom="{{ $produit->libelle }}"
                                                data-produit-prix="{{ $produit->prix_unitaire }}"
                                                style="
                                                    border: 2px solid {{ $produit->categorie->couleur ?? '#007bff' }};
                                                    color: #333;
                                                    white-space: normal;
                                                    height: auto;
                                                    padding: 10px;
                                                    font-size: 0.9em;
                                                    background: white;
                                                ">
                                            <strong>{{ $produit->libelle }}</strong>
                                            <br>
                                            <small class="text-success">{{ number_format($produit->prix_unitaire, 0, ',', ' ') }} FC</small>
                                        </button>
                                    </div>
                                    @endif
                                    @endforeach
                                </div>
                            </div>

                            <!-- Filtres par catégorie -->
                            <div class="categories-filtre mt-3">
                                <h6 class="mb-2">Filtrer par catégorie:</h6>
                                <div class="d-flex flex-wrap">
                                    <button type="button" class="btn btn-outline-secondary btn-sm m-1 categorie-btn active" data-categorie="all">
                                        Tous
                                    </button>
                                    @foreach($produits->groupBy('categorie_id') as $categorieId => $produitsCategorie)
                                    @if($produitsCategorie->first()->categorie)
                                    <button type="button" 
                                            class="btn btn-outline-secondary btn-sm m-1 categorie-btn" 
                                            data-categorie="{{ $produitsCategorie->first()->categorie->id }}"
                                            style="border-color: {{ $produitsCategorie->first()->categorie->couleur }}; color: {{ $produitsCategorie->first()->categorie->couleur }};">
                                        {{ $produitsCategorie->first()->categorie->libelle }}
                                    </button>
                                    @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Espace vide -->
                <div class="col-1"></div>

                <!-- Panneau 2: Panier de la commande -->
                <div class="col-3">
                    <div class="box" style="background: white; border: 1px solid #e0e0e0;">
                        <div class="box-header with-border text-center">
                            <h4 class="box-title mb-0">
                                <i class="fa fa-shopping-cart"></i> 
                                <strong>PANIER</strong>
                            </h4>
                        </div>
                        <div class="box-body p-0">
                            <form id="editCommandeForm" method="POST" action="">
                                @csrf
                                @method('PUT')
                                
                                <div id="panierContainer" style="max-height: 500px; overflow-y: auto;">
                                    <!-- Les produits du panier seront affichés ici -->
                                </div>

                                <!-- Total général -->
                                <div class="p-3 border-top">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong class="h5 mb-0">Total:</strong>
                                        <strong class="h5 mb-0 text-success" id="totalGeneral">0 FC</strong>
                                    </div>
                                </div>

                                <div class="p-3 border-top">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fa fa-save"></i> Mettre à jour la commande
                                    </button>
                                    <a href="" class="btn btn-secondary btn-block mt-2">
                                        <i class="fa fa-arrow-left"></i> Retour
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
// Variables globales
let panier = [];

// Données des produits existants de la commande
const produitsExistants = [
    @foreach($detailsFactures as $detail)
    @if($detail->produit)
    {
        produit_id: {{ $detail->produit_id }},
        nom: "{{ addslashes($detail->produit->libelle) }}",
        prix: {{ floatval($detail->prix_unitaire) }},
        quantite: {{ intval($detail->quantite) }},
        sous_total: {{ floatval($detail->sous_total) }}
    },
    @endif
    @endforeach
];

// Initialisation du panier avec les produits existants
function initialiserPanier() {
    panier = [...produitsExistants];
    afficherPanier();
    calculerTotalGeneral();
}

// Ajouter un produit au panier
function ajouterProduit(produitId, nom, prix) {
    const produitExistant = panier.find(p => p.produit_id == produitId);
    
    if (produitExistant) {
        // Si le produit existe déjà, augmenter la quantité
        produitExistant.quantite += 1;
        produitExistant.sous_total = produitExistant.prix * produitExistant.quantite;
    } else {
        // Sinon, ajouter un nouveau produit
        panier.push({
            produit_id: produitId,
            nom: nom,
            prix: prix,
            quantite: 1,
            sous_total: prix
        });
    }
    
    afficherPanier();
    calculerTotalGeneral();
}

// Supprimer un produit du panier
function supprimerProduit(index) {
    panier.splice(index, 1);
    afficherPanier();
    calculerTotalGeneral();
}

// Mettre à jour la quantité d'un produit
function mettreAJourQuantite(index, nouvelleQuantite) {
    if (nouvelleQuantite < 1) {
        supprimerProduit(index);
        return;
    }
    
    panier[index].quantite = nouvelleQuantite;
    panier[index].sous_total = panier[index].prix * nouvelleQuantite;
    
    afficherPanier();
    calculerTotalGeneral();
}

// Afficher le panier
function afficherPanier() {
    const container = document.getElementById('panierContainer');
    
    if (panier.length === 0) {
        container.innerHTML = `
            <div class="text-center p-4">
                <i class="fa fa-shopping-cart fa-3x text-muted mb-3"></i>
                <p class="text-muted">Panier vide</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    panier.forEach((produit, index) => {
        html += `
            <div class="panier-item border-bottom p-3">
                <div class="d-flex align-items-start">
                    <i class="fa fa-tag text-primary mt-1 mr-2"></i>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">${produit.nom}</h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">${produit.prix.toLocaleString()} FC × ${produit.quantite}</small>
                            <strong class="text-success">${produit.sous_total.toLocaleString()} FC</strong>
                        </div>
                        <div class="d-flex align-items-center mt-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="mettreAJourQuantite(${index}, ${produit.quantite - 1})">
                                <i class="fa fa-minus"></i>
                            </button>
                            <input type="number" 
                                   class="form-control form-control-sm mx-2 text-center" 
                                   value="${produit.quantite}" 
                                   min="1" 
                                   style="width: 60px;"
                                   onchange="mettreAJourQuantite(${index}, parseInt(this.value))">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="mettreAJourQuantite(${index}, ${produit.quantite + 1})">
                                <i class="fa fa-plus"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm ml-2" onclick="supprimerProduit(${index})">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Calculer le total général
function calculerTotalGeneral() {
    const total = panier.reduce((sum, produit) => sum + produit.sous_total, 0);
    document.getElementById('totalGeneral').textContent = total.toLocaleString() + ' FC';
}

// Filtrer les produits par recherche
function filtrerProduits(recherche) {
    const produitsItems = document.querySelectorAll('.produit-item');
    const searchTerm = recherche.toLowerCase().trim();
    
    produitsItems.forEach(item => {
        const produitNom = item.getAttribute('data-produit-nom');
        if (produitNom.includes(searchTerm) || searchTerm === '') {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

// Filtrer les produits par catégorie
function filtrerParCategorie(categorieId) {
    const produitsItems = document.querySelectorAll('.produit-item');
    
    produitsItems.forEach(item => {
        const itemCategorieId = item.getAttribute('data-categorie-id');
        
        if (categorieId === 'all' || itemCategorieId == categorieId) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

// Préparer le formulaire avant soumission
function preparerFormulaire() {
    const form = document.getElementById('editCommandeForm');
    const hiddenInputs = form.querySelectorAll('input[type="hidden"]');
    hiddenInputs.forEach(input => input.remove());
    
    panier.forEach((produit, index) => {
        const inputId = document.createElement('input');
        inputId.type = 'hidden';
        inputId.name = `produits[${index}][id]`;
        inputId.value = produit.produit_id;
        form.appendChild(inputId);
        
        const inputPrix = document.createElement('input');
        inputPrix.type = 'hidden';
        inputPrix.name = `produits[${index}][prix]`;
        inputPrix.value = produit.prix;
        form.appendChild(inputPrix);
        
        const inputQuantite = document.createElement('input');
        inputQuantite.type = 'hidden';
        inputQuantite.name = `produits[${index}][quantite]`;
        inputQuantite.value = produit.quantite;
        form.appendChild(inputQuantite);
    });
}

// Événements
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser le panier
    initialiserPanier();
    
    // Événement recherche
    document.getElementById('searchProduit').addEventListener('input', function(e) {
        filtrerProduits(e.target.value);
    });
    
    // Événements boutons catégories
    document.querySelectorAll('.categorie-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // Retirer la classe active de tous les boutons
            document.querySelectorAll('.categorie-btn').forEach(b => {
                b.classList.remove('active');
            });
            // Ajouter la classe active au bouton cliqué
            this.classList.add('active');
            
            const categorieId = this.getAttribute('data-categorie');
            filtrerParCategorie(categorieId);
        });
    });
    
    // Événements boutons produits
    document.querySelectorAll('.produit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const produitId = this.getAttribute('data-produit-id');
            const nom = this.getAttribute('data-produit-nom');
            const prix = parseFloat(this.getAttribute('data-produit-prix'));
            
            ajouterProduit(produitId, nom, prix);
        });
    });
    
    // Validation du formulaire
    document.getElementById('editCommandeForm').addEventListener('submit', function(e) {
        if (panier.length === 0) {
            e.preventDefault();
            alert('Veuillez ajouter au moins un produit au panier.');
            return;
        }
        
        preparerFormulaire();
    });
});
</script>

<style>
.produits-container {
    scrollbar-width: thin;
    scrollbar-color: #007bff #f8f9fa;
}

.produits-container::-webkit-scrollbar {
    width: 6px;
}

.produits-container::-webkit-scrollbar-track {
    background: #f8f9fa;
    border-radius: 3px;
}

.produits-container::-webkit-scrollbar-thumb {
    background: #007bff;
    border-radius: 3px;
}

.produit-btn {
    transition: all 0.3s ease;
    border-radius: 8px;
}

.produit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    background: #f8f9fa !important;
}

.categories-filtre {
    background: white;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
}

.categorie-btn.active {
    background-color: #007bff !important;
    color: white !important;
}

#panierContainer {
    scrollbar-width: thin;
    scrollbar-color: #28a745 #f8f9fa;
}

#panierContainer::-webkit-scrollbar {
    width: 6px;
}

#panierContainer::-webkit-scrollbar-track {
    background: #f8f9fa;
    border-radius: 3px;
}

#panierContainer::-webkit-scrollbar-thumb {
    background: #28a745;
    border-radius: 3px;
}

.panier-item {
    transition: background-color 0.2s ease;
}

.panier-item:hover {
    background-color: #f8f9fa;
}

.btn-outline-secondary, .btn-outline-danger {
    border-width: 1px;
}

.form-control-sm {
    height: 30px;
}
</style>
@endpush