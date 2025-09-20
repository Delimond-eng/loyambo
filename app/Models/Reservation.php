<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        "chambre_id",
        "table_id",
        "client_id",
        "date_debut",
        "date_fin",
        "statut",
        "ets_id"
    ];

    public function chambre(){
        return $this->belongsTo(Chambre::class, "chambre_id");
    }
}
