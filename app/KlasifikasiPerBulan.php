<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KlasifikasiPerBulan extends Model
{
    protected $table = 'klasifikasi_perbulans';
    protected $fillable = ['dataset_id','hasil_preprocessing_data','kategori_prediksi_tfidf','dataset_month','dataset_date'];
    public function dataset() {
        return $this->belongsTo(DatasetPerBulan::class);
    }
}
