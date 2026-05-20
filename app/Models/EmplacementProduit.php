<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmplacementProduit extends Model
{
    protected $table = 'emplacement_produit';

    protected $fillable = [
        'emplacement_id',
        'produit_id',
        'prix',
    ];

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function emplacement()
    {
        return $this->belongsTo(Emplacement::class);
    }
}