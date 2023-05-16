@extends('master')
@section('title','Dashboard')
@section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Dashboard</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <p>Diagram batang untuk analisis sentimen jumlah kategori positif, negatif dan netral menggunakan metode Naive Bayes.</p>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Diagram Analisis Sentimen Jumlah Kategori Positif, Negatif dan Netral</h5>

                        <!-- Column Chart -->
                        <div id="columnChart"></div>

                        <script>
                        var positif = {!!json_encode($positif)!!};
                        var negatif = {!!json_encode($negatif)!!};
                        var netral = {!!json_encode($netral)!!};
                        var positifTF = {!!json_encode($positifTF)!!};
                        var negatifTF = {!!json_encode($negatifTF)!!};
                        var netralTF = {!!json_encode($netralTF)!!};
                        var positifTFIDF = {!!json_encode($positifTFIDF)!!};
                        var negatifTFIDF = {!!json_encode($negatifTFIDF)!!};
                        var netralTFIDF = {!!json_encode($netralTFIDF)!!};
                        // var akurasiTF = {!!json_encode($akurasiTF)!!};
                        // akurasiTF = akurasiTF*100;
                        // var presisiTF = {!!json_encode($presisiTF)!!};
                        // presisiTF = presisiTF*100;
                        // var recallTF = {!!json_encode($recallTF)!!};
                        // recallTF = recallTF*100;
                        // var akurasiTFIDF = {!!json_encode($akurasiTFIDF)!!};
                        // akurasiTFIDF = akurasiTFIDF*100;
                        // var presisiTFIDF = {!!json_encode($presisiTFIDF)!!};
                        // presisiTFIDF = presisiTFIDF*100;
                        // var recallTFIDF = {!!json_encode($recallTFIDF)!!};
                        // recallTFIDF = recallTFIDF*100;

                            document.addEventListener("DOMContentLoaded", () => {
                                new ApexCharts(document.querySelector("#columnChart"), {
                                    series: [{
                                        name: 'Manual',
                                        data: [positif, netral, negatif]
                                    }, {
                                        name: 'Naive Bayes TF',
                                        data: [positifTF, netralTF, negatifTF]
                                    }, {
                                        name: 'Naive Bayes TF-IDF',
                                        data: [positifTFIDF, netralTFIDF, negatifTFIDF]
                                    }],
                                    chart: {
                                        type: 'bar',
                                        height: 350
                                    },
                                    plotOptions: {
                                        bar: {
                                            horizontal: false,
                                            columnWidth: '55%',
                                            endingShape: 'rounded'
                                        },
                                    },
                                    dataLabels: {
                                        enabled: false
                                    },
                                    stroke: {
                                        show: true,
                                        width: 2,
                                        colors: ['transparent']
                                    },
                                    xaxis: {
                                        categories: ['Positif', 'Netral', 'Negatif'],
                                    },
                                    yaxis: {
                                        title: {
                                            text: '(Jumlah)'
                                        }
                                    },
                                    fill: {
                                        opacity: 1
                                    },
                                    tooltip: {
                                        y: {
                                            formatter: function(val) {
                                                return val
                                            }
                                        }
                                    }
                                }).render();
                            });
                        </script>
                        <!-- End Column Chart -->

                    </div>
                </div>
            </div>
        <div class="card">
    <div class="card-header">
    Keterangan
  </div>
  <div class="card-body">
  <ul>
    <li class="card-text">Positif : Tweet yang berhubungan dengan IKN dan Ekonomi </li>
    <li class="card-text">Negatif : Tweet yang berhubungan dengan IKN tapi tidak ada hubungannya dengan Ekonomi</li>
    <li class="card-text">Netral  : Tweet yang ada kaitannya dengan IKN dan Ekonomi, tapi tidak sepenuhnya tentang Ekonomi</li>
 </ul>
  </div>
