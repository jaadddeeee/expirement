
@extends('layouts/contentNavbarLayout')

@section('title', $pageTitle)


@section('content')

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
      <div class="card-body">

          <div class = "table-responsive">
              <table class="table table-sm-table-hover">
                <thead>
                  <tr>
                    <th class = "text-nowrap text-center">#</th>
                    <th class = "text-nowrap text-center">Action</th>
                    <th>SchoolYear</th>
                    <th>Semester</th>
                    <th>Schedule</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($scheds as $sched)
                  <tr>
                    <td class = "text-nowrap text-center">{{isset($ctr)?++$ctr:$ctr=1}}</td>
                    <td class = "text-nowrap text-center">
                      <a href = "{{route('faculty-evaluation-one',['id' => Crypt::encryptstring($sched->id)])}}"><i class='bx bx-show'></i></a>
                      <a href = "{{route('faculty-evaluation-analytics',['id' => Crypt::encryptstring($sched->id)])}}"><i class='text-info bx bx-line-chart'></i></a>
                    </td>
                    <td class = "text-nowrap">{{GENERAL::setSchoolYearLabel($sched->SchoolYear,$sched->Semester)}}</td>
                    <td class = "text-nowrap">{{GENERAL::Semesters()[$sched->Semester]['Long']}}</td>
                    <td class = "text-nowrap">{{date('F j, Y', strtotime($sched->startdate)).' - '.date('F j, Y', strtotime($sched->enddate))}}</td>
                    <td class = "text-nowrap">
                      <?php
                        $status = '<span class = "text-danger fw-bold">CLOSED<span>';
                        if (strtotime(date('F j, Y')) >= strtotime($sched->startdate) and strtotime(date('F j, Y')) <= strtotime($sched->enddate)){
                          $status = '<span class = "text-success fw-bold">OPEN<span>';
                        }
                        echo $status;
                      ?>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
          </div>
      </div>
    </div>
  </div>
</div>

@include('surveys.modal')
@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@endsection
