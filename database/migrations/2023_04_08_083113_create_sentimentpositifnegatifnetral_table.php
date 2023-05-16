<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSentimentpositifnegatifnetralTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sentimentpositifnegatifnetral', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('jumlah_positif');
            $table->string('jumlah_netral');
            $table->string('jumlah_negatif');
            $table->string('bulan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sentimentpositifnegatifnetral');
    }
}
