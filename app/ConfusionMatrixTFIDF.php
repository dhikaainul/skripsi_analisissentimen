<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConfusionMatrixTFIDF extends Model
{
    protected $table = "confusion_matrixs_tfidf";

    protected $fillable = ['key', 'value'];
}
