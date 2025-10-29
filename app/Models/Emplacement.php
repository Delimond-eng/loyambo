<?php

namespace App\Models;

use App\Models\Facture;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Emplacement extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        "libelle",
        "type",
        "ets_id",
    ];

    // Relation tables (pour tout sauf hôtel)
    public function tables()
    {
        return $this->hasMany(RestaurantTable::class, "emplacement_id", "id");
    }
    public function chambres()
    {
        return $this->hasMany(Chambre::class, "emplacement_id", "id");
    }
    public function factures()
    {
        return $this->hasMany(Facture::class, "emplacement_id", "id");
    }
}
