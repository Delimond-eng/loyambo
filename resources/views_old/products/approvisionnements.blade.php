@extends("layouts.admin")

@section("content")
<div class="content-wrapper">
    <div class="container-full">
        <div class="content-header">
            <div class="d-flex align-items-center justify-content-between">
                <div class="me-auto">
                    <h3 class="page-title">Approvisionnement Stock</h3>
                </div>
            </div>
        </div>

        <section class="content">
            <!-- Messages de session -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                {{ session('error') }}
            </div>
            @endif

            @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show">
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                {{ session('info') }}
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="row">
                <!-- Panneau Produits -->
                <div class="col-xl-8">
                    <div class="box">
                        <div class="box-body p-0">
                            <!-- Barre de recherche -->
                            <div class="p-3 border-bottom">
                                <div class="input-group" style="max-width: 200px;">
                                    <span class="input-group-text bg-transparent border-end-0">
                                        <i class="mdi mdi-magnify"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0" 
                                           placeholder="Rechercher produit..." id="searchProduct">
                                </div>
                            </div>

                            <!-- Filtre Catégories -->
                            <div class="p-3 border-bottom">
                                <div class="d-flex flex-wrap gap-2">
                                    <button class="btn btn-sm category-filter active" data-category="">
                                        Tous
                                    </button>
                                    @foreach($categories as $categorie)
                                    @php
                                        $textColor = Illuminate\Support\Str::is(['#ffffff', '#fff', '#FFFFFF', '#FFF'], $categorie->couleur) ? 'text-dark' : 'text-white';
                                    @endphp
                                    <button class="btn btn-sm category-filter" 
                                            data-category="{{ $categorie->id }}"
                                            style="background-color: {{ $categorie->couleur }}; border-color: {{ $categorie->couleur }}"
                                            class="{{ $textColor }}">
                                        {{ $categorie->libelle }}
                                    </button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Liste des Produits -->
                            <div class="p-3">
                                <div class="row" id="productsContainer">
                                    @foreach($produits as $produit)
                                    @php
                                        $stock = $produit->qte_init;
                                        foreach($produit->stocks as $mouvement) {
                                            if(in_array($mouvement->type_mouvement, ['entree', 'inventaire_plus', 'ajustement_plus'])) {
                                                $stock += $mouvement->quantite;
                                            } else if(in_array($mouvement->type_mouvement, ['sortie', 'inventaire_moins', 'ajustement_moins', 'vente'])) {
                                                $stock -= $mouvement->quantite;
                                            }
                                        }
                                        $stock = max(0, $stock);
                                        $categorieCouleur = $produit->categorie->couleur ?? '#007bff';
                                    @endphp
                                    <div class="col-md-6 col-lg-4 mb-3 product-item" 
                                         data-id="{{ $produit->id }}"
                                         data-name="{{ strtolower($produit->libelle) }}"
                                         data-category="{{ $produit->categorie_id }}">
                                        <div class="card border-2" style="border-color: {{ $categorieCouleur }} !important;">
                                            <div class="card-body text-center p-3">
                                                <h6 class="card-title mb-2">{{ $produit->libelle }}</h6>
                                                <button class="btn btn-primary btn-sm add-product" 
                                                        data-id="{{ $produit->id }}"
                                                        data-name="{{ $produit->libelle }}"
                                                        data-prix="{{ $produit->prix_unitaire }}">
                                                    Ajouter
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                <!-- Message aucun produit -->
                                <div id="noProducts" class="text-center py-4" style="display: none;">
                                    <p class="text-muted">Aucun produit trouvé</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panneau Approvisionnement -->
                <div class="col-xl-4">
                    <div class="box">
                        <div class="box-header">
                            <h4 class="box-title">Approvisionnement</h4>
                        </div>
                        <div class="box-body">
                            <form id="approForm" action="{{ route('approvisionnement.store') }}" method="POST">
                                @csrf
                                
                                <div id="emptyAppro" class="text-center py-4">
                                    <p class="text-muted">Aucun produit sélectionné</p>
                                </div>

                                <div id="approList" style="display: none;">
                                    <div id="approItems" class="mb-3"></div>
                                    
                                    <button type="submit" class="btn btn-success w-100">
                                        Valider l'approvisionnement
                                    </button>
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

