<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DatasetPerBulan extends Model
{
    protected $table = "dataset_perbulans";
 
    protected $fillable = ['author','text','month','date'];
    public function preprocessing() {
        return $this->hasOne(PreprocessingPerBulan::class, 'dataset_id');
    }
} 
