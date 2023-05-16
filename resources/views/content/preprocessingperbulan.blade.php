@extends('master')
@section('title','Preprocessing Dataset')
@section('content')
<main id="main" class="main">
  <div class="pagetitle">
    <h1>Tabel Preprocessing Data</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item">Klasifikasi Per Bulan</li>
        <li class="breadcrumb-item active">Preprocessing</li>
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

  {{-- notifikasi gagal --}}
  @if ($message = Session::get('gagal'))
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-octagon me-1"></i>
    <strong>{{ $message }}</strong>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-header">
            <a class="btn btn-outline-primary" href="/proses-preprocessing-perbulan" role="button">Preprocessing Data</a>
          </div>
          <div class="card-body">
            <!-- <h5 class="card-title">Dataset</h5> -->
            <!-- <p>Add lightweight datatables to your project with using the <a href="https://github.com/fiduswriter/Simple-DataTables" target="_blank">Simple DataTables</a> library. Just add <code>.datatable</code> class name to any table you wish to conver to a datatable</p> -->
            <!-- Table with stripped rows -->
            <table class="table datatable">
              <thead>
                <tr>
                  <th width="1px" scope="col">No</th>
                  <th class="text-center" width="1px" scope="col">Author</th>
                  <th class="text-center" width="300px" scope="col">Hasil Preprocessing</th>
                  <!-- <th scope="col">Platform</th> -->
                  <th width="1px" scope="col">Month</th>
                  <th class="text-center" width="1px" scope="col">Date</th>
                </tr>
              </thead>
              <tbody>
                <!-- {{ $preprocessing }} -->
                @php $i=1 @endphp
                @foreach($preprocessing as $s)
                <!-- {{ $s }} -->
                <tr>
                  <th scope="row">{{ $i++ }}</th>
                  <td class="text-center">{{$s->dataset_author}}</td>
                  <td>{{$s->hasil_preprocessing_data}}</td>
                  <td>{{$s->dataset_month}}</td>
                  <td>{{$s->dataset_date}}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
            <!-- End Table with stripped rows -->
          </div>
        </div>
        <!-- Import Excel -->
        <div class="modal fade" id="importExcel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <form method="post" action="/dataset/import_excel" enctype="multipart/form-data">
              <!-- <form> -->
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Import Excel</h5>
                </div>
                <div class="modal-body">

                  {{ csrf_field() }}

                  <label>Pilih file excel</label>
                  <div class="form-group">
                    <input type="file" name="file" required="required" id="file">
                  </div>

                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary">Import</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

@endsection