<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProbabilitasKategori extends Model
{
    protected $table = "probabilitas_kategoris";
 
    protected $fillable = ['key','value'];
}
