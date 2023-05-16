<?php

namespace App\Http\Controllers;

use App\Klasifikasi;
use App\KlasifikasiPerBulan;
use App\Preprocessing;
use App\PreprocessingPerBulan;
use App\ProbabilitasKategori;
use App\Traits\KelolaDataTrait;
use Illuminate\Http\Request;
use DB;

class KlasifikasiPerBulanController extends Controller
{

    use KelolaDataTrait;

    public function dataklasifikasiperbulan()
    {
        $klasifikasi = KlasifikasiPerBulan::all();
        return view('content.klasifikasiperbulan', ['klasifikasi' => $klasifikasi]);
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
        $TF = [];
        // dd($dataMentah);
        foreach ($dataMentah as $key => $value) {
            $TF[] = $this->beratdarikata($value, $hasilDataset); // menghitung nilai per kata 
        }
        // dd($TF); // 0 => array:513 [▼ "perintah" => 1 "jamin" => 1 "huni" => 1 "ikn" => 1 "nusantara" => 1 "aman" => 1 100 => 1 "hadir" => 0
        $probabilitastiapkata = $jumlahKataPerkategori = $jumlahTFIDF = $df = $idf = $tfidf = $conditionalprobability = [
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
                $probabilitasKategori[$k][$key] = round(($value) / ($totaldata), 4);
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
        // dd($sumWordFlag);
        // dd($df);

        //Hitung nilai IDF

        $totalSeluruhDataset = count($dataset);
        // dd($totalSeluruhDataset);
        foreach ($df as $k => $v) {
            // $totalKataSama = array_sum($v);
            foreach ($v as $key => $value) {
                // $totalSameWord2[$k][$key] = array_sum($v);
                $idf[$k][$key] = round((log10($totalSeluruhDataset / $value)), 4);
            }
        }
        // dd($idf);
        // dd($jumlahKataPerkategori);
        // dd($idf);
        // dd($jumlahTFIDF);

        //Hitung TF-IDF

        foreach ($idf as $k => $v) {
            // dd($v);
            $totalIDF = array_sum($v);
            foreach ($v as $key => $value) {
                // $totalSameWord2[$k][$key] = array_sum($v);
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
                // $totalSameWord2[$k][$key] = array_sum($v);
                $conditionalprobability[$k][$key] = round((1 + $value) / ($totalTFIDFSama + $totalIDF), 4);
            }
        }
        // dd($totalIDF);
        // dd($totalSameWord2);
        // dd($conditionalprobability);
        // dd($totalSameWord2);
        // dd($normalisasi);

        // Data Uji

        $HasilTesData = $HasilPrediksiData = $dataNormalisasiUtama = [];
        $dataset2 = PreprocessingPerBulan::all();
        $countData = count($dataset2);

        for ($i = 0; $i < $countData; $i++) {
            $index = $i;
            $kalimat = $dataset2[$index]->hasil_preprocessing_data;
            // dd($kalimat);
            $kalimat_dipisah_perkata = explode(' ', $kalimat);
            $probabilitas = ProbabilitasKategori::all();
            $finalResult = [];
            $finalResultTFIDF = [];

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

            // Mengambil data dari dataset
            $dataNormalisasiUtama[] = $dataset2[$index];

            // Mencari nilai MAX
            $HasilPrediksiDataTFIDF[] = array_keys($finalResultTFIDF, max($finalResultTFIDF))[0];
        }

        // Hapus Data Lama dan Simpan Data Klasifikasi Baru di tabel Klasifikasi
        KlasifikasiPerBulan::query()->truncate();
        foreach ($dataNormalisasiUtama as $key => $value) {
            KlasifikasiPerBulan::create([
                'dataset_id' => $value->dataset_id,
                'dataset_month' => $value->dataset_month,
                'dataset_date' => $value->dataset_date,
                'hasil_preprocessing_data' => $value->hasil_preprocessing_data,
                'kategori_prediksi_tfidf' => $HasilPrediksiDataTFIDF[$key]
            ]);
        }
        return redirect()->route('klasifikasiperbulan')->with('sukses', 'Data Preprocessing Berhasil Di Klasifikasi');
    }

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
