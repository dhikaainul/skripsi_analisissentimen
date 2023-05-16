<?php

namespace App\Traits;

trait KelolaDataTrait{

    public function ambil_kata_unik($kataperkalimat) {
        $kata = [];
        foreach ($kataperkalimat as $k => $value) { //kata-kata yang ada didalam kalimat
        // dd($kataperkalimat);
            if(empty($kata)) { // jika kata $kata kosong
                array_push($kata, $value); //maka push $value ke $kata
            } else {
                if(!in_array($value, $kata)) { //melakukan pengecekan jika $value tidak didalam array $kata 
                    array_push($kata, $value); //maka push $value ke $kata
                }
            }
        }
        return $kata; // mengembalikan kagta
    }
}
