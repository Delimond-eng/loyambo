<?php

namespace App\Models;

use App\Models\Inventaire;
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
        "image","quantified","ets_id"
    ];

    public function categorie(){
        return $this->belongsTo(Categorie::class, "categorie_id");
    }
    public function inventaires()
{
    return $this->hasMany(Inventaire::class, 'produit_id');
}

    public function stocks(){
        return $this->hasMany(MouvementStock::class, "produit_id","id");
    }
}
