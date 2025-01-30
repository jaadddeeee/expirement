
@extends('layouts/contentNavbarLayout')

@section('title', $pageTitle)


@section('content')
<?php

  // $stars = [5,4,3,2,1];
  // $labels = '"'.implode('","',$course_array).'"';
  // // dd($labels);
  function getTotalEmp($arr = [], $empid){
    $total = 0;
    foreach($arr as $oneEmp){
      if ($oneEmp->EmployeeID == $empid){
        $total += $oneEmp->Rated;
      }
    }

    return $total;
  }
//
  function getRate($arr = [], $empid){
    $total = 0;

    foreach($arr as $one){
      if ($one->EmployeeID == $empid){
        $total += $one->avgRating;
      }
    }

    return $total/4;
  }

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

<div class="row gy-4">
  <div class="col-xl-4 col-md-6">
    <div class="card">
      <div class="card-header">
        <div class="d-flex justify-content-between">
          <h5 class="mb-1">Overall Performance</h5>
        </div>
      </div>
      <div class="card-body">
        <div id="chart"></div>
      </div>
    </div>

    <div class="card mt-4">
      <div class="card-header">
        <div class="d-flex justify-content-between">
          <h5 class="mb-1">Performance by Sex</h5>
        </div>
      </div>
      <div class="card-body">
        <div id="bygender"></div>
      </div>
    </div>
  </div>

  <div class="col-xl-8 col-md-6 col-sm-12">
    <div class="card">
      <div class="card-header">
        <div class="d-flex justify-content-between">
          <h5 class="mb-1">Overall Faculty Rating</h5>
        </div>
      </div>
      <div class="card-body">
          <table class="table table-sm table-striped table-hover">
            <thead>
              <tr>
                <th>#</th>
                <th>Faculty</th>
                <th># of Enrolled</th>
                <th># of Ratee</th>
                <th></th>
                <th>Overall Rating</th>
              </tr>
            </thead>
            <tbody>
              @foreach($faculties as $faculty)
              <?php
                $enrolledcount = $faculty->countStudent;
                $rateecount = getTotalEmp($feeParticipateds, $faculty->teacher);
                if ($rateecount > $enrolledcount){
                  $rateecount = $enrolledcount;
                }
                $rate = getRate($ratesResults, $faculty->teacher);
              ?>
              <tr>
                <td>{{isset($ctr)?++$ctr:$ctr=1}}</td>
                <td>{{$faculty->Faculty}}</td>
                <td>{{$enrolledcount}}</td>
                <td>{{$rateecount}}</td>
                <td width = "20%">
                  <div class="progress bg-label-primary" style="height: 4px;">
                    <div class="progress-bar bg-primary" style="width: {{empty($enrolledcount)?0:($rateecount/$enrolledcount)*100}}%" role="progressbar" aria-valuenow="{{empty($enrolledcount)?0:($rateecount/$enrolledcount)*100}}" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                </td>
                <td>{{number_format($rate,3,'.','')}}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
      </div>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    //overall
    var options = {
      series: [{
      name: 'Participated',
      data: [<?=implode(',', $participated)?>]
    }, {
      name: 'Available Schedule',
      data: [<?=implode(',', $enrolled)?>]
    }],
      chart: {
      type: 'bar',
      height: 430
    },
    plotOptions: {
      bar: {
        horizontal: true,
        dataLabels: {
          position: 'top',
        },
      }
    },
    dataLabels: {
      enabled: true,
      offsetX: -6,
      style: {
        fontSize: '12px',
        colors: ['#fff']
      }
    },
    stroke: {
      show: true,
      width: 1,
      colors: ['#fff']
    },
    tooltip: {
      shared: true,
      intersect: false
    },
    xaxis: {
      categories: [<?=implode(',', $campuses)?>],
    },
    };

    var chart = new ApexCharts(document.querySelector("#chart"), options);
    chart.render();

</script>
@endsection
