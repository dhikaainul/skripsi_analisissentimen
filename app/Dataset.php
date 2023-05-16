<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dataset extends Model
{
    protected $table = "datasets";
 
    protected $fillable = ['author','text','platform','kategori'];
    public function klasifikasi() {
        return $this->hasOne(Klasifikasi::class, 'dataset_id');
    }
}
