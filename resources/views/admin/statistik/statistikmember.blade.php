@extends('layouts.admin')

@section('styles')
    <style>
    div.scrollmenu {
      /* background-color: #333; */
      overflow: auto;
      white-space: nowrap;
    }
    </style>
@endsection

@section('content')

    <form  action="{{ route("admin.statistik.member") }}" method="GET"  >
        {{-- @csrf --}}
        <div class="col-md-12 ">
            <div class="row " style="justify-content: center; align-items: center">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="Month">Month</label>
                        <select name="month" id="Month" class="form-control" >
                            <option value="all"  {{ isset($_GET['month']) && $_GET['month'] == 'all'? 'selected' : null }}>Pilih Bulan </option>
                            <option value="01" {{ isset($_GET['month']) && $_GET['month'] == '01'? 'selected' : null }}>Januari</option>
                            <option value="02" {{ isset($_GET['month']) && $_GET['month'] == '02'? 'selected' : null }}>Februari</option>
                            <option value="03" {{ isset($_GET['month']) && $_GET['month'] == '03'? 'selected' : null }}>Maret</option>
                            <option value="04" {{ isset($_GET['month']) && $_GET['month'] == '04'? 'selected' : null }}>April</option>
                            <option value="05" {{ isset($_GET['month']) && $_GET['month'] == '05'? 'selected' : null }}>Mei</option>
                            <option value="06" {{ isset($_GET['month']) && $_GET['month'] == '06'? 'selected' : null }}>Juni</option>
                            <option value="07" {{ isset($_GET['month']) && $_GET['month'] == '07'? 'selected' : null }}>Jui</option>
                            <option value="08" {{ isset($_GET['month']) && $_GET['month'] == '08'? 'selected' : null }}>Agutus</option>
                            <option value="09" {{ isset($_GET['month']) && $_GET['month'] == '09'? 'selected' : null }}>September</option>
                            <option value="10" {{ isset($_GET['month']) && $_GET['month'] == '10'? 'selected' : null }}>Oktober</option>
                            <option value="11" {{ isset($_GET['month']) && $_GET['month'] == '11'? 'selected' : null }}>November</option>
                            <option value="12" {{ isset($_GET['month']) && $_GET['month'] == '12'? 'selected' : null }}>Desember</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="Month">Years</label>
                        <?php 
                            $yearnow = date('Y'); 
                            $yearnow = (int)$yearnow;
                        ?>
                        <select name="year" id="Month" class="form-control" >
                            <option  value="" {{ !isset($_GET['year'] ) ? 'selected' : ''}}>Pilih Tahun </option>
                            @for ($i = $yearnow; $i >= ($yearnow - 10); $i--)
                                <option value="{{ $i }}" {{ isset($_GET['year']) && $_GET['year'] == $i? 'selected' : '' }} >{{ $i }}</option> }}
                            @endfor
                            {{-- <option value="2020">2020</option>
                            <option value="2021">2021</option> --}}
                        </select>
                    </div>
                </div>
                <div class="col-md-4"  >
                    <label> Seacrh</label>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="scrollmenu">
        <canvas id="myChartCount"></canvas>
    </div>
    
    <div class="scrollmenu">
        <canvas id="myChartCount2"></canvas>
    </div>

    <div class="scrollmenu">
        <canvas id="myChartCount3"></canvas>
    </div>

    <div class="scrollmenu">
        <canvas id="myChartCount4"></canvas>
    </div>
   
@endsection
@section('scripts')
@parent

