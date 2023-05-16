<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('content.dataset');
// });
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', 'DashboardController@index')->name('dashboard');
    Route::get('/dashboardperbulan', 'DashboardController@indexperbulan')->name('dashboardperbulan');
    Route::get('/dataset', 'DatasetController@dataset')->name('dataset');
    Route::get('/preprocessing', 'PreprocessingController@datapreprocessing')->name('preprocessing');
    Route::get('/proses-preprocessing', 'PreprocessingController@preprocessing')->name('proses-preprocessing');
    Route::get('/klasifikasi', 'KlasifikasiController@dataklasifikasi')->name('klasifikasi');
    Route::get('/proses-klasifikasi', 'KlasifikasiController@klasifikasi')->name('proses-klasifikasi');
    // Route::get('/dataset', 'DatasetController@index');
    // Route::get('/siswa/export_excel', 'SiswaController@export_excel');
    Route::post('/dataset/import_excel', 'DatasetController@import_excel')->name('import_excel');

    Route::get('/datasetperbulan', 'DatasetController@datasetperbulan')->name('datasetperbulan');
    Route::get('/preprocessingperbulan', 'PreprocessingPerBulanController@datapreprocessingperbulan')->name('preprocessingperbulan');
    Route::get('/proses-preprocessing-perbulan', 'PreprocessingPerBulanController@preprocessing')->name('proses-preprocessing-perbulan');
    Route::get('/klasifikasiperbulan', 'KlasifikasiPerBulanController@dataklasifikasiperbulan')->name('klasifikasiperbulan');
    Route::get('/proses-klasifikasi-perbulan', 'KlasifikasiPerBulanController@klasifikasi')->name('proses-klasifikasi-bulan');    Route::post('/dataset/import_excel', 'DatasetController@import_excel')->name('import_excel');
    Route::post('/dataset/import_excel_perbulan', 'DatasetController@import_excel_perbulan')->name('import_excel_perbulan');
    // Route::get('/pesan/sukses','DatasetController@sukses');
});
//--------Authentication--------
Route::get('/', 'AuthController@login')->name('login');
Route::get('/login', 'AuthController@viewlogin')->name('viewlogin');
Route::get('/register', 'AuthController@viewregister')->name('viewregister');
Route::post('/simpanregistrasi', 'AuthController@store')->name('simpanregistrasi');
Route::post('/loginpost', 'AuthController@loginPost')->name('loginpost');
Route::get('/logout', 'AuthController@logout');
