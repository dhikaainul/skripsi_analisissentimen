@extends('master')
@section('title','Dashboard')
@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Dashboard Per Bulan</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Klasifikasi Per Bulan</li>
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <p>Diagram line chart untuk menampilkan jumlah dari sentimen positif, negatif dan netral per bulan.</p>
    <script type="text/javascript" src="http://code.jquery.com/jquery-1.11.3.min.js"></script>
    <script type="text/javascript" src="http://code.highcharts.com/highcharts.js"></script>
    <script type="text/javascript" src="http://code.highcharts.com/modules/exporting.js"></script>
    <style type="text/css">
        .container {
            margin: auto;
            padding: 5px;
            width: 1000px;
            border: 2px solid #DBDBDB;
        }
    </style>
    </head>

    <body>
        <div class="container">
            <div class="grafik" style="width:100%; height:400px;"></div>
        </div>
        <script type="text/javascript">
            var positifFebruari = {!!json_encode($positifFebruari)!!};
            var negatifFebruari = {!!json_encode($negatifFebruari)!!};
            var netralFebruari = {!!json_encode($netralFebruari)!!};
            var positifMaret = {!!json_encode($positifMaret)!!};
            var negatifMaret = {!!json_encode($negatifMaret)!!};
            var netralMaret = {!!json_encode($netralMaret)!!};
            var positifApril = {!!json_encode($positifApril)!!};
            var negatifApril = {!!json_encode($negatifApril)!!};
            var netralApril = {!!json_encode($netralApril)!!};
            var positifMei = {!!json_encode($positifMei)!!};
            var negatifMei = {!!json_encode($negatifMei)!!};
            var netralMei = {!!json_encode($netralMei)!!};
            $('.grafik').highcharts({
                chart: {
                    type: 'line',
                    marginTop: 80
                },
                credits: {
                    enabled: false
                },
                tooltip: {
                    shared: true,
                    crosshairs: true,
                    headerFormat: '<b>{point.key}</b> <br>{}'
                },
                title: {
                    text: 'JUMLAH POSITIF, NEGATIF DAN NETRAL'
                },
                subtitle: {
                    text: 'Bulan Februari - Mei'
                },
                xAxis: {
                    categories: ["Februari", "Maret", "April", "Mei"],
                    labels: {
                        rotation: 0,
                        align: 'right',
                        style: {
                            fontSize: '10px',
                            fontFamily: 'Verdana, sans-serif'
                        }
                    }
                },
                legend: {
                    enabled: true
                },
                series: [{
                    "name": "Positif",
                    "data": [positifFebruari, positifMaret, positifApril, positifMei]
                }, {
                    "name": "Negatif",
                    "data": [negatifFebruari, negatifMaret, negatifApril, negatifMei]
                }, {
                    "name": "Netral",
                    "data": [netralFebruari, netralMaret, netralApril, netralMei]
                }]
            });
        </script>
        <br>
        <div class="container">
            <div class="card" style="width:100%; height:100%;">
                <div class="card-header">
                    Keterangan
                </div>
                <div class="card-body">
                    <ul>
                        <li class="card-text">Positif : Tweet yang berhubungan dengan IKN dan Ekonomi </li>
                        <li class="card-text">Negatif : Tweet yang berhubungan dengan IKN tapi tidak ada hubungannya dengan Ekonomi</li>
                        <li class="card-text">Netral  : Tweet yang ada kaitannya dengan IKN dan Ekonomi, tapi tidak sepenuhnya tentang Ekonomi</li>
                    </ul>
                    <ul>
                        <li class="card-text">Positif Paling Banyak Di Bulan {{$bulanPositif}} Sebanyak = {{$positif}}</li>
                        <li class="card-text">Negatif Paling Banyak Di Bulan {{$bulanNegatif}} Sebanyak = {{$negatif}} </li>
                        <li class="card-text">Netral Paling Banyak Di Bulan {{$bulanNetral}} Sebanyak = {{$netral}} </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- <div class="container">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Klasifikasi Naive Bayes Menggunakan TF</h5>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">No</th>
                                <th scope="col">Jenis Sentimen</th>
                                <th scope="col">Paling Banyak (Bulan)</th>
                                <th scope="col">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="row">1</th>
                                <td>Positif</td>
                                <td>Januari</td>
                                <td>200</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div> -->
    </body>
</main><!-- End #main -->
@endsection