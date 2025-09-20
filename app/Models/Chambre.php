<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Chambre extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        "numero",
        "type",
        "capacite",
        "prix",
        "prix_devise",
        "emplacement_id",
        "statut",
        "ets_id"
    ];


    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'chambre_id');
    }

    // Vérifie si la chambre est occupée actuellement
    public function occuped(): bool
    {
        return $this->reservations()
            ->whereIn('statut', ['confirmée'])
            ->where('date_debut', '<=', Carbon::today())
            ->where('date_fin', '>=', Carbon::today())
            ->exists();
    }

    public function getEtatAttribute(): string
    {
        return $this->occuped() ? 'Occupée' : 'Libre';
    }

    public function emplacement(){
        return $this->belongsTo(Emplacement::class, "emplacement_id");
    }
}
