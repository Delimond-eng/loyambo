<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;
use App\Models\Facture;


class Reservation extends Model
{
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
    public function table(){
        return $this->belongsTo(RestaurantTable::class, "table_id");
    }

    public function client(){
        return $this->belongsTo(Client::class, "client_id");
    }
    public function facture()
{
    return $this->hasManyThrough(
        Facture::class, // Modèle final
        Client::class,  // Modèle intermédiaire
        'id',           // Clé locale sur la table clients
        'client_id',    // Clé étrangère sur la table factures
        'client_id',    // Clé étrangère locale sur la table reservations
        'id'            // Clé primaire sur la table clients
    );
}
}
