<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Emplacement;

class Facture extends Model
{
    use HasFactory;

    // Les champs assignables
    protected $fillable = [
        'numero_facture',
        'user_id',
        'table_id',
        'chambre_id',
        'sale_day_id',
        'total_ht',
        'remise',
        'total_ttc',
        'devise',
        'date_facture',
        'statut',
        'statut_service',
        'ets_id',
        'emplacement_id',
        'client_id'
    ];
    public function reservations()
{
    return $this->hasManyThrough(
        Reservation::class, // Le modèle final
        Client::class,      // Le modèle intermédiaire
        'id',               // Clé locale du modèle intermédiaire (Client)
        'client_id',        // Clé étrangère du modèle final (Reservation)
        'client_id',        // Clé étrangère locale sur la table Facture
        'id'                // Clé primaire sur la table Client
    );
}

    // Relations
    public function emplacement()
{
    return $this->belongsTo(Emplacement::class, 'emplacement_id');
}

    // Facture appartient à un utilisateur
    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }

    // Facture peut être liée à une table de restaurant
    public function table()
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }
    public function chambre()
    {
        return $this->belongsTo(Chambre::class, 'chambre_id');
    }

    // Facture peut être liée à un sale day
    public function saleDay()
    {
        return $this->belongsTo(SaleDay::class, "sale_day_id");
    }

    // Une facture a plusieurs détails
    public function details()
    {
        return $this->hasMany(FactureDetail::class, "facture_id", "id");
    }

    public function payments(){
        return $this->hasMany(Payments::class, "facture_id", "id");
    }


    public function client(){
        return $this->belongsTo(Client::class, "client_id");
    }
}