<script>

    var ctxCount = document.getElementById('myChartCount').getContext('2d');
    var chartCount = new Chart(ctxCount, {
        // The type of chart we want to create
        type: 'bar',
// The data for our dataset
        data: {
            labels:  {!!json_encode($chartCount->labels)!!} ,
            datasets: [
                {
                    label: 'Statistik Pertumbuhan Member User',
                    backgroundColor: {!! json_encode($chartCount->colours)!!} ,
                    data:  {!! json_encode($chartCount->dataset)!!} ,
                },
            ]
        },
// Configuration options go here
        options: {
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        callback: function(value) {if (value % 1 === 0) {return value;}}
                    },
                    scaleLabel: {
                        display: false
                    }
                }]
            },
            legend: {
                labels: {
                    // This more specific font property overrides the global property
                    fontColor: '#122C4B',
                    fontFamily: "'Muli', sans-serif",
                    padding: 25,
                    boxWidth: 25,
                    fontSize: 14,
                }
            },
            layout: {
                padding: {
                    left: 10,
                    right: 10,
                    top: 0,
                    bottom: 10
                }
            }
        }
    });

    var ctxCount2 = document.getElementById('myChartCount2').getContext('2d');
    var chartCount2 = new Chart(ctxCount2, {
        // The type of chart we want to create
        type: 'bar',
// The data for our dataset
        data: {
            labels:  {!!json_encode($chartCount2->labels)!!} ,
            datasets: [
                {
                    label: 'Statistik Pertumbuhan Member Silver',
                    backgroundColor: {!! json_encode($chartCount2->colours)!!} ,
                    data:  {!! json_encode($chartCount2->dataset)!!} ,
                },
            ]
        },
// Configuration options go here
        options: {
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        callback: function(value) {if (value % 1 === 0) {return value;}}
                    },
                    scaleLabel: {
                        display: false
                    }
                }]
            },
            legend: {
                labels: {
                    // This more specific font property overrides the global property
                    fontColor: '#122C4B',
                    fontFamily: "'Muli', sans-serif",
                    padding: 25,
                    boxWidth: 25,
                    fontSize: 14,
                }
            },
            layout: {
                padding: {
                    left: 10,
                    right: 10,
                    top: 0,
                    bottom: 10
                }
            }
        }
    });

    var ctxCount3 = document.getElementById('myChartCount3').getContext('2d');
    var chartCount3 = new Chart(ctxCount3, {
        // The type of chart we want to create
        type: 'bar',
// The data for our dataset
        data: {
            labels:  {!!json_encode($chartCount3->labels)!!} ,
            datasets: [
                {
                    label: 'Statistik Pertumbuhan Member Gold',
                    backgroundColor: {!! json_encode($chartCount3->colours)!!} ,
                    data:  {!! json_encode($chartCount3->dataset)!!} ,
                },
            ]
        },
// Configuration options go here
        options: {
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        callback: function(value) {if (value % 1 === 0) {return value;}}
                    },
                    scaleLabel: {
                        display: false
                    }
                }]
            },
            legend: {
                labels: {
                    // This more specific font property overrides the global property
                    fontColor: '#122C4B',
                    fontFamily: "'Muli', sans-serif",
                    padding: 25,
                    boxWidth: 25,
                    fontSize: 14,
                }
            },
            layout: {
                padding: {
                    left: 10,
                    right: 10,
                    top: 0,
                    bottom: 10
                }
            }
        }
    });

    var ctxCount4 = document.getElementById('myChartCount4').getContext('2d');
    var chartCount4 = new Chart(ctxCount4, {
        // The type of chart we want to create
        type: 'bar',
// The data for our dataset
        data: {
            labels:  {!!json_encode($chartCount4->labels)!!} ,
            datasets: [
                {
                    label: 'Statistik Pertumbuhan Member Platinum',
                    backgroundColor: {!! json_encode($chartCount4->colours)!!} ,
                    data:  {!! json_encode($chartCount4->dataset)!!} ,
                },
            ]
        },
// Configuration options go here
        options: {
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        callback: function(value) {if (value % 1 === 0) {return value;}}
                    },
                    scaleLabel: {
                        display: false
                    }
                }]
            },
            legend: {
                labels: {
                    // This more specific font property overrides the global property
                    fontColor: '#122C4B',
                    fontFamily: "'Muli', sans-serif",
                    padding: 25,
                    boxWidth: 25,
                    fontSize: 14,
                }
            },
            layout: {
                padding: {
                    left: 10,
                    right: 10,
                    top: 0,
                    bottom: 10
                }
            }
        }
    });


    
    </script>
@endsection