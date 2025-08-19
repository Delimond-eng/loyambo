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


    public function tables(){
        return $this->hasMany(RestaurantTable::class, "emplacement_id", "id");
    }
}
