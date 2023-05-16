<?php

namespace App\Http\Controllers;

use App\ConfusionMatrixTF;
use App\ConfusionMatrixTFIDF;
use App\Klasifikasi;
use App\Preprocessing;
use App\ProbabilitasKategori;
use App\ProbabilitasTF;
use App\ProbabilitasTFIDF;
use App\Traits\KelolaDataTrait;
use Illuminate\Http\Request;
use Phpml\Metric\Accuracy;
use Phpml\Metric\ConfusionMatrix;
use DB;
use Phpml\Metric\ClassificationReport;

class KlasifikasiOriginalController extends Controller
{

    use KelolaDataTrait;

    public function dataklasifikasi()
    {
        $klasifikasi = Klasifikasi::with('dataset')->get();
        $matrixtf = ConfusionMatrixTF::all();
        $matrixtfidf = ConfusionMatrixTFIDF::all();
        return view('content.klasifikasi', ['klasifikasi' => $klasifikasi])
            ->with('matrixtf', $matrixtf)
            ->with('matrixtfidf', $matrixtfidf);
    }

    public function klasifikasi()
    {
        $dataset = Preprocessing::with('dataset')->limit(500)->get();
        // $dataset = Preprocessing::with('dataset')->limit(800)->get();
        $kategoriData = $dataMentah = [];
        foreach ($dataset as $key => $value) {
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
        // dd($dataMentah);
        foreach ($dataMentah as $key => $value) {
            $TF[] = $this->beratdarikata($value, $hasilDataset); // menghitung nilai per kata 
        }
        // dd($TF); // 0 => array:513 [▼ "perintah" => 1 "jamin" => 1 "huni" => 1 "ikn" => 1 "nusantara" => 1 "aman" => 1 100 => 1 "hadir" => 0
        $probabilitastiapkata = $jumlahKataPerkategori = $jumlahTFIDF = $idf = $tfidf = $conditionalprobability = [
            '1' => [],
            '0' => [],
            '2' => [],
        ];

        $totalIDF = 0;

        // Jumlah Kata Per Kategori

        foreach ($TF as $key => $value) {
            if ($kategoriData[$key] == '1') { //jika kategori data == 1/2/0 (positif/negatif/netral)
                foreach ($value as $kata => $val) { // $val = value nilai dari kata
                    if ($val >= 1) { // jika $val lebih dari atau sama dengan 1
                        if (empty($jumlahKataPerkategori['1'][$kata])) { //jika nilai pada kata kosong maka = 1
                            $jumlahKataPerkategori['1'][$kata] = 1;
                        } else {
                            $jumlahKataPerkategori['1'][$kata] += 1; //jika nilai kata tidak kosong maka + 1
                        }
                    } else {
                        if (empty($jumlahKataPerkategori['1'][$kata])) { //jika kata tidak ada atau kosong 
                            $jumlahKataPerkategori['1'][$kata] = 0;  // maka = 0
                        }
                    }
                }
            } else if ($kategoriData[$key] == '0') {
                foreach ($value as $kata => $val) {
                    if ($val >= 1) {
                        if (empty($jumlahKataPerkategori['0'][$kata])) {
                            $jumlahKataPerkategori['0'][$kata] = 1;
                        } else {
                            $jumlahKataPerkategori['0'][$kata] += 1;
                        }
                    } else {
                        if (empty($jumlahKataPerkategori['0'][$kata])) {
                            $jumlahKataPerkategori['0'][$kata] = 0;
                        }
                    }
                }
            } else if ($kategoriData[$key] == '2') {
                foreach ($value as $kata => $val) {
                    if ($val >= 1) {
                        if (empty($jumlahKataPerkategori['2'][$kata])) {
                            $jumlahKataPerkategori['2'][$kata] = 1;
                        } else {
                            $jumlahKataPerkategori['2'][$kata] += 1;
                        }
                    } else {
                        if (empty($jumlahKataPerkategori['2'][$kata])) {
                            $jumlahKataPerkategori['2'][$kata] = 0;
                        }
                    }
                }
            }
        }
        // dd($jumlahKataPerkategori);
        // Jumlah TF IDF
        foreach ($TF as $key => $value) {
            if ($kategoriData[$key] == '1') { //jika kategori data == 1/2/0 (positif/negatif/netral)
                foreach ($value as $kata => $val) { // $val = value nilai dari kata
                    if ($val >= 1) { // jika $val lebih dari atau sama dengan
                        if (empty($jumlahTFIDF['1'][$kata])) { //jika kosong maka = 1
                            $jumlahTFIDF['1'][$kata] = 1;
                        } else {
                            $jumlahTFIDF['1'][$kata] += 1; //jika tidak kosong maka + 1
                        }
                    }
                }
            } else if ($kategoriData[$key] == '0') {
                foreach ($value as $kata => $val) {
                    if ($val >= 1) {
                        if (empty($jumlahTFIDF['0'][$kata])) {
                            $jumlahTFIDF['0'][$kata] = 1;
                        } else {
                            $jumlahTFIDF['0'][$kata] += 1;
                        }
                    }
                }
            } else if ($kategoriData[$key] == '2') {
                foreach ($value as $kata => $val) {
                    if ($val >= 1) {
                        if (empty($jumlahTFIDF['2'][$kata])) {
                            $jumlahTFIDF['2'][$kata] = 1;
                        } else {
                            $jumlahTFIDF['2'][$kata] += 1;
                        }
                    }
                }
            }
        }

        $probabilitasKategori = $jumlahKategori = $totalSameWord2 = $coba = [
            '0' => [],
            '1' => [],
            '2' => [],
        ];

        //Jumlah Seluruh Data Perkategori

        foreach ($dataset as $key => $value) {
            // dd($dataset);
            if ($kategoriData[$key] == '1') { //jika Kategoridata pada key == 1
                foreach ($value as $kata => $val) { // Perulangan setiap dataset yang 1 jika ada yang sama
                    if (empty($jumlahKategori['1'][$kata])) { //dicek 1 per 1 kata dengan bobot bertambah atau tetap atau 0 //jika kosong maka =1
                        $jumlahKategori['1'][$kata] = 1; // Jika array tsb belum ada maka = 1 $k == kata yang diset
                    } else if (!empty($jumlahKategori['1'][$kata])) {
                        $jumlahKategori['1'][$kata] += 1; // jika sudah ada maka + 1
                    } else {
                        $jumlahKategori['1'][$kata] = 0; // jika data tsb belum ada dan tidak ada data maka = 0
                    }
                }
            } else if ($kategoriData[$key] == '0') {
                foreach ($value as $kata => $val) { // Perulangan setiap dataset yang 1 jika ada yang sama
                    if (empty($jumlahKategori['0'][$kata])) { //dicek 1 per 1 kata dengan bobot bertambah atau tetap atau 0 //jika kosong maka =1
                        $jumlahKategori['0'][$kata] = 1; // Jika array tsb belum ada maka = 1 $k == kata yang diset
                    } else if (!empty($jumlahKategori['0'][$kata])) {
                        $jumlahKategori['0'][$kata] += 1; // jika sudah ada maka + 1
                    } else {
                        $jumlahKategori['0'][$kata] = 0; // jika data tsb belum ada dan tidak ada data maka = 0
                    }
                }
            } else if ($kategoriData[$key] == '2') {
                foreach ($value as $kata => $val) { // Perulangan setiap dataset yang 1 jika ada yang sama
                    if (empty($jumlahKategori['2'][$kata])) { //dicek 1 per 1 kata dengan bobot bertambah atau tetap atau 0 //jika kosong maka =1
                        $jumlahKategori['2'][$kata] = 1; // Jika array tsb belum ada maka = 1 $k == kata yang diset
                    } else if (!empty($jumlahKategori['2'][$kata])) {
                        $jumlahKategori['2'][$kata] += 1; // jika sudah ada maka + 1
                    } else {
                        $jumlahKategori['2'][$kata] = 0; // jika data tsb belum ada dan tidak maka = 0
                    }
                }
            }
        }
        // dd($jumlahKategori);
        $totaldata = count($dataset);
        foreach ($jumlahKategori as $k => $v) { //$k = 1/0/2 $v = 0 = "exists" => 43 1 = "exists" => 450 2 = "exists" => 7
            foreach ($v as $key => $value) {
                // probablitas dari setiap kategori
                $probabilitasKategori[$k][$key] = round(@($value) / @($totaldata), 4);
            }
        }
        ProbabilitasKategori::query()->truncate(); // hapus isi tabel dari probabilitaskategori
        foreach ($probabilitasKategori as $key => $value) {
            ProbabilitasKategori::create([
                'key' => $key,
                'value' => $value['exists']
            ]);
        }

        // dd($probabilitasKategori);

        $totalDataset = count($hasilDataset);
        foreach ($jumlahKataPerkategori as $k => $v) {
            // dd($jumlahKataPerkategori);
            $totalKataSama = array_sum($v);
            foreach ($v as $key => $value) {
                // $totalSameWord2[$k][$key] = array_sum($v);

                $probabilitastiapkata[$k][$key] = round(@(1 + $value) / @($totalKataSama + $totalDataset), 4);
            }
        }

        //Hitung nilai IDF
        $totalSeluruhDataset = count($dataset);
        // dd($totalSeluruhDataset);
        // dd($sumWordFlag);
        foreach ($jumlahKataPerkategori as $k => $v) {
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

        //Hitung TF-IDF

        // dd($idf);
        // dd($jumlahTFIDF);
        foreach ($idf as $k => $v) {
            // dd($v);
            $totalIDF += array_sum($v);
            foreach ($v as $key => $value) {
                // $totalSameWord2[$k][$key] = array_sum($v);
                $tfidf[$k][$key] = round(@($value * $jumlahTFIDF[$k][$key]), 4);
            }
        }
        // dd($tfidf);

        // Hitung Conditional Probability
        foreach ($tfidf as $k => $v) {
            // dd($tfidf);
            $totalTFIDFSama = array_sum($v);
            foreach ($v as $key => $value) {
                // $totalSameWord2[$k][$key] = array_sum($v);
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
        ProbabilitasTF::query()->truncate();
        ProbabilitasTFIDF::query()->truncate();
        for ($i = 0; $i < $countData; $i++) {
            $index = $i;
            $kalimat = $dataset[$index]->hasil_preprocessing_data;
            $kalimat_dipisah_perkata = explode(' ', $kalimat);
            $probabilitas = ProbabilitasKategori::all();
            $finalResult = [];
            $finalResultTFIDF = [];
            // Hitung Probabilitas kelas dengan dikali nilai perbandingan kategori dengan seluruh data
            foreach ($probabilitastiapkata as $key => $value) {
                $finalResult[$key] = $probabilitas[$key]->value;
                foreach ($value as $kata_kunci => $v) {
                    if ($this->searchValue($kalimat_dipisah_perkata, $kata_kunci)) {
                        $finalResult[$key] *= $v;
                        // dd($finalResult);
                    }
                }
            }
            // dd($kata_kunci);
            $dataNormalisasiUtama[] = $dataset[$index];
            $HasilTesData[] = $dataset[$index]->dataset->kategori;
            $HasilPrediksiData[] = array_keys($finalResult, max($finalResult))[0];
            // dd($finalResult);

            //TF
            ProbabilitasTF::insert([
                'dataset_id' => $index + 1,
                'probabilitas_positif' => $finalResult[1],
                'probabilitas_netral' => $finalResult[0],
                'probabilitas_negatif' => $finalResult[2],
                'max_value' => max($finalResult),
            ]);
           

            foreach ($conditionalprobability as $key => $value) {
                $finalResultTFIDF[$key] = $probabilitas[$key]->value;
                // Hitung Probabilitas kelas dengan dikali nilai perbandingan kategori dengan seluruh data
                foreach ($value as $kata_kunci => $v) {
                    if ($this->searchValue($kalimat_dipisah_perkata, $kata_kunci)) {
                        $finalResultTFIDF[$key] *= $v;
                        // dd($finalResult);
                    }
                }
            }
            // $dataNormalisasiUtamaTFIDF[] = $dataset2[$index];
            $HasilTesDataTFIDF[] = $dataset[$index]->dataset->kategori;
            $HasilPrediksiDataTFIDF[] = array_keys($finalResultTFIDF, max($finalResultTFIDF))[0];

            //TFIDF
            ProbabilitasTFIDF::insert([
                'dataset_id' => $index + 1,
                'probabilitas_positif' => $finalResultTFIDF[1],
                'probabilitas_netral' => $finalResultTFIDF[0],
                'probabilitas_negatif' => $finalResultTFIDF[2],
                'max_value' => max($finalResultTFIDF),
            ]);
        }

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
        // $report = new ClassificationReport($HasilTesData, $HasilPrediksiData);
        // $report->getAverage();
        // dd($report);
        //positif
        $positifTP = $confusionMatrix[1][1];
        // dd($positifTP); //array multidimensional array 1 dan isinya array ke 1 (true positif)
        $positifTN = $confusionMatrix[1][0];
        // dd($positifTN); //array multidimensional array 1 dan isinya array ke 0 (true negatif)
        $positifFN = $confusionMatrix[1][2];
        // dd($positifFN); //array multidimensional array 1 dan isinya array ke 2 (false negatif)

        //negatif
        $negatifTP = $confusionMatrix[0][0];
        // dd($negatifTP); //array multidimensional array 0 dan isinya array ke 0 (true positif)
        $negatifTN = $confusionMatrix[0][1];
        // dd($negatifTN); //array multidimensional array 0 dan isinya array ke 1 (true negatiff)
        $negatifFN = $confusionMatrix[0][2];
        // dd($negatifFN); //array multidimensional array 0 dan isinya array ke 0 (false negatif)

        $netralTP = $confusionMatrix[2][2];
        // dd($netralTP); //array multidimensional array 2 dan isinya array ke 2 (true positif)
        $netralTN = $confusionMatrix[2][0];
        // dd($netralTN); //array multidimensional array 2 dan isinya array ke 0 (true negatif)
        $netralFN = $confusionMatrix[2][1];
        // dd($netralFN); //array multidimensional array 2 dan isinya array ke 1 (false negatif)

        $truePositif = $positifTP + $negatifTP + $netralTP; // Data True dan benar (TP)
        $trueNegative = $positifTN + $negatifTN + $netralTN; // Data Salah dan benar (TN)
        $falseNegative = $positifFN + $negatifFN + $netralFN; // Data Salah dan negatif (FN) 

        $akurasiTF = Accuracy::score($HasilTesData, $HasilPrediksiData);
        // $akurasiTF = $truePositif / ($truePositif + $trueNegative + $falseNegative); // TruePositif / All Data
        $presisiPositif = @($positifTP / ($positifTP + $positifTN));
        $presisiNegatif = @($negatifTP / ($negatifTP + $negatifTN));
        $presisiNetral = @($netralTP / ($netralTP + $netralTN));
        $presisi = ($presisiPositif + $presisiNegatif + $presisiNetral) / 3; // All Presisi / Jumlah Kelas
        $presisiTF = round(@($presisi), 2);
        $recallPositif = @($positifTP / ($positifTP + $positifFN)); // True Positif / TP + FN
        $recallNegatif = @($negatifTP / ($negatifTP + $negatifFN)); // True Positif / TP + FN
        $recallNetral = @($netralTP / ($netralTP + $netralFN)); // True Positif / TP + FN
        $recall = ($recallPositif + $recallNegatif + $recallNetral) / 3; // All Recall / Jumlah Kelas
        $recallTF =  round(@($recall), 2);
        ConfusionMatrixTF::query()->truncate();
        ConfusionMatrixTF::create([
            'key' => 'Akurasi',
            'value' => $akurasiTF,
        ]);
        ConfusionMatrixTF::create([
            'key' => 'Presisi',
            'value' => $presisiTF,
        ]);
        ConfusionMatrixTF::create([
            'key' => 'Recall',
            'value' => $recallTF
        ]);

        $confusionMatrix = ConfusionMatrix::compute($HasilTesDataTFIDF, $HasilPrediksiDataTFIDF, [0, 1, 2]);
        //positif
        $positifTP = $confusionMatrix[1][1];
        // dd($positifTP); //array multidimensional array 1 dan isinya array ke 1 (true positif)
        $positifTN = $confusionMatrix[1][0];
        // dd($positifTN); //array multidimensional array 1 dan isinya array ke 0 (true negatif)
        $positifFN = $confusionMatrix[1][2];
        // dd($positifFN); //array multidimensional array 1 dan isinya array ke 2 (false negatif)

        //negatif
        $negatifTP = $confusionMatrix[0][0];
        // dd($negatifTP); //array multidimensional array 0 dan isinya array ke 0 (true positif)
        $negatifTN = $confusionMatrix[0][1];
        // dd($negatifTN); //array multidimensional array 0 dan isinya array ke 1 (true negatiff)
        $negatifFN = $confusionMatrix[0][2];
        // dd($negatifFN); //array multidimensional array 0 dan isinya array ke 0 (false negatif)

        $netralTP = $confusionMatrix[2][2];
        // dd($netralTP); //array multidimensional array 2 dan isinya array ke 2 (true positif)
        $netralTN = $confusionMatrix[2][0];
        // dd($netralTN); //array multidimensional array 2 dan isinya array ke 0 (true negatif)
        $netralFN = $confusionMatrix[2][1];
        // dd($netralFN); //array multidimensional array 2 dan isinya array ke 1 (false negatif)

        $truePositif = $positifTP + $negatifTP + $netralTP; // Data True dan benar (TP)
        $trueNegative = $positifTN + $negatifTN + $netralTN; // Data Salah dan benar (TN)
        $falseNegative = $positifFN + $negatifFN + $netralFN; // Data Salah dan negatif (FN) 

        $akurasiTFIDF = Accuracy::score($HasilTesDataTFIDF, $HasilPrediksiDataTFIDF);
        // $akurasiTFIDF = $truePositif / ($truePositif + $trueNegative + $falseNegative); // TruePositif / All Data
        $presisiPositif = @($positifTP / ($positifTP + $positifTN));
        $presisiNegatif = @($negatifTP / ($negatifTP + $negatifTN));
        $presisiNetral = @($netralTP / ($netralTP + $netralTN));
        $presisi = ($presisiPositif + $presisiNegatif + $presisiNetral) / 3; // All Presisi / Jumlah Kelas
        $presisiTFIDF = round(@($presisi), 2);
        $recallPositif = @($positifTP / ($positifTP + $positifFN)); // True Positif / TP + FN
        $recallNegatif = @($negatifTP / ($negatifTP + $negatifFN)); // True Positif / TP + FN
        $recallNetral = @($netralTP / ($netralTP + $netralFN)); // True Positif / TP + FN
        $recall = ($recallPositif + $recallNegatif + $recallNetral) / 3; // All Recall / Jumlah Kelas
        $recallTFIDF =  round(@($recall), 2);
        ConfusionMatrixTFIDF::query()->truncate();
        ConfusionMatrixTFIDF::create([
            'key' => 'Akurasi',
            'value' => $akurasiTFIDF,
        ]);
        ConfusionMatrixTFIDF::create([
            'key' => 'Presisi',
            'value' => $presisiTFIDF,
        ]);
        ConfusionMatrixTFIDF::create([
            'key' => 'Recall',
            'value' => $recallTFIDF
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
    public function searchValue($kalimat_dipisah_perkata, $kata_kunci)
    {
        // dd($arr); //explode kalimat   0 => "perintah"1 => "jamin"2 => "huni"3 => "ikn"4 => "nusantara"5 => "aman"
        // dd($keyword); //menampilkan $k = perintah
        foreach ($kalimat_dipisah_perkata as $key => $value) {
            if ($value == $kata_kunci) {
                return true;
            }
        }
        return false;
    }

    private function beratdarikata($data, $dataset2) //maksute iki piye
    {
        // dd($data);
        $result = [];
        $index = 0;
        $newData = implode(' ', $data); // kata dijadikan kalimat
        foreach ($dataset2 as $key => $value) {
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
