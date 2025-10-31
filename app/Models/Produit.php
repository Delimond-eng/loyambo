<?php

namespace App\Models;

use App\Models\Categorie;
use App\Models\Inventaire;
use App\Models\Emplacement;
use App\Models\MouvementStock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produit extends Model
{
    use HasFactory;

    protected $fillable = [
        "code_barre",
        "reference",
        "categorie_id",
        "libelle",
        "prix_unitaire",
        "qte_init",
        "unite",
        "seuil_reappro",
        "image","quantified","ets_id","emplacement_id"
    ];

    public function categorie(){
        return $this->belongsTo(Categorie::class, "categorie_id");
    }

    // AJOUT: Relation avec emplacement
    public function emplacement(){
        return $this->belongsTo(Emplacement::class, "emplacement_id");
    }

    public function inventaires()
    {
        return $this->hasMany(Inventaire::class, 'produit_id');
    }

    public function stocks(){
        return $this->hasMany(MouvementStock::class, "produit_id","id");
    }
}
