<?php

namespace App\Http\Controllers;

use App\Dataset;
use App\Preprocessing;
use App\Traits\KelolaDataTrait;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Sastrawi\Stemmer\StemmerFactory;
use Sastrawi\StopWordRemover\StopWordRemoverFactory;
use Session;

class PreprocessingController extends Controller
{
    use KelolaDataTrait; // memanggil trait

    public function datapreprocessing()
    {
        $preprocessing = Preprocessing::all();
        // dd($preprocessing);
        return view('content.preprocessing', ['preprocessing' => $preprocessing]);
    }
    public function preprocessing()
    {
        DB::beginTransaction();
        try {
            $dataset = Dataset::all(); //menggabungkan keseluruhan function
            $case_folding = $this->case_folding($dataset); //setiap result berbeda function 
            $cleansing = $this->cleansing($case_folding); //dan dijadikan 1 untuk dapat saling terhubung satu sama lain
            $stemming = $this->stemming($cleansing);
            $stopword_removal = $this->stopword_removal($stemming);
            Preprocessing::query()->truncate();  //Hapus semua data preprocesiing
            // dd($data2);
            foreach ($stopword_removal as $key => $value) {
                // dd($value['text']);
                Preprocessing::create([
                    'dataset_id' => $value['id'],
                    'hasil_preprocessing_data' => $value['text'],
                    'dataset_author' =>$value['author'],
                    'dataset_kategori' =>$value['kategori']
                ]); // Insert ke database
            }
            DB::commit(); // lakukan query
            // Dengan menggunakan COMMIT, kita dapat mengakhiri semua transaksi dan menjadikannya sebagai perubahan permanen.
            return redirect()->route('preprocessing')->with('sukses', 'Dataset Berhasil Di Preprocessing');
        } catch (\Exception $e) {
            DB::rollBack(); // kembalikan query jika ada yang gagal
            // Dengan menggunakan ROLLBACK, kita bisa melompat ke keadaan terakhir dari sebuah transaksi yang telah di-commit sehingga update query berikut tidak akan tercatat di dalam transaksi.
            return redirect()->back()->with('gagal', $e->getMessage());
        }
    }


    public function case_folding($dataset)
    {
        $result = [];
        foreach ($dataset as $key => $value) { //mengambil data dari dataset dan dilakukan perulangan foreach
            $temp = [];
            $temp['id'] = $value->id;
            $temp['author'] = $value->author;
            $temp['kategori'] = $value->kategori;
            $temp['text'] = trim(preg_replace('/\s+/', ' ', Str::lower($value->text)), ' '); // merubah huruf menjadi huruf kecil
        //    dd($temp['text']);
            array_push($result, $temp); // push ke array
        }
        return $result;
    }

    public function cleansing($dataset)
    {
        $result = [];
        foreach ($dataset as $key => $value) {
            $temp = [];
            // menghapus Nomer di data
            $hapus_nomer = preg_replace('/[0-9]+/', '', $value['text']);
            // dd($hapus_nomer);
            $hapus_karakter = htmlentities($value['text'], ENT_COMPAT, 'utf-8'); //menampilkan nama karakter html
            // dd($hapus_karakter);
            $hapus_karakter = preg_replace('/&(mdash|pound|eth|yuml|yen|deg|curren|iuml|acirc|bdquo|hellip|acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);/i', '', $hapus_karakter); //menghapus karakter html
            $array_perkalimat = explode(' ', $hapus_karakter);
            // dd($array_perkalimat);
            $hapus_url = [];
            // Menghapus URL 
            foreach ($array_perkalimat as $k => $v) {
                $regex_url = "@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?).*$)@";
                $hapus_url[] = preg_replace($regex_url, ' ', $v);
            }
            // dd($temp_url);
            $hasil_hapus_url = implode(" ", $hapus_url);

            // dd($hasil_hapus_url);
            $hastagathttps = [];
            $test = explode(' ', $hasil_hapus_url);
            for ($i = 0; $i < count($test); $i++) {
                $cek = (strchr($test[$i], "#"));
                $cek2 = (strchr($test[$i], "@"));
                $cek3 = (strchr($test[$i], "https"));
                if ($cek == null && $cek2 == null && $cek3 == null) {
                    array_push($hastagathttps, $test[$i]);
                }
            }
            // $new2 = array_filter($hastag);
            $hapus_karakter = implode(" ", $hastagathttps); // Hasil data di hilangkan #, @ dan https
            // dd($hapus_karakter);
            $hapus_karakter = preg_replace(array('/[^a-z0-9]/i', '/[-]+/'), '-', $hapus_karakter);
            // dd($hapus_karakter);
            // $clear_character = str_replace('rt', ' ', $clear_character);
            $hapus_karakter = str_replace('-', ' ', $hapus_karakter);
            // dd($hapus_karakter);
            $hapus_karakter = trim(str_replace('/\s+/', ' ', $hapus_karakter), ' '); //menghapus ruang kosong pada value text
            // dd($hapus_karakter);
            $temp['id'] = $value['id'];
            $temp['author'] = $value['author'];
            $temp['kategori'] = $value['kategori'];
            $temp['text'] = $hapus_karakter;
            array_push($result, $temp);
        }
        return $result;
    }
    public function stemming($dataset) // merubah kata menjadi kata dasar
    {
        $stemmerFactory = new StemmerFactory();
        $stemmer  = $stemmerFactory->createStemmer();
        $result = [];

        foreach ($dataset as $key => $value) {
            $temp = [];
            $temp['id'] = $value['id'];
            $temp['author'] = $value['author'];
            $temp['kategori'] = $value['kategori'];
            $temp['text'] = $stemmer->stem($value['text']);
            // dd($temp['text']);
            array_push($result, $temp);
        }
        return $result;
    }
    public function stopword_removal($dataset) // menghapus kata-kata yang tidak diperlukan
    {
        $stopremovalFactory = new StopWordRemoverFactory();
        $stopremoval  = $stopremovalFactory->createStopWordRemover();
        $result = [];

        foreach ($dataset as $key => $value) {
            $temp = [];
            $temp['id'] = $value['id'];
            $temp['author'] = $value['author'];
            $temp['kategori'] = $value['kategori'];
            $temp['text'] = $stopremoval->remove($value['text']);
            // dd($temp['text']);
            array_push($result, $temp);
        }
        return $result;
    }
}
