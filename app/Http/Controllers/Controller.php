<?php

namespace App\Http\Controllers;

use App\ConfusionMatrixs;
use App\Klasifikasi;
use App\Preprocessing;
use App\ProbabilitasKategori;
use App\Traits\KelolaDataTrait;
use Illuminate\Http\Request;
use Phpml\Metric\Accuracy;
use Phpml\Metric\ConfusionMatrix;
use DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{

    use KelolaDataTrait;

    public function dataklasifikasi()
    {
        $klasifikasi = Klasifikasi::with('dataset')->get();
        $matrix = ConfusionMatrixs::all();
        return view('content.klasifikasi', ['klasifikasi' => $klasifikasi])
            ->with('matrix', $matrix);
    }

    public function klasifikasi()
    {
        $dataset2 = Preprocessing::with('dataset')->limit(500)->get();
        $kategoriData = $dataMentah = [];
        foreach ($dataset2 as $key => $value) {
            $dataMentah[] = $this->ambil_kata_unik(explode(' ', $value->hasil_preprocessing_data)); // Menghapus kata" yang sama dan mengembalikan kata-kata unik //array multidimensional
            if ($value->dataset->kategori == 1) { //cek kategori data == 1 (Positif)
                $kategoriData[] = '1'; //maka array kategoriData[] = 1
            } else if ($value->dataset->kategori == 2) { //cek kategori data == 2 (Negatif)
                $kategoriData[] = '2'; //maka array kategoriData[] =2
            } else if ($value->dataset->kategori == 0) { //cek kategori data == 0 (Netral)
                $kategoriData[] = '0'; //maka array kategoriData[] = 0
            }
        }
        // dd($kategoriData); // menyimpan kategori data seluruh kalimat 1/0/2 positif/negatif/netral 
        // dd($dataMentah); //  0 => array:7 [▼ 0 => "perintah" 1 => "jamin" 2 => "huni" 3 => "ikn" 4 => "nusantara" 5 => "aman" 6 => "100" ]
        $hasilDataset = [];
        for ($i = 0; $i < count($dataMentah); $i++) { // Dataset push ke array baru
            $hasilDataset = array_merge($hasilDataset, $dataMentah[$i]);
        }
        // dd($hasilDataset);  // 0 => "perintah" 1 => "jamin" 2 => "huni" 3 => "ikn" 4 => "nusantara" 5 => "aman"
        $hasilDataset = $this->ambil_kata_unik($hasilDataset); // Menghapus kata" yang sama dan mengembalikan kata-kata unik//array multidimensional
        $TF = [];
        foreach ($dataMentah as $key => $value) {
            $TF[] = $this->beratdarikata($value, $hasilDataset); // menghitung nilai per kata 
        }
        // dd($TF); // 0 => array:513 [▼ "perintah" => 1 "jamin" => 1 "huni" => 1 "ikn" => 1 "nusantara" => 1 "aman" => 1 100 => 1 "hadir" => 0
        $probabilitastiapkata = $jumlahkataperkategori = $sumTFIDF = $idf = $tfidf = $conditionalprobability =[
            '1' => [],
            '0' => [],
            '2' => [],
        ];

        $totalIDF = 0;
        foreach ($TF as $key => $value) {
            if ($kategoriData[$key] == '1') { //jika kategori data == 1/2/0 (positif/negatif/netral)
                foreach ($value as $k => $val) {
                    if ($val >= 1) { // jika $val lebih dari atau sama dengan 1
                        if (empty($jumlahkataperkategori['1'][$k])) {
                            $jumlahkataperkategori['1'][$k] = 1;
                        } else {
                            $jumlahkataperkategori['1'][$k] += 1;
                        }
                    } else {
                        if (empty($jumlahkataperkategori['1'][$k])) {
                            $jumlahkataperkategori['1'][$k] = 0;
                        }
                    }
                }
            } else if ($kategoriData[$key] == '0') {
                foreach ($value as $k => $val) {
                    if ($val >= 1) {
                        if (empty($jumlahkataperkategori['0'][$k])) {
                            $jumlahkataperkategori['0'][$k] = 1;
                        } else {
                            $jumlahkataperkategori['0'][$k] += 1;
                        }
                    } else {
                        if (empty($jumlahkataperkategori['0'][$k])) {
                            $jumlahkataperkategori['0'][$k] = 0;
                        }
                    }
                }
            } else if ($kategoriData[$key] == '2') {
                foreach ($value as $k => $val) {
                    if ($val >= 1) {
                        if (empty($jumlahkataperkategori['2'][$k])) {
                            $jumlahkataperkategori['2'][$k] = 1;
                        } else {
                            $jumlahkataperkategori['2'][$k] += 1;
                        }
                    } else {
                        if (empty($jumlahkataperkategori['2'][$k])) {
                            $jumlahkataperkategori['2'][$k] = 0;
                        }
                    }
                }
            }
        }
        // dd($jumlahkataperkategori);
        foreach ($TF as $key => $value) {
            if ($kategoriData[$key] == '1') {
                foreach ($value as $k => $val) {
                    if ($val >= 1) {
                        if (empty($sumTFIDF['1'][$k])) {
                            $sumTFIDF['1'][$k] = 1;
                        } else {
                            $sumTFIDF['1'][$k] += 1;
                        }
                        // } else {
                        //     if (empty($sumWordFlag['1'][$k])) {
                        //         $sumWordFlag['1'][$k] = 0;
                        //     }
                    }
                }
            } else if ($kategoriData[$key] == '0') {
                foreach ($value as $k => $val) {
                    if ($val >= 1) {
                        if (empty($sumTFIDF['0'][$k])) {
                            $sumTFIDF['0'][$k] = 1;
                        } else {
                            $sumTFIDF['0'][$k] += 1;
                        }
                        // } else {
                        //     if (empty($sumWordFlag['0'][$k])) {
                        //         $sumWordFlag['0'][$k] = 0;
                        //     }
                    }
                }
            } else if ($kategoriData[$key] == '2') {
                foreach ($value as $k => $val) {
                    if ($val >= 1) {
                        if (empty($sumTFIDF['2'][$k])) {
                            $sumTFIDF['2'][$k] = 1;
                        } else {
                            $sumTFIDF['2'][$k] += 1;
                        }
                        // } else {
                        //     if (empty($sumWordFlag['2'][$k])) {
                        //         $sumWordFlag['2'][$k] = 0;
                        //     }
                    }
                }
            }
        }

        $probabilitasKategori = $sumTextFlag = [
            '0' => [],
            '1' => [],
            '2' => [],
        ];
        $totalSameWord2 = [
            '1' => [],
            '0' => [],
            '2' => [],
        ];

        foreach ($dataset2 as $key => $value) {
            // dd($dataset2);
            if ($kategoriData[$key] == '1') {
                foreach ($value as $k => $val) {
                    if (empty($sumTextFlag['1'][$k])) {
                        $sumTextFlag['1'][$k] = 1;
                    } else if (!empty($sumTextFlag['1'][$k])) {
                        $sumTextFlag['1'][$k] += 1;
                    } else {
                        $sumTextFlag['1'][$k] = 0;
                    }
                }
            } else if ($kategoriData[$key] == '0') {
                foreach ($value as $k => $val) {
                    if (empty($sumTextFlag['0'][$k])) {
                        $sumTextFlag['0'][$k] = 1;
                    } else if (!empty($sumTextFlag['1'][$k])) {
                        $sumTextFlag['0'][$k] += 1;
                    } else {
                        $sumTextFlag['0'][$k] = 0;
                    }
                }
            } else if ($kategoriData[$key] == '2') {
                foreach ($value as $k => $val) {
                    if (empty($sumTextFlag['2'][$k])) {
                        $sumTextFlag['2'][$k] = 1;
                    } else if (!empty($sumTextFlag['1'][$k])) {
                        $sumTextFlag['2'][$k] += 1;
                    } else {
                        $sumTextFlag['2'][$k] = 0;
                    }
                }
            }
        }
        $totalData = count($dataset2);
        foreach ($sumTextFlag as $k => $v) {
            foreach ($v as $key => $value) {
                $probabilitasKategori[$k][$key] = round(@($value) / @($totalData), 4);
            }
        }
        ProbabilitasKategori::query()->truncate();
        foreach ($probabilitasKategori as $key => $value) {
            ProbabilitasKategori::create([
                'key' => $key,
                'value' => $value['exists']
            ]);
        }

        $totalDataset = count($hasilDataset);
        foreach ($jumlahkataperkategori as $k => $v) {
            // dd($sumWordFlag);
            $totalKataSama = array_sum($v);
            foreach ($v as $key => $value) {
                // $totalSameWord2[$k][$key] = array_sum($v);
                $probabilitastiapkata[$k][$key] = round(@(1 + $value) / @($totalKataSama + $totalDataset), 4);
            }
        }

        $totalSeluruhDataset = count($dataset2);
        // dd($totalSeluruhDataset);
        // dd($sumWordFlag);
        foreach ($jumlahkataperkategori as $k => $v) {
            // $totalKataSama = array_sum($v);
            foreach ($v as $key => $value) {
                // $totalSameWord2[$k][$key] = array_sum($v);
                if ($value > 0) {
                    $idf[$k][$key] = round(@(log10($totalSeluruhDataset / $value)), 4);
                } else {
                    $idf[$k][$key] = 0;
                }
            }
        }
        // dd($idf);
        foreach ($idf as $k => $v) {
            // dd($v);
            $totalIDF += array_sum($v);
            foreach ($v as $key => $value) {
                // $totalSameWord2[$k][$key] = array_sum($v);
                $tfidf[$k][$key] = round(@($value * $sumTFIDF[$k][$key]), 4);
            }
        }
        // dd($tfidf);
        foreach ($tfidf as $k => $v) {
            // dd($tfidf);
            $totalTFIDFSama = array_sum($v);
            foreach ($v as $key => $value) {
                $totalSameWord2[$k][$key] = array_sum($v);
                $conditionalprobability[$k][$key] = round(@(1 + $value) / @($totalTFIDFSama + $totalIDF), 4);
            }
        }
        // dd($totalIDF);
        // dd($totalSameWord2);
        // dd($conditionalprobability);

        // dd($totalSameWord2);
        // dd($normalisasi);
        // Data Uji
        $HasilTesData = $HasilPrediksiData = $dataNormalisasiUtama = [];
        $countData = 200;
        for ($i = 0; $i < $countData; $i++) {
            $index = $i;
            $kata_kunci = $dataset2[$index]->hasil_preprocessing_data;
            $kata_kunci = explode(' ', $kata_kunci);
            $probabilitas = ProbabilitasKategori::all();
            $finalResult = [];
            $finalResultTFIDF = [];

            foreach ($probabilitastiapkata as $key => $value) {
                $finalResult[$key] = $probabilitas[$key]->value;
                foreach ($value as $k => $v) {
                    if ($this->searchValue($kata_kunci, $k)) {
                        $finalResult[$key] *= $v;
                        // dd($finalResult);
                    }
                }
            }
            $dataNormalisasiUtama[] = $dataset2[$index];
            $HasilTesData[] = $dataset2[$index]->dataset->kategori;
            $HasilPrediksiData[] = array_keys($finalResult, max($finalResult))[0];
            // dd(max($finalResult));

            foreach ($conditionalprobability as $key => $value) {
                $finalResultTFIDF[$key] = $probabilitas[$key]->value;
                foreach ($value as $k => $v) {
                    if ($this->searchValue($kata_kunci, $k)) {
                        $finalResultTFIDF[$key] *= $v;
                        // dd($finalResult);
                    }
                }
            }
            // $dataNormalisasiUtamaTFIDF[] = $dataset2[$index];
            $HasilTesDataTFIDF[] = $dataset2[$index]->dataset->kategori;
            $HasilPrediksiDataTFIDF[] = array_keys($finalResultTFIDF, max($finalResultTFIDF))[0];
        }
        // dd($finalResult);
        Klasifikasi::query()->truncate();
        foreach ($dataNormalisasiUtama as $key => $value) {
            Klasifikasi::create([
                'dataset_id' => $value->dataset_id,
                'kategori_dataset' => $HasilTesData[$key],
                'kategori_prediksi' => $HasilPrediksiData[$key],
                'kategori_prediksi_tfidf' => $HasilPrediksiDataTFIDF[$key]
            ]);
        }
        $confusionMatrix = ConfusionMatrix::compute($HasilTesData, $HasilPrediksiData, [0, 1, 2]);
        //positif
        $positifTP = $confusionMatrix[1][1];
        // dd($positifTP); //array multidimensional array 1 dan isinya array ke 1 = 169 (true positif)
        $positifTN = $confusionMatrix[1][0];
        // dd($positifTN); //array multidimensional array 1 dan isinya array ke 0 = 8 (true negatif)
        $positifFN = $confusionMatrix[1][2];
        // dd($positifFN); //array multidimensional array 1 dan isinya array ke 2 = 4 (false negatif)

        //negatif
        $negatifTP = $confusionMatrix[0][0];
        // dd($negatifTP); //array multidimensional array 0 dan isinya array ke 0 = 3 (true positif)
        $negatifTN = $confusionMatrix[0][1];
        // dd($negatifTN); //array multidimensional array 0 dan isinya array ke 1 = 0 (true negatiff)
        $negatifFN = $confusionMatrix[0][2];
        // dd($negatifFN); //array multidimensional array 0 dan isinya array ke 0 = 0 (false negatif)

        $netralTP = $confusionMatrix[2][2];
        // dd($netralTP); //array multidimensional array 2 dan isinya array ke 2 = 10 (true positif)
        $netralTN = $confusionMatrix[2][0];
         // dd($netralTN); //array multidimensional array 2 dan isinya array ke 0 = 0 (true negatif)
        $netralFN = $confusionMatrix[2][1];
        // dd($netralFN); //array multidimensional array 2 dan isinya array ke 1 = 6 (false negatif)

        $truePositif = $positifTP + $negatifTP + $netralTP; // Data True dan benar (TP)
        $trueNegative = $positifTN + $negatifTN + $netralTN; // Data Salah dan benar (TN)
        $falseNegative = $positifFN + $negatifFN + $netralFN; // Data Salah dan negatif (FN) 

        $akurasi = Accuracy::score($HasilTesData, $HasilPrediksiData);
        // $akurasi = $truePositif / ($truePositif + $trueNegative + $falseNegative); // TruePositif / All Data
        $presisiPositif = @($positifTP / ($positifTP + $positifTN));
        $presisiNegatif = @($negatifTP / ($negatifTP + $negatifTN));
        $presisiNetral = @($netralTP / ($netralTP + $netralTN));
        $presisi = ($presisiPositif + $presisiNegatif + $presisiNetral) / 3; // All Presisi / Jumlah Kelas
        $presisi2 = round(@($presisi), 2);
        $recallPositif = @($positifTP / ($positifTP + $positifFN)); // True Positif / TP + FN
        $recallNegatif = @($negatifTP / ($negatifTP + $negatifFN)); // True Positif / TP + FN
        $recallNetral = @($netralTP / ($netralTP + $netralFN)); // True Positif / TP + FN
        $recall = ($recallPositif + $recallNegatif + $recallNetral) / 3; // All Recall / Jumlah Kelas
        $recall2 =  round(@($recall), 2);
        ConfusionMatrixs::query()->truncate();
        ConfusionMatrixs::create([
            'key' => 'Akurasi',
            'value' => $akurasi,
        ]);
        ConfusionMatrixs::create([
            'key' => 'Presisi',
            'value' => $presisi2,
        ]);
        ConfusionMatrixs::create([
            'key' => 'Recall',
            'value' => $recall2
        ]);

        $confusionMatrix = ConfusionMatrix::compute($HasilPrediksiDataTFIDF, $HasilPrediksiDataTFIDF, [0, 1, 2]);
        //positif
        $positifTP = $confusionMatrix[1][1];
        // dd($positifTP); //array multidimensional array 1 dan isinya array ke 1 = 169 (true positif)
        $positifTN = $confusionMatrix[1][0];
        // dd($positifTN); //array multidimensional array 1 dan isinya array ke 0 = 8 (true negatif)
        $positifFN = $confusionMatrix[1][2];
        // dd($positifFN); //array multidimensional array 1 dan isinya array ke 2 = 4 (false negatif)

        //negatif
        $negatifTP = $confusionMatrix[0][0];
        // dd($negatifTP); //array multidimensional array 0 dan isinya array ke 0 = 3 (true positif)
        $negatifTN = $confusionMatrix[0][1];
        // dd($negatifTN); //array multidimensional array 0 dan isinya array ke 1 = 0 (true negatiff)
        $negatifFN = $confusionMatrix[0][2];
        // dd($negatifFN); //array multidimensional array 0 dan isinya array ke 0 = 0 (false negatif)

        $netralTP = $confusionMatrix[2][2];
        // dd($netralTP); //array multidimensional array 2 dan isinya array ke 2 = 10 (true positif)
        $netralTN = $confusionMatrix[2][0];
        // dd($netralTN); //array multidimensional array 2 dan isinya array ke 0 = 0 (true negatif)
        $netralFN = $confusionMatrix[2][1];
        // dd($netralFN); //array multidimensional array 2 dan isinya array ke 1 = 6 (false negatif)

        $truePositif = $positifTP + $negatifTP + $netralTP; // Data True dan benar (TP)
        $trueNegative = $positifTN + $negatifTN + $netralTN; // Data Salah dan benar (TN)
        $falseNegative = $positifFN + $negatifFN + $netralFN; // Data Salah dan negatif (FN) 

        $akurasi = Accuracy::score($HasilTesDataTFIDF, $HasilPrediksiDataTFIDF);
        // $akurasi = $truePositif / ($truePositif + $trueNegative + $falseNegative); // TruePositif / All Data
        $presisiPositif = @($positifTP / ($positifTP + $positifTN));
        $presisiNegatif = @($negatifTP / ($negatifTP + $negatifTN));
        $presisiNetral = @($netralTP / ($netralTP + $netralTN));
        $presisi = ($presisiPositif + $presisiNegatif + $presisiNetral) / 3; // All Presisi / Jumlah Kelas
        $presisi2 = round(@($presisi), 2);
        $recallPositif = @($positifTP / ($positifTP + $positifFN)); // True Positif / TP + FN
        $recallNegatif = @($negatifTP / ($negatifTP + $negatifFN)); // True Positif / TP + FN
        $recallNetral = @($netralTP / ($netralTP + $netralFN)); // True Positif / TP + FN
        $recall = ($recallPositif + $recallNegatif + $recallNetral) / 3; // All Recall / Jumlah Kelas
        $recall2 =  round(@($recall), 2);
        // ConfusionMatrixs::query()->truncate();
        ConfusionMatrixs::create([
            'key' => 'Akurasi TF-IDF',
            'value' => $akurasi,
        ]);
        ConfusionMatrixs::create([
            'key' => 'Presisi TF-IDF',
            'value' => $presisi2,
        ]);
        ConfusionMatrixs::create([
            'key' => 'Recall TF-IDF',
            'value' => $recall2
        ]);

        return redirect()->route('klasifikasi')->with('sukses', 'Data Preprocessing Berhasil Di Klasifikasi');
    }
    // public function klasifikasi2()
    // {
    //     $data = DB::table('preprocessing_datas')
    //         ->join('datasets', 'datasets.id', '=', 'preprocessing_datas.dataset_id')
    //         ->limit(500)
    //         ->get();
    //     // dd($data);

    //     $inisiasiKata = [
    //         '1' => [],
    //         '0' => [],
    //         '2' => [],
    //     ];
    //     foreach ($data as $key => $value) {
    //         $kal = explode(' ', $value->hasil_preprocessing_data);
    //         foreach ($kal as $key => $kat) {
    //             $inisiasiKata['0'] = [$kat];
    //             $inisiasiKata['1'] = [$kat];
    //             $inisiasiKata['2'] = [$kat];
    //         }
    //     }
    //     dd($inisiasiKata);
    //     // dd($kal);

    //     $kataPositif = $inisiasiKata;
    //     $kataNegatif = $inisiasiKata;
    //     $kataNetral = $inisiasiKata;
    //     foreach ($data as $key => $value) {
    //         $kalimat[0] = $this->get_data_unique(explode(' ', $value->hasil_preprocessing_data));
    //         // dd($data);
    //         $kategoriKal = $value->kategori;
    //         if ($kategoriKal == 1) {
    //             foreach ($kalimat[0] as $key => $kata) {
    //                 if ($kataPositif['1'][$kata] == 0) {
    //                     $kataPositif['1'][$kata] = 1;
    //                 } else {
    //                     $kataPositif['1'][$kata] += 1;
    //                 }
    //             }
    //         }
    //         if ($kategoriKal == 2) {
    //             foreach ($kalimat[0] as $key => $kata) {
    //                 if ($kataNegatif[$kata] == 0) {
    //                     $kataNegatif[$kata] = 1;
    //                 } else {
    //                     $kataNegatif[$kata] += 1;
    //                 }
    //             }
    //         }
    //         if ($kategoriKal == 0) {
    //             foreach ($kalimat[0] as $key => $kata) {
    //                 if ($kataNetral[$kata] == 0) {
    //                     $kataNetral[$kata] = 1;
    //                 } else {
    //                     $kataNetral[$kata] += 1;
    //                 }
    //             }
    //         }
    //     }
    //     dd($kataPositif);
    // }
    public function searchValue($arr, $keyword)
    {
        // dd($arr); //explode kalimat   0 => "perintah"1 => "jamin"2 => "huni"3 => "ikn"4 => "nusantara"5 => "aman"
        // dd($keyword); //menampilkan $k = perintah
        foreach ($arr as $key => $value) {
            if ($value == $keyword) {
                return true;
            }
        }
        return false;
    }

    private function beratdarikata($data, $dataset) //maksute iki piye
    {
        // dd($data);
        $result = [];
        $index = 0;
        /** Berdasarkan datacrawl */
        // substr_count("Hello world. The world is nice","world")
        // $newDataset = implode(' ', $dataset);
        // foreach ($data as $key => $value) { // Get all dataset
        //     if(!empty($value)) { // Jaga" jika tidak terdpat kata
        //         $result[$value] = substr_count($newDataset, $value);
        //     } else {
        //         $result[$value] = 0; 
        //     }
        // }
        /** Berdasarkan dataset */
        $newData = implode(' ', $data); // kata dijadikan kalimat
        foreach ($dataset as $key => $value) {
            if (!empty($value)) { // Jaga" jika tidak terdpat kata //jika tidak kosong value/kata
                $result[$value] = substr_count($newData, $value); //maka hitung jumlah data value berdasarkan kata value di kalimat new data
                // dd($result[$value]);
                // dd($newData);
            } else {
                $result[$value] = 0; //jika kosong katanya maka = 0
            }
        }
        return $result;
    }
}

