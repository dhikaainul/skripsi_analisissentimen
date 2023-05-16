<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PreprocessingPerBulan extends Model
{
    protected $table = 'preprocessing_data_perbulans';
    protected $fillable = ['dataset_id','dataset_author','hasil_preprocessing_data','dataset_month','dataset_date'];
    public function dataset() {
        return $this->belongsTo(DatasetPerBulan::class);
    }
}
