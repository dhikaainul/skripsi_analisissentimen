<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Klasifikasi extends Model
{
    protected $table = 'klasifikasis';
    protected $fillable = ['dataset_id','kategori_dataset','kategori_prediksi','kategori_prediksi_tfidf','hasil_preprocessing_data'];
    public function dataset() {
        return $this->belongsTo(Dataset::class);
    }
}
