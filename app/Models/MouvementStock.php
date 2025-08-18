<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MouvementStock extends Model
{
    use HasFactory;

    protected $fillable = [
        "produit_id",
        "numdoc",
        "type_mouvement",
        "quantite",
        "source",
        "destination",
        "sale_day_id", 
        "date_mouvement",
        "user_id"
    ];


    public function produit(){
        return $this->belongsTo(Produit::class, 'produit_id');
    }

    
}
