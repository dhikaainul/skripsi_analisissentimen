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

class KlasifikasiController extends Controller
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
        // Data Trining
        
        $dataset = DB::table('preprocessing_datas')->limit(800)->get();
        // $dataset = Preprocessing::with('dataset')->limit(800)->get();
        $kategoriData = $dataMentah = [];
        foreach ($dataset as $key => $value) {
            $dataMentah[] = $this->ambil_kata_unik(explode(' ', $value->hasil_preprocessing_data)); // Menghapus kata" yang sama dan mengembalikan kata-kata unik //array multidimensional
            if ($value->dataset_kategori == 1) { //cek kategori data == 1 (Positif)
                $kategoriData[] = '1'; //maka array kategoriData[] = 1
            } else if ($value->dataset_kategori == 2) { //cek kategori data == 2 (Negatif)
                $kategoriData[] = '2'; //maka array kategoriData[] =2
            } else if ($value->dataset_kategori == 0) { //cek kategori data == 0 (Netral)
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
        // dd($hasilDataset);
        $TF = [];
        // dd($dataMentah);
        
        foreach ($dataMentah as $key => $value) {
            $TF[] = $this->beratdarikata($value, $hasilDataset); // menghitung nilai per kata 
        }
        // dd($TF); // 0 => array:513 [▼ "perintah" => 1 "jamin" => 1 "huni" => 1 "ikn" => 1 "nusantara" => 1 "aman" => 1 100 => 1 "hadir" => 0
        $probabilitastiapkata = $jumlahKataPerkategori = $df = $idf = $tfidf = $conditionalprobability = [
            '1' => [],
            '0' => [],
            '2' => [],
        ];

        $totalIDF = 0;

        // Jumlah Seluruh Kata Per Kategori

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

        // Jumlah Kata Semua Kategori

        foreach ($TF as $key => $value) {
            foreach ($value as $kata => $val) { // $val = value nilai dari kata
                if ($val >= 1) { // jika $val lebih dari atau sama dengan 1
                    if (empty($df['1'][$kata])) { //jika nilai pada kata kosong maka = 1
                        $df['1'][$kata] = 1;
                    } else {
                        $df['1'][$kata] += 1; //jika nilai kata tidak kosong maka + 1
                    }
                } else {
                    if (empty($df['1'][$kata])) { //jika kata tidak ada atau kosong 
                        $df['1'][$kata] = 0;  // maka = 0
                    }
                }
                if ($val >= 1) { // jika $val lebih dari atau sama dengan 1
                    if (empty($df['0'][$kata])) { //jika nilai pada kata kosong maka = 1
                        $df['0'][$kata] = 1;
                    } else {
                        $df['0'][$kata] += 1; //jika nilai kata tidak kosong maka + 1
                    }
                } else {
                    if (empty($df['0'][$kata])) { //jika kata tidak ada atau kosong 
                        $df['0'][$kata] = 0;  // maka = 0
                    }
                }
                if ($val >= 1) { // jika $val lebih dari atau sama dengan 1
                    if (empty($df['2'][$kata])) { //jika nilai pada kata kosong maka = 1
                        $df['2'][$kata] = 1;
                    } else {
                        $df['2'][$kata] += 1; //jika nilai kata tidak kosong maka + 1
                    }
                } else {
                    if (empty($df['2'][$kata])) { //jika kata tidak ada atau kosong 
                        $df['2'][$kata] = 0;  // maka = 0
                    }
                }
            }
        }
        // dd($df);        

        $probabilitasKategori = $jumlahKategori = $totalSameWord2 = $coba = [
            '0' => [],
            '1' => [],
            '2' => [],
        ];

        // Jumlah Seluruh Data Pada Setiap Kategori

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

        // Hitung Probabilitas Setiap Kategori
        $totaldata = count($dataset);
        foreach ($jumlahKategori as $k => $v) { //$k = 1/0/2 $v = 0 = "exists" => 43 1 = "exists" => 450 2 = "exists" => 7
            foreach ($v as $key => $value) {
                // probablitas dari setiap kategori
                // $probabilitasKategori[$k][$key] = round(($value) / ($totaldata), 4);
                $probabilitasKategori[$k][$key] = ($value) / ($totaldata);
            }
        }
        // dd($probabilitasKategori);

        // Hapus Data Lama dan Simpan Probabilitas Kategori Yang Baru
        ProbabilitasKategori::query()->truncate(); // hapus isi tabel dari probabilitaskategori
        foreach ($probabilitasKategori as $key => $value) {
            ProbabilitasKategori::create([
                'key' => $key,
                'value' => $value['dataset_id']
            ]);
        }
        // dd($probabilitasKategori);
        // dd($jumlahKataPerkategori);

        // Menghitung Probabilitas Tiap Kata Per Kategori

        $totalKata = count($hasilDataset);
        // dd($hasilDataset);
        foreach ($jumlahKataPerkategori as $k => $v) {
            // dd($jumlahKataPerkategori);
            $totalKataSama = array_sum($v);
            foreach ($v as $key => $value) {
                // $totalSameWord2[$k][$key] = array_sum($v);
                $probabilitastiapkata[$k][$key] = round((1 + $value) / ($totalKataSama + $totalKata), 4);
            }
        }
        // dd($totalSameWord2);
        // dd($probabilitastiapkata);

        //Hitung nilai IDF

        $totalSeluruhDataset = count($dataset);
        // dd($totalSeluruhDataset);
        // dd($df);
        foreach ($df as $k => $v) {
            foreach ($v as $key => $value) {
                $idf[$k][$key] = round((log10($totalSeluruhDataset / $value)), 4);
            }
        }
        // dd($idf);
        // dd($jumlahKataPerkategori);
        // dd($jumlahTFIDF);
        // dd($totalSameWord2);

        //Hitung TF-IDF

        foreach ($idf as $k => $v) {
            // dd($v);
            $totalIDF = array_sum($v);
            foreach ($v as $key => $value) {
                $totalSameWord2[$k][$key] = array_sum($v);
                // dd($jumlahKataPerkategori);
                $tfidf[$k][$key] = round(($value * $jumlahKataPerkategori[$k][$key]), 4);
            }
        }
        // dd($tfidf);
        // dd($totalSameWord2);
        // dd($totalIDF);

        // Hitung Conditional Probability atau Probabilitas Tiap Kata TF-IDF

        foreach ($tfidf as $k => $v) {
            // dd($tfidf);
            $totalTFIDFSama = array_sum($v);
            foreach ($v as $key => $value) {
                $totalSameWord2[$k][$key] = array_sum($v);
                // dd($totalIDF);
                $conditionalprobability[$k][$key] = round((1 + $value) / ($totalTFIDFSama + $totalIDF), 4);
            // dd($totalTFIDFSama);
            }
        }
        // dd($totalIDF);
        // dd($totalSameWord2);
        // dd($conditionalprobability);
        // dd($totalSameWord2);
        // dd($normalisasi);

        // Data Uji
        $PrediksiDataAwal = $HasilPrediksiData = $dataNormalisasiUtama = [];
        $countData = 200;

        // Hapus Data ProbabilitasTF dan IDF
        ProbabilitasTF::query()->truncate();
        ProbabilitasTFIDF::query()->truncate();
        for ($i = 0; $i < $countData; $i++) {
            $index = $i;
            $kalimat = $dataset[$index]->hasil_preprocessing_data;
            // dd($kalimat);
            $kalimat_dipisah_perkata = explode(' ', $kalimat);
            $probabilitas = ProbabilitasKategori::all();
            $finalResult = [];
            $finalResultTFIDF = [];
            
            // Hitung Probabilitas kata tiap kelas dengan dikali nilai perbandingan kategori dengan seluruh data
            
            foreach ($probabilitastiapkata as $key => $value) {
                $finalResult[$key] = $probabilitas[$key]->value;
                foreach ($value as $kata_kunci => $v) {
                    if ($this->cekKata($kalimat_dipisah_perkata, $kata_kunci)) {
                        $finalResult[$key] *= $v;
                        // dd($finalResult);
                    }
                }
            }
            // dd($finalResult);
            // dd($kata_kunci);

            // Mengambil data dari dataset
            $dataNormalisasiUtama[] = $dataset[$index];
            $PrediksiDataAwal[] = $dataset[$index]->dataset_kategori;

            // Mencari nilai MAX
            $HasilPrediksiData[] = array_keys($finalResult, max($finalResult))[0];
            // dd($finalResult);

            // Simpan Data Probabilitas TF
            ProbabilitasTF::insert([
                'dataset_id' => $index + 1,
                'probabilitas_positif' => $finalResult[1],
                'probabilitas_netral' => $finalResult[0],
                'probabilitas_negatif' => $finalResult[2],
                'max_value' => max($finalResult),
            ]);

            // Hitung Probabilitas kata tiap kelas dengan dikali nilai perbandingan kategori dengan seluruh data
            foreach ($conditionalprobability as $key => $value) {
                $finalResultTFIDF[$key] = $probabilitas[$key]->value;
                foreach ($value as $kata_kunci => $v) {
                    if ($this->cekKata($kalimat_dipisah_perkata, $kata_kunci)) {
                        $finalResultTFIDF[$key] *= $v;
                        // dd($finalResult);
                    }
                }
            }

            // dd($finalResultTFIDF);
            // Mencari nilai MAX
            $HasilPrediksiDataTFIDF[] = array_keys($finalResultTFIDF, max($finalResultTFIDF))[0];

            // Simpan Data Probabilitas TFIDF
            ProbabilitasTFIDF::insert([
                'dataset_id' => $index + 1,
                'probabilitas_positif' => $finalResultTFIDF[1],
                'probabilitas_netral' => $finalResultTFIDF[0],
                'probabilitas_negatif' => $finalResultTFIDF[2],
                'max_value' => max($finalResultTFIDF),
            ]);
        }

        // Hapus Data Lama dan Simpan Data Klasifikasi Baru di tabel Klasifikasi

        Klasifikasi::query()->truncate();
        foreach ($dataNormalisasiUtama as $key => $value) {
            Klasifikasi::create([
                'dataset_id' => $value->dataset_id,
                'hasil_preprocessing_data' => $value->hasil_preprocessing_data,
                'kategori_dataset' => $PrediksiDataAwal[$key],
                'kategori_prediksi' => $HasilPrediksiData[$key],
                'kategori_prediksi_tfidf' => $HasilPrediksiDataTFIDF[$key]
            ]);
        }

        // Menghitung Confussion Matrix TF

        $confusionMatrix = ConfusionMatrix::compute($PrediksiDataAwal, $HasilPrediksiData, [0, 1, 2]);
        // dd($confusionMatrix);

        $netral1 = $confusionMatrix[0][0];
        $netral2 = $confusionMatrix[0][1];
        $netral3 = $confusionMatrix[0][2];

        $positif1 = $confusionMatrix[1][0];
        $positif2 = $confusionMatrix[1][1];
        $positif3 = $confusionMatrix[1][2];

        $negatif1 = $confusionMatrix[2][0];
        $negatif2 = $confusionMatrix[2][1];
        $negatif3 = $confusionMatrix[2][2];

        $netralTP = $netral1;
        $netralFP = $netral2 + $netral3;
        $netralFN = $positif1 + $negatif1;

        $positifTP = $positif2;
        $positifFP = $positif1 + $positif3;
        $positifFN = $netral2 + $negatif2;

        $negatifTP = $negatif3;
        $negatifFP = $negatif1 + $negatif2;
        $negatifFN = $netral3 + $positif3;

        // Akurasi TF
        $akurasiTF = Accuracy::score($PrediksiDataAwal, $HasilPrediksiData);

        $presisiNetral = $netralTP / ($netralTP + $netralFP);
        $presisiPositif = $positifTP / ($positifTP + $positifFP);
        $presisiNegatif = $negatifTP / ($negatifTP + $negatifFP);

        // Presisi TF
        $presisi = ($presisiPositif + $presisiNegatif + $presisiNetral) / 3; // All Presisi / Jumlah Kelas
        $presisiTF = round(($presisi), 3);
        // dd($presisiTF);

        $recallNetral = $netralTP / ($netralTP + $netralFN);
        $recallPositif = $positifTP / ($positifTP + $positifFN);
        $recallNegatif = $negatifTP / ($negatifTP + $negatifFN);

        // Recall TF
        $recall = ($recallPositif + $recallNegatif + $recallNetral) / 3; // All Recall / Jumlah Kelas
        $recallTF =  round(($recall), 3);

        // F1-Score TF
        $f1ScoreTF = round((2 * $presisiTF * $recallTF) / ($presisiTF + $recallTF), 3);

        // Hapus Data Lama dan Simpan Data Di Tabel ConfussionMatrix TF

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
        ConfusionMatrixTF::create([
            'key' => 'F-1 Score',
            'value' => $f1ScoreTF
        ]);

        // Menghitung Confussion Matrix TF-IDF

        $confusionMatrix2 = ConfusionMatrix::compute($PrediksiDataAwal, $HasilPrediksiDataTFIDF, [0, 1, 2]);
        // dd($confusionMatrix2);

        $netral1 = $confusionMatrix2[0][0];
        $netral2 = $confusionMatrix2[0][1];
        $netral3 = $confusionMatrix2[0][2];

        $positif1 = $confusionMatrix2[1][0];
        $positif2 = $confusionMatrix2[1][1];
        $positif3 = $confusionMatrix2[1][2];

        $negatif1 = $confusionMatrix2[2][0];
        $negatif2 = $confusionMatrix2[2][1];
        $negatif3 = $confusionMatrix2[2][2];

        $netralTP = $netral1;
        // dd($netralTP);
        $netralFP = $netral2 + $netral3;
        // dd($netralFP);
        $netralFN = $positif1 + $negatif1;
        // dd($netralFN);

        $positifTP = $positif2;
        $positifFP = $positif1 + $positif3;
        $positifFN = $netral2 + $negatif2;

        $negatifTP = $negatif3;
        $negatifFP = $negatif1 + $negatif2;
        $negatifFN = $netral3 + $positif3;

        // Akurasi TF-IDF
        $akurasiTFIDF = Accuracy::score($PrediksiDataAwal, $HasilPrediksiDataTFIDF);

        $presisiNetral = $netralTP / ($netralTP + $netralFP);
        $presisiPositif = $positifTP / ($positifTP + $positifFP);
        $presisiNegatif = $negatifTP / ($negatifTP + $negatifFP);

        // Presisi TF-IDF
        $presisi = ($presisiPositif + $presisiNegatif + $presisiNetral) / 3; // All Presisi / Jumlah Kelas
        $presisiTFIDF = round(($presisi), 3);

        $recallNetral = $netralTP / ($netralTP + $netralFN);
        $recallPositif = $positifTP / ($positifTP + $positifFN);
        $recallNegatif = $negatifTP / ($negatifTP + $negatifFN);

        // Recall TF-IDF
        $recall = ($recallPositif + $recallNegatif + $recallNetral) / 3; // All Recall / Jumlah Kelas
        $recallTFIDF =  round(($recall), 3);
        // dd($recallTFIDF);

        // F1-Score TF-IDF
        $f1ScoreTFIDF = round((2 * $presisiTFIDF * $recallTFIDF) / ($presisiTFIDF + $recallTFIDF), 3);
        // dd($f1ScoreTFIDF);

        // Hapus Data Lama dan Simpan Data Di Tabel ConfussionMatrix TF=IDF
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
        ConfusionMatrixTFIDF::create([
            'key' => 'F-1 Score',
            'value' => $f1ScoreTFIDF
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

    // Cek Setiap Kata Pada Kata Dikalimat Preprocessing
    public function cekKata($kalimat_dipisah_perkata, $kata_kunci)
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

    // Menghitung Berat Dari Kata Pada Setiap Putaran Looping Kata Yang Ada Dikalimat
    private function beratdarikata($data, $dataset2)
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