</div>
            <!-- <style type="text/css">
                .container {
                    width: 30%;
                    margin: 15px auto;
                }
            </style>
            <div class="container">
                <canvas id="doughnutchart1" width="100" height="100"></canvas>
            </div>
            <div class="container">
                <canvas id="doughnutchart2" width="100" height="100"></canvas>
            </div>
            <div class="container">
                <canvas id="doughnutchart3" width="100" height="100"></canvas>
            </div>
            <div class="container">
                <canvas id="doughnutchart4" width="100" height="100"></canvas>
            </div>
            <div class="container">
                <canvas id="doughnutchart5" width="100" height="100"></canvas>
            </div>
            <div class="container">
                <canvas id="doughnutchart6" width="100" height="100"></canvas>
            </div> -->

        </div>
    </section>

</main><!-- End #main -->
<!-- <script src="js/Chart.js"></script> -->
<!-- pertama -->
<!-- <script type="text/javascript">
    var ctx = document.getElementById("doughnutchart1").getContext("2d");
    var data = {
        labels: ["Akurasi TF Naive Bayes %"],
        datasets: [{
            label: "TF Naive Bayes",
            data: [akurasiTF],
            backgroundColor: [
                '#29B0D0',
            ]
        }]
    };

    var mydoughnutchart1 = new Chart(ctx, {
        type: 'doughnut',
        data: data,
        options: {
            responsive: true
        },
        tooltip: {
            formatter: function() {
                return '<b>' + this.data + ' %';
            }
        },
    });
</script> -->
<!-- kedua -->
<!-- <script type="text/javascript">
    var ctx = document.getElementById("doughnutchart2").getContext("2d");
    var data = {
        labels: ["Presisi TF Naive Bayes %"],
        datasets: [{
            label: "TF Naive Bayes",
            data: [presisiTF],
            backgroundColor: [
                '#2A516E',
            ]
        }]
    };

    var mydoughnutchart2 = new Chart(ctx, {
        type: 'doughnut',
        data: data,
        options: {
            responsive: true
        }
    });
</script> -->
<!-- ketiga -->
<!-- <script type="text/javascript">
    var ctx = document.getElementById("doughnutchart3").getContext("2d");
    var data = {
        labels: ["Recall TF Naive Bayes %"],
        datasets: [{
            label: "TF Naive Bayes",
            data: [recallTF],
            backgroundColor: [
                '#F07124',
            ]
        }]
    };

    var mydoughnutchart3 = new Chart(ctx, {
        type: 'doughnut',
        data: data,
        options: {
            responsive: true
        }
    });
</script>
 -->
<!-- keempat -->
<!-- <script type="text/javascript">
    var ctx = document.getElementById("doughnutchart4").getContext("2d");
    var data = {
        labels: ["Akurasi TF-IDF Naive Bayes %"],
        datasets: [{
            label: "TF-IDF Naive Bayes",
            data: [akurasiTFIDF],
            backgroundColor: [
                '#29B0D0',
            ]
        }]
    };

    var mydoughnutchart4 = new Chart(ctx, {
        type: 'doughnut',
        data: data,
        options: {
            responsive: true
        }
    });
</script> -->
<!-- kelima -->
<!-- <script type="text/javascript">
    var ctx = document.getElementById("doughnutchart5").getContext("2d");
    var data = {
        labels: ["Presisi TF-IDF Naive Bayes %"],
        datasets: [{
            label: "TF Naive Bayes",
            data: [presisiTFIDF],
            backgroundColor: [
                '#2A516E',
            ]
        }]
    };

    var mydoughnutchart5 = new Chart(ctx, {
        type: 'doughnut',
        data: data,
        options: {
            responsive: true
        }
    });
</script> -->
<!-- keenam -->
<!-- <script type="text/javascript">
    var ctx = document.getElementById("doughnutchart6").getContext("2d");
    var data = {
        labels: ["Recall TF-IDF Naive Bayes %"],
        datasets: [{
            label: "TF-IDF Naive Bayes",
            data: [recallTFIDF],
            backgroundColor: [
                '#F07124',
            ]
        }]
    };

    var mydoughnutchart6 = new Chart(ctx, {
        type: 'doughnut',
        data: data,
        options: {
            responsive: true
        }
    });
</script> -->

@endsection