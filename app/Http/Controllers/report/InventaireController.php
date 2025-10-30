<?php

namespace App\Http\Controllers\report;

use DB;
use App\Models\Produit;
use App\Models\Categorie;
use App\Models\Inventaire;
use App\Models\Emplacement;
use Illuminate\Http\Request;
use App\Models\MouvementStock;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class InventaireController extends Controller
{
    /**
 * Afficher le formulaire de réajustement
 */
public function showReajustement(Inventaire $inventaire)
{
    // Vérifier qu'il y a bien un écart à ajuster
    if ($inventaire->ecart == 0) {
        return redirect()->route('inventaire.historiques')
            ->with('error', 'Cet inventaire ne nécessite pas de réajustement.');
    }

    $produit = $inventaire->produit;
    
    return view('reports.reajustement_inventaire', compact('inventaire', 'produit'));
}

/**
 * Traiter le réajustement de l'inventaire
 */
public function processReajustement(Request $request, Inventaire $inventaire)
{
    // Validation
    $request->validate([
        'confirmation' => 'required|accepted',
        'observation' => 'nullable|string|max:500',
    ]);

    // Vérifier qu'il y a bien un écart à ajuster
    if ($inventaire->ecart == 0) {
        return redirect()->route('inventaire.historiques')
            ->with('error', 'Cet inventaire ne nécessite pas de réajustement.');
    }

    try {
        DB::beginTransaction();

        $userId = auth()->id();
        $etsId = auth()->user()->ets_id;
        $quantiteAjustement = abs($inventaire->ecart);
        $typeMouvement = $inventaire->ecart > 0 ? 'entrée' : 'sortie';

        // Créer le mouvement de stock pour l'ajustement
        MouvementStock::create([
            'produit_id' => $inventaire->produit_id,
            'type_mouvement' => $typeMouvement,
            'quantite' => $quantiteAjustement,
            'date_mouvement' => now(),
            'user_id' => $userId,
            'ets_id' => $etsId,
            'emplacement_id' => User::find($userId)->emplacement_id, // À adapter selon votre logique
            'numdoc' => time(),
            'source' => 1,
            'destination' => $typeMouvement == 'entrée' ? 'stock' : null,
        ]);

        // Mettre à jour l'inventaire pour marquer qu'il a été réajusté
        $inventaire->update([
            'observation' => $request->observation ?? 'Réajustement effectué le ' . now()->format('d/m/Y H:i'),
        ]);

        DB::commit();

        return redirect()->route('inventaire.historiques')
            ->with('success', 'Réajustement effectué avec succès! Stock ' . 
                   ($inventaire->ecart > 0 ? 'augmenté' : 'réduit') . 
                   ' de ' . $quantiteAjustement . ' unités.');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()
            ->with('error', 'Erreur lors du réajustement: ' . $e->getMessage())
            ->withInput();
    }
}
    public function historiques(Request $request)
{
    // Récupérer les filtres
    $categories = Categorie::all();
    $emplacements = Emplacement::all();
    $produits = Produit::all();

    // Query de base pour les inventaires
    $query = Inventaire::with(['produit', 'produit.categorie', 'user'])
        ->orderBy('date_inventaire', 'desc')
        ->orderBy('created_at', 'desc');

    // Appliquer les filtres
    if ($request->has('produit_id') && $request->produit_id) {
        $query->where('produit_id', $request->produit_id);
    }

    if ($request->has('categorie_id') && $request->categorie_id) {
        $query->whereHas('produit', function($q) use ($request) {
            $q->where('categorie_id', $request->categorie_id);
        });
    }

    if ($request->has('date_debut') && $request->date_debut) {
        $query->whereDate('date_inventaire', '>=', $request->date_debut);
    }

    if ($request->has('date_fin') && $request->date_fin) {
        $query->whereDate('date_inventaire', '<=', $request->date_fin);
    }

    if ($request->has('type_ecart') && $request->type_ecart) {
        if ($request->type_ecart == 'negatif') {
            $query->where('ecart', '<', 0);
        } elseif ($request->type_ecart == 'positif') {
            $query->where('ecart', '>', 0);
        } elseif ($request->type_ecart == 'zero') {
            $query->where('ecart', 0);
        }
    }

    // Récupérer les inventaires avec pagination
    $inventaires = $query->paginate(50);

    // Regrouper par date pour une vue alternative
    $inventairesGroupes = $inventaires->groupBy(function($inventaire) {
       return Carbon::parse($inventaire->date_inventaire)->format('Y-m-d');
    });

    // Statistiques des historiques d'inventaire
    $stats = [
        'total_inventaires' => $inventaires->total(),
        'total_ecarts_negatifs' => $query->clone()->where('ecart', '<', 0)->count(),
        'total_ecarts_positifs' => $query->clone()->where('ecart', '>', 0)->count(),
        'total_sans_ecart' => $query->clone()->where('ecart', 0)->count(),
        'valeur_total_pertes' => $query->clone()->where('ecart', '<', 0)->get()->sum(function($inventaire) {
            return abs($inventaire->ecart * ($inventaire->produit->prix_unitaire ?? 0));
        }),
        'valeur_total_surplus' => $query->clone()->where('ecart', '>', 0)->get()->sum(function($inventaire) {
            return $inventaire->ecart * ($inventaire->produit->prix_unitaire ?? 0);
        }),
        'produits_inventories' => $query->clone()->distinct('produit_id')->count('produit_id'),
    ];

    return view('reports.historique_inventaires', compact(
        'inventaires',
        'inventairesGroupes',
        'stats',
        'categories',
        'emplacements',
        'produits'
    ));
}
    public function create(){
        $produits = Produit::with(['categorie', 'stocks'])->get();
    
        // Calculer le stock théorique pour chaque produit
        $produits = $produits->map(function($produit) {
            $produit->stock_theorique = $this->calculerStockTheorique($produit);
            return $produit;
        });

        return view('reports.create_inventaire', compact('produits'));
    }
   

    public function store(Request $request)
    {
        $request->validate([
            'date_inventaire' => 'required|date',
            'produits' => 'required|array',
            'produits.*.quantite_physique' => 'required|integer|min:0',
            'produits.*.observation' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $dateInventaire = $request->date_inventaire;
            $userId = auth()->id();
            $etsId = auth()->user()->ets_id;

            foreach ($request->produits as $produitId => $data) {
                if (!empty($data['quantite_physique'])) {
                    $produit = Produit::find($produitId);
                    
                    if ($produit) {
                        $stockTheorique = $this->calculerStockTheorique($produit);
                        $quantitePhysique = $data['quantite_physique'];
                        $ecart = $quantitePhysique - $stockTheorique;

                        // Créer l'enregistrement d'inventaire
                        Inventaire::create([
                            'produit_id' => $produitId,
                            'ets_id' => $etsId,
                            'quantite_physique' => $quantitePhysique,
                            'quantite_theorique' => $stockTheorique,
                            'ecart' => $ecart,
                            'observation' => $data['observation'] ?? null,
                            'date_inventaire' => $dateInventaire,
                            'user_id' => $userId,
                        ]);

                        // Créer un mouvement de stock pour l'ajustement si nécessaire
                        if ($ecart != 0) {
                            // Utiliser les types de mouvement existants dans l'enum
                            $typeMouvement = $ecart > 0 ? 'entrée' : 'sortie';
                            $quantiteAjustement = abs($ecart);
                            //doit etre un entier
                            $numdoc = intval($produitId) . time();
                            MouvementStock::create([
                                'produit_id' => $produitId,
                                'type_mouvement' => $typeMouvement,
                                'quantite' => $quantiteAjustement,
                                'date_mouvement' => now(),
                                'user_id' => $userId,
                                'ets_id' => $etsId,
                                'emplacement_id' => 1, // À adapter selon votre logique
                                'numdoc' => $numdoc,
                                'source' => '1',
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('reports.inventaires')
                ->with('success', 'Inventaire créé avec succès!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Erreur lors de la création de l\'inventaire: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Calculer le stock théorique d'un produit basé sur ses mouvements
     * Adapté pour le enum: ('entrée', 'sortie', 'transfert', 'vente')
     */
    private function calculerStockTheorique(Produit $produit)
    {
        $stockInitial = $produit->qte_init ?? 0;
        $stockActuel = $stockInitial;

        foreach ($produit->stocks as $mouvement) {
            $quantite = $mouvement->quantite;
            
            switch ($mouvement->type_mouvement) {
                case 'entrée':
                    $stockActuel += $quantite;
                    break;
                case 'sortie':
                case 'vente':
                    $stockActuel -= $quantite;
                    break;
                case 'transfert':
                    // Pour les transferts, on considère que c'est une sortie de l'emplacement actuel
                    // Si vous avez une logique différente pour les transferts, adaptez cette partie
                    $stockActuel -= $quantite;
                    break;
            }
        }

        return max($stockActuel, 0); // Éviter les stocks négatifs
    }

    public function index(Request $request)
    {
        $categories = Categorie::all();
        $emplacements = Emplacement::all();

        // Récupérer les produits avec leurs inventaires
        $produits = Produit::with(['categorie', 'inventaires' => function($query) {
            $query->orderBy('date_inventaire', 'desc');
        }, 'stocks'])->get();

        // Calculer le stock théorique pour chaque produit basé sur les mouvements
        $produitsAvecStock = $produits->map(function($produit) {
            // Stock théorique = somme de tous les mouvements (entrées - sorties)
            $stockTheorique = $this->calculerStockTheorique($produit);
            
            // Récupérer le dernier inventaire physique
            $dernierInventaire = $produit->inventaires->sortByDesc('date_inventaire')->first();
            
            if ($dernierInventaire) {
                $stockPhysique = $dernierInventaire->quantite_physique;
                $ecart = $dernierInventaire->ecart;
                $dateInventaire = $dernierInventaire->date_inventaire;
            } else {
                // Si pas d'inventaire, on utilise le stock théorique comme référence
                $stockPhysique = $stockTheorique;
                $ecart = 0;
                $dateInventaire = null;
            }

            $produit->stock_theorique = $stockTheorique;
            $produit->stock_physique = $stockPhysique;
            $produit->ecart = $ecart;
            $produit->valeur_ecart = $ecart * $produit->prix_unitaire;
            $produit->pourcentage_ecart = $stockTheorique > 0 ? ($ecart / $stockTheorique) * 100 : 0;
            $produit->date_dernier_inventaire = $dateInventaire;
            $produit->dernier_inventaire = $dernierInventaire;

            return $produit;
        });

        // Appliquer les filtres si présents
        if ($request->has('categorie_id') && $request->categorie_id) {
            $produitsAvecStock = $produitsAvecStock->where('categorie_id', $request->categorie_id);
        }

        if ($request->has('seuil_ecart') && $request->seuil_ecart != '') {
            $seuil = abs($request->seuil_ecart);
            $produitsAvecStock = $produitsAvecStock->filter(function($produit) use ($seuil) {
                return abs($produit->ecart) >= $seuil;
            });
        }

        if ($request->has('type_ecart') && $request->type_ecart) {
            if ($request->type_ecart == 'negatif') {
                $produitsAvecStock = $produitsAvecStock->where('ecart', '<', 0);
            } elseif ($request->type_ecart == 'positif') {
                $produitsAvecStock = $produitsAvecStock->where('ecart', '>', 0);
            } elseif ($request->type_ecart == 'zero') {
                $produitsAvecStock = $produitsAvecStock->where('ecart', 0);
            }
        }

        // Filtrer par date d'inventaire
        if ($request->has('date_inventaire') && $request->date_inventaire) {
            $produitsAvecStock = $produitsAvecStock->filter(function($produit) use ($request) {
                return $produit->date_dernier_inventaire && 
                       $produit->date_dernier_inventaire->format('Y-m-d') == $request->date_inventaire;
            });
        }

        // Statistiques générales
        $stats = [
            'total_produits' => $produitsAvecStock->count(),
            'produits_inventories' => $produitsAvecStock->where('date_dernier_inventaire', '!=', null)->count(),
            'produits_non_inventories' => $produitsAvecStock->where('date_dernier_inventaire', null)->count(),
            'produits_avec_ecart' => $produitsAvecStock->where('ecart', '!=', 0)->count(),
            'produits_sans_ecart' => $produitsAvecStock->where('ecart', 0)->count(),
            'valeur_total_perte' => abs($produitsAvecStock->where('ecart', '<', 0)->sum('valeur_ecart')),
            'valeur_total_surplus' => $produitsAvecStock->where('ecart', '>', 0)->sum('valeur_ecart'),
            'total_ecarts_negatifs' => $produitsAvecStock->where('ecart', '<', 0)->count(),
            'total_ecarts_positifs' => $produitsAvecStock->where('ecart', '>', 0)->count(),
            'stock_theorique_total' => $produitsAvecStock->sum('stock_theorique'),
            'stock_physique_total' => $produitsAvecStock->sum('stock_physique'),
            'valeur_stock_theorique' => $produitsAvecStock->sum(function($produit) {
                return $produit->stock_theorique * $produit->prix_unitaire;
            }),
            'valeur_stock_physique' => $produitsAvecStock->sum(function($produit) {
                return $produit->stock_physique * $produit->prix_unitaire;
            }),
        ];

        // Historique des inventaires
        $historiqueInventaires = Inventaire::with(['produit', 'user'])
            ->orderBy('date_inventaire', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function($inventaire) {
                return Carbon::parse($inventaire->date_inventaire)->format('Y-m-d');
            });

        return view('reports.inventaires', compact(
            'produitsAvecStock', 
            'stats', 
            'historiqueInventaires',
            'categories',
            'emplacements'
        ));  
    }

    public function stocks(Request $request)
    {
        // Récupérer les catégories et emplacements pour les filtres
        $categories = Categorie::all();
        $emplacements = Emplacement::all();

        // Récupérer les produits avec leurs mouvements de stock
        $produits = Produit::with(['categorie', 'stocks' => function($query) {
            $query->orderBy('date_mouvement', 'desc');
        }])->get();

        // Calculer le stock actuel pour chaque produit
        $produitsAvecStock = $produits->map(function($produit) {
            // Utiliser la même fonction pour le calcul cohérent
            $stockActuel = $this->calculerStockTheorique($produit);
            
            // Calculer les totaux par type de mouvement
            $entreesTotal = $produit->stocks->where('type_mouvement', 'entrée')->sum('quantite');
            $sortiesTotal = $produit->stocks->where('type_mouvement', 'sortie')->sum('quantite');
            $ventesTotal = $produit->stocks->where('type_mouvement', 'vente')->sum('quantite');
            $transfertsTotal = $produit->stocks->where('type_mouvement', 'transfert')->sum('quantite');

            // Valorisation
            $valeurStock = $stockActuel * $produit->prix_unitaire;

            $produit->stock_initial = $produit->qte_init ?? 0;
            $produit->stock_actuel = $stockActuel;
            $produit->total_entrees = $entreesTotal;
            $produit->total_sorties = $sortiesTotal;
            $produit->total_ventes = $ventesTotal;
            $produit->total_transferts = $transfertsTotal;
            $produit->valeur_stock = $valeurStock;
            
            // Statut du stock
            $seuilAlerte = $produit->seuil_reappro ?? 5;
            if ($stockActuel == 0) {
                $produit->statut_stock = 'rupture';
                $produit->couleur_statut = 'danger';
            } elseif ($stockActuel <= $seuilAlerte) {
                $produit->statut_stock = 'alerte';
                $produit->couleur_statut = 'warning';
            } else {
                $produit->statut_stock = 'normal';
                $produit->couleur_statut = 'success';
            }

            return $produit;
        });

        // Appliquer les filtres
        $produitsFiltres = $produitsAvecStock;
        
        if ($request->has('categorie_id') && $request->categorie_id) {
            $produitsFiltres = $produitsFiltres->where('categorie_id', $request->categorie_id);
        }

        if ($request->has('statut_stock') && $request->statut_stock) {
            $produitsFiltres = $produitsFiltres->where('statut_stock', $request->statut_stock);
        }

        // Statistiques générales
        $stats = [
            'total_produits' => $produitsAvecStock->count(),
            'produits_rupture' => $produitsAvecStock->where('statut_stock', 'rupture')->count(),
            'produits_alerte' => $produitsAvecStock->where('statut_stock', 'alerte')->count(),
            'produits_normal' => $produitsAvecStock->where('statut_stock', 'normal')->count(),
            'valeur_stock_total' => $produitsAvecStock->sum('valeur_stock'),
            'quantite_stock_total' => $produitsAvecStock->sum('stock_actuel'),
            'total_entrees' => $produitsAvecStock->sum('total_entrees'),
            'total_sorties' => $produitsAvecStock->sum('total_sorties'),
            'total_ventes' => $produitsAvecStock->sum('total_ventes'),
            'total_transferts' => $produitsAvecStock->sum('total_transferts'),
        ];

        return view('reports.stocks', compact(
            'produitsFiltres', 
            'stats', 
            'categories',
            'emplacements'
        ));
    }

    public function mouvementstock(Request $request)
    {
        $categories = Categorie::all();
        $emplacements = Emplacement::all();
        $produits = Produit::all();

        // Query de base pour les mouvements
        $query = MouvementStock::with(['produit', 'produit.categorie', 'user', 'emplacement','prov', 'dest'])
            ->orderBy('date_mouvement', 'desc');

        // Appliquer les filtres
        if ($request->has('produit_id') && $request->produit_id) {
            $query->where('produit_id', $request->produit_id);
        }

        if ($request->has('categorie_id') && $request->categorie_id) {
            $query->whereHas('produit', function($q) use ($request) {
                $q->where('categorie_id', $request->categorie_id);
            });
        }

        if ($request->has('type_mouvement') && $request->type_mouvement) {
            $query->where('type_mouvement', $request->type_mouvement);
        }

        if ($request->has('emplacement_id') && $request->emplacement_id) {
            $query->where('emplacement_id', $request->emplacement_id);
        }

        if ($request->has('date_debut') && $request->date_debut) {
            $query->whereDate('date_mouvement', '>=', $request->date_debut);
        }

        if ($request->has('date_fin') && $request->date_fin) {
            $query->whereDate('date_mouvement', '<=', $request->date_fin);
        }

        // Récupérer les mouvements
        $mouvements = $query->paginate(50);

        // Statistiques des mouvements
        $stats = [
            'total_mouvements' => $mouvements->total(),
            'total_entrees' => $query->clone()->where('type_mouvement', 'entrée')->sum('quantite'),
            'total_sorties' => $query->clone()->where('type_mouvement', 'sortie')->sum('quantite'),
            'total_ventes' => $query->clone()->where('type_mouvement', 'vente')->sum('quantite'),
            'total_transferts' => $query->clone()->where('type_mouvement', 'transfert')->sum('quantite'),
            'valeur_entrees' => $query->clone()->where('type_mouvement', 'entrée')->get()->sum(function($mouvement) {
                return $mouvement->quantite * ($mouvement->produit->prix_unitaire ?? 0);
            }),
            'valeur_sorties' => $query->clone()->where('type_mouvement', 'sortie')->get()->sum(function($mouvement) {
                return $mouvement->quantite * ($mouvement->produit->prix_unitaire ?? 0);
            }),
            'valeur_ventes' => $query->clone()->where('type_mouvement', 'vente')->get()->sum(function($mouvement) {
                return $mouvement->quantite * ($mouvement->produit->prix_unitaire ?? 0);
            }),
        ];

        return view('reports.mouvementstock', compact(
            'mouvements', 
            'stats', 
            'categories',
            'emplacements',
            'produits'
        ));
    }
}