@push('styles')
<style>
.category-filter {
    transition: all 0.3s ease;
    border: 1px solid !important;
}
.category-filter.active {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.product-item .card {
    transition: all 0.3s ease;
}
.product-item .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.appro-item {
    padding: 10px;
    border-radius: 5px;
    background: #f8f9fa;
    margin-bottom: 10px;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let selectedProducts = [];
    
    // Filtrage instantané
    const searchInput = document.getElementById('searchProduct');
    const categoryFilters = document.querySelectorAll('.category-filter');
    
    searchInput.addEventListener('input', filterProducts);
    
    categoryFilters.forEach(btn => {
        btn.addEventListener('click', function() {
            categoryFilters.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            filterProducts();
        });
    });
    
    function filterProducts() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedCategory = document.querySelector('.category-filter.active').dataset.category;
        
        let visibleCount = 0;
        
        document.querySelectorAll('.product-item').forEach(item => {
            const productName = item.dataset.name;
            const productCategory = item.dataset.category;
            
            const matchSearch = productName.includes(searchTerm);
            const matchCategory = !selectedCategory || productCategory === selectedCategory;
            
            if (matchSearch && matchCategory) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });
        
        // Afficher message si aucun produit
        const noProducts = document.getElementById('noProducts');
        noProducts.style.display = visibleCount === 0 ? 'block' : 'none';
    }
    
    // Ajouter produit à l'approvisionnement
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-product')) {
            const productId = e.target.dataset.id;
            const productName = e.target.dataset.name;
            const productPrix = e.target.dataset.prix;
            
            // Vérifier si déjà ajouté
            if (selectedProducts.find(p => p.id === productId)) {
                return;
            }
            
            selectedProducts.push({
                id: productId,
                name: productName,
                prix: productPrix
            });
            
            updateApproList();
        }
    });
    
    // Mettre à jour la liste d'approvisionnement
    function updateApproList() {
        const emptyAppro = document.getElementById('emptyAppro');
        const approList = document.getElementById('approList');
        const approItems = document.getElementById('approItems');
        
        if (selectedProducts.length === 0) {
            emptyAppro.style.display = 'block';
            approList.style.display = 'none';
            return;
        }
        
        emptyAppro.style.display = 'none';
        approList.style.display = 'block';
        
        approItems.innerHTML = '';
        
        selectedProducts.forEach((product, index) => {
            const itemDiv = document.createElement('div');
            itemDiv.className = 'appro-item';
            itemDiv.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">${product.name}</h6>
                    <button type="button" class="btn btn-danger btn-sm remove-product" data-index="${index}">
                        <i class="mdi mdi-close"></i>
                    </button>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <input type="number" name="items[${index}][quantite]" 
                               class="form-control form-control-sm" 
                               placeholder="Quantité" min="1" value="1" required>
                    </div>
                    <div class="col-6">
                        <input type="number" name="items[${index}][prix_unitaire]" 
                               class="form-control form-control-sm" 
                               placeholder="Prix" min="0" step="0.01" 
                               value="${product.prix}" required>
                    </div>
                </div>
                <input type="hidden" name="items[${index}][produit_id]" value="${product.id}">
            `;
            approItems.appendChild(itemDiv);
        });
    }
    
    // Supprimer produit de l'approvisionnement
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-product') || e.target.closest('.remove-product')) {
            const button = e.target.classList.contains('remove-product') ? e.target : e.target.closest('.remove-product');
            const index = parseInt(button.dataset.index);
            
            selectedProducts.splice(index, 1);
            updateApproList();
        }
    });
    
    // Gestion du formulaire
    document.getElementById('approForm').addEventListener('submit', function(e) {
        // La validation se fera côté serveur et les messages seront affichés via les sessions
        if (selectedProducts.length === 0) {
            e.preventDefault();
            alert('Veuillez ajouter au moins un produit');
            return;
        }
    });
});
</script>
@endpush