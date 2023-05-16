<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Preprocessing extends Model
{
    protected $table = 'preprocessing_datas';
    protected $fillable = ['dataset_id','dataset_author','hasil_preprocessing_data','dataset_kategori'];
    public function dataset() {
        return $this->belongsTo(Dataset::class);
    }
}
