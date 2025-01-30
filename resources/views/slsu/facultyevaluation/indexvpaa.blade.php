
@extends('layouts/contentNavbarLayout')

@section('title', $pageTitle)
@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/apex-charts/apex-charts.css')}}">
@endsection


<!-- tblEnrolled -->

@section('content')
<?php

function Categorize($data){
  $poor = 0;
  $under = 0;
  $sat = 0;
  $very = 0;
  $out = 0;
  switch($data){
    case 1:
      $poor++;
      break;
    case 2:
      $under++;
      break;
    case 3:
      $sat++;
      break;
    case 4:
      $very++;
      break;
    case 5:
      $out++;
      break;
  }

  return [
    'Poor' => $poor,
    'Under' => $under,
    'Sat' => $sat,
    'Very' => $very,
    'Out' => $out
  ];
}

$overall = [];
foreach(GENERAL::Campuses() as $index => $campus){
  $poor1 = 0;
  $under1 = 0;
  $sat1 = 0;
  $very1 = 0;
  $out1 = 0;

  $poor2 = 0;
  $under2 = 0;
  $sat2 = 0;
  $very2 = 0;
  $out2 = 0;

  $poor3 = 0;
  $under3 = 0;
  $sat3 = 0;
  $very3 = 0;
  $out3 = 0;

  $poor4 = 0;
  $under4 = 0;
  $sat4 = 0;
  $very4 = 0;
  $out4 = 0;

  $poor5 = 0;
  $under5 = 0;
  $sat5 = 0;
  $very5 = 0;
  $out5 = 0;
  foreach($results as $res){
    if ($res->Campus ==  $index){
      $c1 = round($res->P1,0,PHP_ROUND_HALF_UP);
      $c2 = round($res->P2,0,PHP_ROUND_HALF_UP);
      $c3 = round($res->P3,0,PHP_ROUND_HALF_UP);
      $c4 = round($res->P4,0,PHP_ROUND_HALF_UP);

      $funct = Categorize($c1);
      $poor1 +=  $funct['Poor'];
      $under1 +=  $funct['Under'];
      $sat1 +=  $funct['Sat'];
      $very1 +=  $funct['Very'];
      $out1 +=  $funct['Out'];

      $funct = Categorize($c2);
      $poor2 +=  $funct['Poor'];
      $under2 +=  $funct['Under'];
      $sat2 +=  $funct['Sat'];
      $very2 +=  $funct['Very'];
      $out2 +=  $funct['Out'];

      $funct = Categorize($c3);
      $poor3 +=  $funct['Poor'];
      $under3 +=  $funct['Under'];
      $sat3 +=  $funct['Sat'];
      $very3 +=  $funct['Very'];
      $out3 +=  $funct['Out'];

      $funct = Categorize($c4);
      $poor4 +=  $funct['Poor'];
      $under4 +=  $funct['Under'];
      $sat4 +=  $funct['Sat'];
      $very4 +=  $funct['Very'];
      $out4 +=  $funct['Out'];

    }
  }

  $overall[$index] = [
      'C1' => [
        'POOR' => $poor1,
        'UNDER' => $under1,
        'SAT' => $sat1,
        'VERY' => $very1,
        'OUT' => $out1
      ],
      'C2' => [
        'POOR' => $poor2,
        'UNDER' => $under2,
        'SAT' => $sat2,
        'VERY' => $very2,
        'OUT' => $out2
      ],
      'C3' => [
        'POOR' => $poor3,
        'UNDER' => $under3,
        'SAT' => $sat3,
        'VERY' => $very3,
        'OUT' => $out3
      ],
      'C4' => [
        'POOR' => $poor4,
        'UNDER' => $under4,
        'SAT' => $sat4,
        'VERY' => $very4,
        'OUT' => $out4
      ]
  ];
}

$labelCampus = [];
foreach(GENERAL::Campuses() as $index => $campus){
  $labelCampus[] = "'".$campus['Campus']."'";
}

