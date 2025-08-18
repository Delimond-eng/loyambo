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
        "ets_id",
    ];
}
