<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConfusionMatrixTF extends Model
{
    protected $table = "confusion_matrixs_tf";
 
    protected $fillable = ['key','value'];
}