$labels = ['Outstanding', 'Very Satisfactory', 'Satisfactory', 'Undersatisfactory', 'Poor'];
$js1 = [];
foreach($labels as $label){
  $outs = [];
  foreach(GENERAL::Campuses() as $index => $campus){
    $total = $overall[$index]['C1']['OUT'] + $overall[$index]['C1']['VERY'] + $overall[$index]['C1']['SAT'] + $overall[$index]['C1']['UNDER'] + $overall[$index]['C1']['POOR'];

    foreach ($overall[$index] as $indexCat => $over){
      if ($label == "Outstanding" and $indexCat == "C1"){
        $outs[] = $over['OUT'];
      }

      if ($label == "Very Satisfactory" and $indexCat == "C1"){
        $outs[] = $over['VERY'];
      }

      if ($label == "Satisfactory" and $indexCat == "C1"){
        $outs[] = $over['SAT'];
      }

      if ($label == "Undersatisfactory" and $indexCat == "C1"){
        $outs[] = $over['UNDER'];
      }

      if ($label == "Poor" and $indexCat == "C1"){
        $outs[] = $over['POOR'];
      }
    }
  }
  $js1[] = [
    'name' => $label,
    'data' => $outs
  ];


}


$js2 = [];
foreach($labels as $label){
  $outs = [];
  foreach(GENERAL::Campuses() as $index => $campus){

    foreach ($overall[$index] as $indexCat => $over){
      if ($label == "Outstanding" and $indexCat == "C2"){
        $outs[] = $over['OUT'];
      }

      if ($label == "Very Satisfactory" and $indexCat == "C2"){
        $outs[] = $over['VERY'];
      }

      if ($label == "Satisfactory" and $indexCat == "C2"){
        $outs[] = $over['SAT'];
      }

      if ($label == "Undersatisfactory" and $indexCat == "C2"){
        $outs[] = $over['UNDER'];
      }

      if ($label == "Poor" and $indexCat == "C2"){
        $outs[] = $over['POOR'];
      }
    }
  }
  $js2[] = [
    'name' => $label,
    'data' => $outs
  ];


}

$js3 = [];
foreach($labels as $label){
  $outs = [];
  foreach(GENERAL::Campuses() as $index => $campus){

    foreach ($overall[$index] as $indexCat => $over){
      if ($label == "Outstanding" and $indexCat == "C3"){
        $outs[] = $over['OUT'];
      }

      if ($label == "Very Satisfactory" and $indexCat == "C3"){
        $outs[] = $over['VERY'];
      }

      if ($label == "Satisfactory" and $indexCat == "C3"){
        $outs[] = $over['SAT'];
      }

      if ($label == "Undersatisfactory" and $indexCat == "C3"){
        $outs[] = $over['UNDER'];
      }

      if ($label == "Poor" and $indexCat == "C3"){
        $outs[] = $over['POOR'];
      }
    }
  }
  $js3[] = [
    'name' => $label,
    'data' => $outs
  ];


}

$js4 = [];
foreach($labels as $label){
  $outs = [];
  foreach(GENERAL::Campuses() as $index => $campus){

    foreach ($overall[$index] as $indexCat => $over){
      if ($label == "Outstanding" and $indexCat == "C4"){
        $outs[] = $over['OUT'];
      }

      if ($label == "Very Satisfactory" and $indexCat == "C4"){
        $outs[] = $over['VERY'];
      }

      if ($label == "Satisfactory" and $indexCat == "C4"){
        $outs[] = $over['SAT'];
      }

      if ($label == "Undersatisfactory" and $indexCat == "C4"){
        $outs[] = $over['UNDER'];
      }

      if ($label == "Poor" and $indexCat == "C4"){
        $outs[] = $over['POOR'];
      }
    }
  }
  $js4[] = [
    'name' => $label,
    'data' => $outs
  ];


}
// dd($js1);
//
//   $js1[] = [
//     'name' => $campus['Campus'],
//     'data' => [($overall[$index]['C1']['OUT']/$total) *100,$overall[$index]['C1']['VERY']/$total,$overall[$index]['C1']['SAT']/$total,$overall[$index]['C1']['UNDER']/$total,$overall[$index]['C1']['POOR']/$total],
//   ];

?>
<nav aria-label="breadcrumb">
  <ol class="breadcrumb breadcrumb-style1">
    <li class="breadcrumb-item">
      <a href="{{route('home')}}">Home</a>
    </li>
    <li class="breadcrumb-item">
      <a href="javascript:void(0);">{{$pageTitle}}</a>
    </li>
  </ol>
