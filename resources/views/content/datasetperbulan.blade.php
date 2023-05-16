@extends('master')
@section('title','Dataset')
@section('content')

<!-- <style type="text/css">
  #loader {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    background: rgba(0, 0, 0, 0.75) url(images/loading2.gif) no-repeat center center;
    z-index: 10000;
  }
</style> -->
<main id="main" class="main">
  <div class="pagetitle">
    <h1>Tabel Dataset</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item">Klasifikasi Per Bulan</li>
        <li class="breadcrumb-item active">Dataset</li>
      </ol>
    </nav>
  </div><!-- End Page Title -->
  {{-- notifikasi form validasi --}}

  @if (count($errors) > 0)
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-octagon me-1"></i>
    @foreach ($errors->all() as $error)
    <strong>{{ $error }}</strong>
    @endforeach
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

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
            <button class="btn btn-outline-primary" data-toggle="modal" data-target="#importExcel">Import Dataset</button>
          </div>
          <div class="card-body">
            <!-- <h5 class="card-title">Dataset</h5> -->
            <!-- <p>Add lightweight datatables to your project with using the <a href="https://github.com/fiduswriter/Simple-DataTables" target="_blank">Simple DataTables</a> library. Just add <code>.datatable</code> class name to any table you wish to conver to a datatable</p> -->
            <!-- Table with stripped rows -->
            <table class="table datatable">
              <thead>
                <tr>
                  <th width="1px" scope="col">No</th>
                  <th width="1px" scope="col">Author</th>
                  <th class="text-center" width="300px" scope="col">Tweet</th>
                  <!-- <th scope="col">Platform</th> -->
                  <th width="1px" scope="col">Month</th>
                  <th class="text-center" width="1px" scope="col">Date</th>
                </tr>
              </thead>
              <tbody>
                @php $i=1 @endphp
                @foreach($dataset as $s)
                <tr>
                  <th scope="row">{{ $i++ }}</th>
                  <td>{{$s->author}}</td>
                  <td>{{$s->text}}</td>
                  <td>{{$s->month}}</td>
                  <td>{{$s->date}}</td>
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
            <form method="post" action="/dataset/import_excel_perbulan" enctype="multipart/form-data">
              <!-- <form class="form horizontal"> -->
              <!-- <form> -->
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Import Excel</h5>
                </div>
                <div class="modal-body">

                  {{ csrf_field() }}

                  <label>Pilih file excel</label>
                  <div class="form-group">
                    <input type="file" name="file" required="required">
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
      <div id="loader"></div>
    </div>
  </section>
  <!-- membuat loading data ketika disubmit  -->
  <!-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
  <script>
    var spinner = $('#loader');
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });
    $(function() {
      $('form').submit(function(e) {
        e.preventDefault();
        var formData = new FormData()
        var files = $('#file')[0].files
        if (files.length > 0) {
          formData.append('excel', files[0])
          spinner.show();
          new Promise((resolve, reject) => {
            $.ajax({
              url: 'http://localhost:8000/dataset/import_excel',
              type: 'POST',
              data: formData,
              contentType: false,
              processData: false,
            })

          }).done(function(resp) {
            spinner.hide();
            alert(resp.status);
            $.load('http://localhost:8000/')
          });
        }
      });
    });
  </script> -->
</main>
@endsection