@extends('master')
@section('title','Klasifikasi Data Naive Bayes')
@section('content')
<main id="main" class="main">
  <div class="pagetitle">
    <h1>Tabel Klasifikasi Naive Bayes</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item active">Klasifikasi Naive Bayes</li>
      </ol>
    </nav>
  </div><!-- End Page Title -->

  {{-- notifikasi sukses --}}
  @if ($message = Session::get('sukses'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-1"></i>
    <strong>{{ $message }}</strong>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  <section class="section">
    <div class="row">
      <div class="col-lg-12">

        <div class="card">
          <div class="card-header">
            <a class="btn btn-outline-primary" href="/proses-klasifikasi" role="button">Klasifikasi Data</a>
          </div>
          <div class="card-body">
            <!-- <h5 class="card-title">Dataset</h5> -->
            <!-- <p>Add lightweight datatables to your project with using the <a href="https://github.com/fiduswriter/Simple-DataTables" target="_blank">Simple DataTables</a> library. Just add <code>.datatable</code> class name to any table you wish to conver to a datatable</p> -->
            <!-- Table with stripped rows -->
            <table class="table datatable">
              <thead>
                <tr>
                  <th width="1px" scope="col">No</th>
                  <th class="text-center" width="300px" scope="col">Text</th>
                  <th class="text-center" width="1px" scope="col">Sentimen Manual</th>
                  <th class="text-center" width="1px" scope="col">Sentimen TF</th>
                  <th class="text-center" width="1px" scope="col">Sentimen TF-IDF</th>
                </tr>
              </thead>
              <tbody>
                @php $i=1 @endphp
                @foreach($klasifikasi as $s)
                <tr>
                  <th scope="row">{{ $i++ }}</th>
                  <td>{{$s->hasil_preprocessing_data}}</td>
                  @if($s->kategori_dataset== '1')
                  <td class="text-center"><span class="badge bg-success">POSITIF</span></td>
                  @endif
                  @if($s->kategori_dataset == '0')
                  <td class="text-center"><span font-size: 200px class="badge bg-info">NETRAL</span></td>
                  @endif
                  @if($s->kategori_dataset == '2')
                  <td class="text-center"><span class="badge bg-danger">NEGATIF</span></td>
                  @endif
                  @if($s->kategori_prediksi == '1')
                  <td class="text-center"><span class="badge bg-success">POSITIF</span></td>
                  @endif
                  @if($s->kategori_prediksi == '0')
                  <td class="text-center"><span font-size: 200px class="badge bg-info">NETRAL</span></td>
                  @endif
                  @if($s->kategori_prediksi == '2')
                  <td class="text-center"><span class="badge bg-danger">NEGATIF</span></td>
                  @endif
                  @if($s->kategori_prediksi_tfidf == '1')
                  <td class="text-center"><span class="badge bg-success">POSITIF</span></td>
                  @endif
                  @if($s->kategori_prediksi_tfidf == '0')
                  <td class="text-center"><span font-size: 200px class="badge bg-info">NETRAL</span></td>
                  @endif
                  @if($s->kategori_prediksi_tfidf == '2')
                  <td class="text-center"><span class="badge bg-danger">NEGATIF</span></td>
                  @endif
                </tr>
                @endforeach
              </tbody>
            </table>
            <!-- End Table with stripped rows -->
          </div>
        </div>
      </div>
    </div>
  </section>
  <div class="row">
    <div class="col-sm-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Klasifikasi Naive Bayes Menggunakan TF</h5>
          <table class="table table-hover">
            <thead>
              <tr>
                <th scope="col">No</th>
                <th scope="col">Confussion Matrixs</th>
                <th scope="col">Nilai Persentase</th>
              </tr>
            </thead>
            <tbody>
              @php $i=1 @endphp
              @foreach($matrixtf as $s)
              <tr>
                <th scope="row">{{ $i++ }}</th>
                <td>{{ $s->key }}</td>
                <td>{{$s->value*100}} %</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-sm-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Klasifikasi Naive Bayes Menggunakan TF-IDF</h5>
          <table class="table table-hover">
            <thead>
              <tr>
                <th scope="col">No</th>
                <th scope="col">Confussion Matrixs</th>
                <th scope="col">Nilai Persentase</th>
              </tr>
            </thead>
            <tbody>
              @php $i=1 @endphp
              @foreach($matrixtfidf as $s)
              <tr>
                <th scope="row">{{ $i++ }}</th>
                <td>{{ $s->key }}</td>
                <td>{{$s->value*100}} %</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</main>
@endsection