</nav>

<div class="row">
  <div class="col-sm-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between">
        <div class="header-title">
          <h4 class="card-title">{{ $pageTitle ?? 'List'}}</h4>

        </div>
        <div class="card-action">
            {!! $headerAction ?? '' !!}

        </div>
      </div>
      <hr>
      <div class="row m-2">
        <div class="row g-3 align-items-center ms-2 mb-3">
            <div class="col-auto">
              <select name="Period" id="Period" class="form-select">
                <option value="0">Select Period</option>
                  @foreach($scheds as $sched)
                    <option value="{{$sched->id}}" <?=($sched->id==$ID?"Selected":"")?>>{{GENERAL::setSchoolYearLabel($sched->SchoolYear,$sched->Semester)}} - {{GENERAL::Semesters()[$sched->Semester]['Long']}}</option>
                  @endforeach
              </select>
            </div>
        </div>

        <div class="row g-3 align-items-center ms-2 mb-3">
          <h5 class = 'text-dark'>TURNOUT RATE</h5>
          <div id="chartEnrolled"></div>
        </div>
        <div class="col-lg-12 col-sm-12">
          <div class="table-responsive">
              <table class="table-sm table-striped table-hover table-bordered table">
                <thead>
                  <tr>
                    <th class = "text-nowrap" rowspan = '2' valign = "middle">Campus</th>
                    <th class = "text-nowrap text-center" colspan = '5'>Commitment</th>
                    <th class = "text-nowrap text-center" colspan = '5'>Knowledge of Subject</th>
                    <th class = "text-nowrap text-center" colspan = '5'>Teaching for Independent Learning</th>
                    <th class = "text-nowrap text-center" colspan = '5'>Management of Learning</th>
                  </tr>
                  <tr>
                    <th class = "text-nowrap text-center">O</th>
                    <th class = "text-nowrap text-center">VS</th>
                    <th class = "text-nowrap text-center">S</th>
                    <th class = "text-nowrap text-center">US</th>
                    <th class = "text-nowrap text-center">P</th>

                    <th class = "text-nowrap text-center">O</th>
                    <th class = "text-nowrap text-center">VS</th>
                    <th class = "text-nowrap text-center">S</th>
                    <th class = "text-nowrap text-center">US</th>
                    <th class = "text-nowrap text-center">P</th>

                    <th class = "text-nowrap text-center">O</th>
                    <th class = "text-nowrap text-center">VS</th>
                    <th class = "text-nowrap text-center">S</th>
                    <th class = "text-nowrap text-center">US</th>
                    <th class = "text-nowrap text-center">P</th>

                    <th class = "text-nowrap text-center">O</th>
                    <th class = "text-nowrap text-center">VS</th>
                    <th class = "text-nowrap text-center">S</th>
                    <th class = "text-nowrap text-center">US</th>
                    <th class = "text-nowrap text-center">P</th>


                  </tr>
                </thead>
                <tbody>
                  @foreach(GENERAL::Campuses() as $index => $campus)

                  <tr>
                    <td class = "text-nowrap">
                      <a href = "{{ route('faculty-evaluation-all', ['Period' => $ID, 'Campus' => $index]) }}">{{$campus['Campus']}}</a>
                    </td>
                        <?php
                          $start = 1;

                        ?>

                        @foreach($overall[$index] as $cat)

                        <td class = "text-nowrap text-center">{{$cat['OUT']}}</td>
                        <td class = "text-nowrap text-center">{{$cat['VERY']}}</td>
                        <td class = "text-nowrap text-center">{{$cat['SAT']}}</td>
                        <td class = "text-nowrap text-center">{{$cat['UNDER']}}</td>
                        <td class = "text-nowrap text-center">{{$cat['POOR']}}</td>
                        <?php
                          $start++;
                        ?>
                        @endforeach
                  </tr>
                  @endforeach
                </tbody>
              </table>
          </div>
        </div>
      </div>

      <div class="row mt-5">
        <div id="chart1"></div>

        <div id="chart2"></div>

        <div id="chart3"></div>

        <div id="chart4"></div>

      </div>
    </div>
  </div>
</div>

@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/apex-charts/apexcharts.js')}}"></script>
@endsection

@section('page-script')

