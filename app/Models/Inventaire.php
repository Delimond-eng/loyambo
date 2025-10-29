<?php

namespace App\Models;

use App\Models\Produit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventaire extends Model
{
    use HasFactory;
    protected $fillable = [
        'produit_id',
        'ets_id',
        'quantite_physique',
        'quantite_theorique',
        'ecart',
        'observation',
        'date_inventaire',
        'user_id',
    ];

    public function produit()
    {
        return $this->belongsTo(Produit::class, 'produit_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
