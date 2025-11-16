<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Etablissement extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';


    protected $fillable = [
        'nom',
        'type',
        'adresse',
        'telephone',
        'token',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }


    public function licence(){
        return $this->hasOne(Licence::class, "ets_id", "id");
    }
}
