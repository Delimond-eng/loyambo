<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantTable extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        "numero",
        "emplacement_id",
        "statut"
    ];

    public function emplacement(){
        return $this->belongsTo(Emplacement::class, "emplacement_id");
    }
}
