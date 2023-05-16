<?php

namespace App\Http\Controllers;

use App\Dataset;
use App\DatasetPerBulan;
use Session;
// use Illuminate\Http\UploadedFile;
use App\Imports\DatasetImport;
use App\Imports\DatasetImportPerBulan;
use Facade\FlareClient\Stacktrace\File;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Foundation\Validation\ValidatesRequests;

class DatasetController extends Controller
{
    public function dataset()
    {
        $dataset = Dataset::all();
        return view('content.dataset', ['dataset' => $dataset]);
    }
    public function import_excel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xls,xlsx,txt',
        ]);

        // menangkap file excel
        $file = $request->file('file');

        // membuat nama file unik
        $nama_file = rand() . $file->getClientOriginalName();

        $file_path = app_path() . '/file_dataset/';

        if (file_exists($file_path)) { // Apakah ada file
            unlink($file_path); // Hapus file
        }

        Dataset::query()->truncate(); // hapus data di database

        // upload ke folder file_siswa di dalam folder public
        $file->move('file_dataset', $nama_file);

        // import data
        Excel::import(new DatasetImport, public_path('/file_dataset/' . $nama_file));
        //session sukses
        Session::flash('sukses', 'Dataset Berhasil Diimport!');
        // alihkan halaman kembali
        return redirect('/dataset');
    }
    public function datasetperbulan()
    {
        $dataset = DatasetPerBulan::all();
        return view('content.datasetperbulan', ['dataset' => $dataset]);
    }
    public function import_excel_perbulan(Request $request)
    {

        $request->validate([
            'file' => 'required|mimes:csv,xls,xlsx,txt',
        ]);

        // menangkap file excel
        $file = $request->file('file');

        // membuat nama file unik
        $nama_file = rand() . $file->getClientOriginalName();

        $file_path = app_path() . '/file_dataset/';

        if (file_exists($file_path)) { // Apakah ada file
            unlink($file_path); // Hapus file
        }

        DatasetPerBulan::query()->truncate(); // hapus data di database

        // upload ke folder file_siswa di dalam folder public
        $file->move('file_dataset', $nama_file);

        // import data
        Excel::import(new DatasetImportPerBulan, public_path('/file_dataset/' . $nama_file));
        //session sukses
        Session::flash('sukses', 'Dataset Berhasil Diimport!');
        // alihkan halaman kembali
        return redirect('/datasetperbulan');
    }
}
