<?php

namespace App\Http\Controllers;

use App\Sentimen;
use Illuminate\Http\Request;
use DB;

class DashboardController extends Controller
{
    public function index()
    {
        $positif = DB::table('klasifikasis')->where('kategori_dataset', '1')->count();
        $netral = DB::table('klasifikasis')->where('kategori_dataset', '0')->count();
        $negatif = DB::table('klasifikasis')->where('kategori_dataset', '2')->count();
        $positifTF = DB::table('klasifikasis')->where('kategori_prediksi', '1')->count();
        $netralTF = DB::table('klasifikasis')->where('kategori_prediksi', '0')->count();
        $negatifTF = DB::table('klasifikasis')->where('kategori_prediksi', '2')->count();
        $positifTFIDF = DB::table('klasifikasis')->where('kategori_prediksi_tfidf', '1')->count();
        $netralTFIDF = DB::table('klasifikasis')->where('kategori_prediksi_tfidf', '0')->count();
        $negatifTFIDF = DB::table('klasifikasis')->where('kategori_prediksi_tfidf', '2')->count();
        $akurasiTF = DB::table('confusion_matrixs_tf')->where('key', 'Akurasi')->value('value');
        $presisiTF = DB::table('confusion_matrixs_tf')->where('key', 'Presisi')->value('value');
        $recallTF = DB::table('confusion_matrixs_tf')->where('key', 'Recall')->value('value');
        $akurasiTFIDF = DB::table('confusion_matrixs_tfidf')->where('key', 'Akurasi')->value('value');
        $presisiTFIDF = DB::table('confusion_matrixs_tfidf')->where('key', 'Presisi')->value('value');
        $recallTFIDF = DB::table('confusion_matrixs_tfidf')->where('key', 'Recall')->value('value');
        return view('content.dashboardutama', compact('positif', 'negatif', 'netral', 'positifTF', 'negatifTF', 'netralTF', 'positifTFIDF', 'negatifTFIDF', 'netralTFIDF', 'akurasiTF', 'presisiTF', 'recallTF', 'akurasiTFIDF', 'presisiTFIDF', 'recallTFIDF'));
    }
    public function indexperbulan()
    {
        $positifFebruari = DB::table('klasifikasi_perbulans')->where('dataset_month', 'Februari')->where('kategori_prediksi_tfidf', '1')->count();
        $netralFebruari = DB::table('klasifikasi_perbulans')->where('dataset_month', 'Februari')->where('kategori_prediksi_tfidf', '0')->count();
        $negatifFebruari = DB::table('klasifikasi_perbulans')->where('dataset_month', 'Februari')->where('kategori_prediksi_tfidf', '2')->count();
        $positifMaret = DB::table('klasifikasi_perbulans')->where('dataset_month', 'Maret')->where('kategori_prediksi_tfidf', '1')->count();
        $netralMaret = DB::table('klasifikasi_perbulans')->where('dataset_month', 'Maret')->where('kategori_prediksi_tfidf', '0')->count();
        $negatifMaret = DB::table('klasifikasi_perbulans')->where('dataset_month', 'maret')->where('kategori_prediksi_tfidf', '2')->count();
        $positifApril = DB::table('klasifikasi_perbulans')->where('dataset_month', 'April')->where('kategori_prediksi_tfidf', '1')->count();
        $netralApril = DB::table('klasifikasi_perbulans')->where('dataset_month', 'April')->where('kategori_prediksi_tfidf', '0')->count();
        $negatifApril = DB::table('klasifikasi_perbulans')->where('dataset_month', 'April')->where('kategori_prediksi_tfidf', '2')->count();
        $positifMei = DB::table('klasifikasi_perbulans')->where('dataset_month', 'Mei')->where('kategori_prediksi_tfidf', '1')->count();
        $netralMei = DB::table('klasifikasi_perbulans')->where('dataset_month', 'Mei')->where('kategori_prediksi_tfidf', '0')->count();
        $negatifMei = DB::table('klasifikasi_perbulans')->where('dataset_month', 'Mei')->where('kategori_prediksi_tfidf', '2')->count();
        // dd($netralFebruari);
        Sentimen::query()->truncate();
        Sentimen::create([
            'jumlah_positif' => $positifFebruari,
            'jumlah_netral' => $netralFebruari,
            'jumlah_negatif' => $negatifFebruari,
            'bulan' => 'Februari'
        ]);
        Sentimen::create([
            'jumlah_positif' => $positifMaret,
            'jumlah_netral' => $netralMaret,
            'jumlah_negatif' => $negatifMaret,
            'bulan' => 'Maret'
        ]);
        Sentimen::create([
            'jumlah_positif' => $positifApril,
            'jumlah_netral' => $netralApril,
            'jumlah_negatif' => $negatifApril,
            'bulan' => 'April'
        ]);
        Sentimen::create([
            'jumlah_positif' => $positifMei,
            'jumlah_netral' => $netralMei,
            'jumlah_negatif' => $negatifMei,
            'bulan' => 'Mei'
        ]);

        $positif = DB::table('sentimens')->max('jumlah_positif');
        $negatif = Sentimen::max('jumlah_negatif');
        $netral = Sentimen::max('jumlah_netral');

        $bulanPositif = DB::table('sentimens')->where('jumlah_positif', $positif)->value('bulan');
        $bulanNegatif = DB::table('sentimens')->where('jumlah_negatif', $negatif)->value('bulan');
        $bulanNetral = DB::table('sentimens')->where('jumlah_netral', $netral)->value('bulan');
        return view('content.dashboardperbulan', compact('positifFebruari', 'negatifFebruari', 'netralFebruari', 'positifMaret', 'negatifMaret', 'netralMaret', 'positifApril', 'negatifApril', 'netralApril', 'positifMei', 'negatifMei', 'netralMei', 'positif', 'negatif', 'netral', 'bulanPositif', 'bulanNegatif', 'bulanNetral'));
    }
}