<script>

  var options = {
          series: [{
          name: 'Participated',
          data: [<?=round(($tblEnrolled->SGResult/$tblEnrolled->SG)*100, 2)?>, <?=round(($tblEnrolled->MCCResult/$tblEnrolled->MCC)*100, 2)?>, <?=round(($tblEnrolled->TOResult/$tblEnrolled->TO)*100, 2)?>, <?=round(($tblEnrolled->BNResult/$tblEnrolled->BN)*100, 2)?>, <?=round(($tblEnrolled->SJResult/$tblEnrolled->SJ)*100, 2)?>, <?=round(($tblEnrolled->HNResult/$tblEnrolled->HN)*100, 2)?>]
        }, {
          name: 'Total Participants',
          data: [100, 100, 100, 100, 100, 100]
        }],
          chart: {
          type: 'bar',
          height: 350
        },
        plotOptions: {
          bar: {
            horizontal: false,
            columnWidth: '40%',
            borderRadius: 5,
            borderRadiusApplication: 'right'
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
          categories: [<?=implode(',', $labelCampus)?>],
        },
        yaxis: {
          title: {
            text: '%'
          }
        },
        fill: {
          opacity: 1
        },
        tooltip: {
          y: {
            formatter: function (val) {
              return val + "%"
            }
          }
        }
  };

  var chart = new ApexCharts(document.querySelector("#chartEnrolled"), options);
  chart.render();

  var options = {
          series: <?=json_encode($js1)?>,
          chart: {
          type: 'bar',
          height: 350,
          stacked: true,
          stackType: '100%'
        },
        plotOptions: {
          bar: {
            horizontal: true,
          },
        },
        stroke: {
          width: 1,
          colors: ['#fff']
        },
        title: {
          text: 'COMMITMENT'
        },
        xaxis: {
          categories: [<?=implode(',', $labelCampus)?>],
        },

        fill: {
          opacity: 1

        },
        legend: {
          position: 'top',
          horizontalAlign: 'right',
          offsetX: 40
        }
  };

  var chart = new ApexCharts(document.querySelector("#chart1"), options);
  chart.render();

  var options2 = {
          series: <?=json_encode($js2)?>,
          chart: {
          type: 'bar',
          height: 350,
          stacked: true,
          stackType: '100%'
        },
        plotOptions: {
          bar: {
            horizontal: true,
          },
        },
        stroke: {
          width: 1,
          colors: ['#fff']
        },
        title: {
          text: 'Knowledge of Subject'
        },
        xaxis: {
          categories: [<?=implode(',', $labelCampus)?>],
        },

        fill: {
          opacity: 1

        },
        legend: {
          position: 'top',
          horizontalAlign: 'right',
          offsetX: 40
        }
  };

  var chart = new ApexCharts(document.querySelector("#chart2"), options2);
  chart.render();

  var options3 = {
          series: <?=json_encode($js3)?>,
          chart: {
          type: 'bar',
          height: 350,
          stacked: true,
          stackType: '100%'
        },
        plotOptions: {
          bar: {
            horizontal: true,
          },
        },
        stroke: {
          width: 1,
          colors: ['#fff']
        },
        title: {
          text: 'Teaching for Independent Learning'
        },
        xaxis: {
          categories: [<?=implode(',', $labelCampus)?>],
        },

        fill: {
          opacity: 1

        },
        legend: {
          position: 'top',
          horizontalAlign: 'right',
          offsetX: 40
        }
  };

  var chart = new ApexCharts(document.querySelector("#chart3"), options3);
  chart.render();


  var options4 = {
          series: <?=json_encode($js4)?>,
          chart: {
          type: 'bar',
          height: 350,
          stacked: true,
          stackType: '100%'
        },
        plotOptions: {
          bar: {
            horizontal: true,
          },
        },
        stroke: {
          width: 1,
          colors: ['#fff']
        },
        title: {
          text: 'Management of Learning'
        },
        xaxis: {
          categories: [<?=implode(',', $labelCampus)?>],
        },

        fill: {
          opacity: 1

        },
        legend: {
          position: 'top',
          horizontalAlign: 'right',
          offsetX: 40
        }
  };

  var chart = new ApexCharts(document.querySelector("#chart4"), options4);
  chart.render();

</script>
@endsection
