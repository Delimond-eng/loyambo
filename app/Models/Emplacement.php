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

    // Relation tables (pour tout sauf hôtel)
    public function tables()
    {
        return $this->hasMany(RestaurantTable::class, "emplacement_id", "id")
                    ->whereHas("emplacement", function ($q) {
                        $q->where("type", "!=", "hôtel");
                    });
    }

    // Relation beds (uniquement hôtel)
    public function beds()
    {
        return $this->hasMany(RestaurantTable::class, "emplacement_id", "id")
                    ->whereHas("emplacement", function ($q) {
                        $q->where("type", "hôtel");
                    });
    }

    // Attribut calculé : items (toujours correct)
    protected $appends = ['items'];

    public function getItemsAttribute()
    {
        if ($this->type === 'hôtel') {
            return $this->beds;
        }

        return $this->tables;
    }
}
