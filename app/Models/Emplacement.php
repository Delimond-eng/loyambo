<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emplacement extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        "libelle",
        "type",
        "ets_id",
    ];

    public static function getTypesForEts(int $etsId)
    {
        return static::query()
            ->where("ets_id", $etsId)
            ->select("type")
            ->distinct()
            ->pluck("type")
            ->filter()
            ->values();
    }

    public static function isHotelType(?string $type): bool
    {
        if (!$type) {
            return false;
        }

        return in_array($type, ["hotel", "hôtel"], true);
    }

    // Relation tables (pour tout sauf hôtel)
    public function tables()
    {
        return $this->hasMany(RestaurantTable::class, "emplacement_id", "id");
    }
    public function chambres()
    {
        return $this->hasMany(Chambre::class, "emplacement_id", "id");
    }

    public function mouvements()
    {
        return $this->hasMany(MouvementStock::class, 'emplacement_id');
    }

    public function produits()
    {
        return $this->belongsToMany(Produit::class, 'emplacement_produit')->withPivot('prix')->withTimestamps();
    }
}
