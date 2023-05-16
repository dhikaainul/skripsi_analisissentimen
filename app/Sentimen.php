<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sentimen extends Model
{
    protected $table = "sentimens";

    protected $fillable = ['jumlah_positif', 'jumlah_netral', 'jumlah_negatif','bulan'];
}
