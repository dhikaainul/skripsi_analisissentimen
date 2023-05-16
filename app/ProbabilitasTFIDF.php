<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProbabilitasTFIDF extends Model
{
    protected $table = 'probabilitas_tfidf';
    protected $fillable = ['dataset_id','probabilitas_positif','probabilitas_netral','probabilitas_negatif','max_value'];
    public function dataset() {
        return $this->belongsTo(Dataset::class);
    }
}
