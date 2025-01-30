
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
                    <th>SchoolYear</th>
                    <th>Semester</th>
                    <th class = "text-nowrap text-center">Commitment</th>
                    <th class = "text-nowrap text-center">Knowledge<br>of Subject</th>
                    <th class = "text-nowrap text-center">Teaching for<br>Independent Learning</th>
                    <th class = "text-nowrap text-center">Management<br>of Learning</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($perSchedAVGs as $sched)
                  <tr>
                    <td class = "text-nowrap text-center">{{isset($ctr)?++$ctr:$ctr=1}}</td>
                    <td class = "text-nowrap">{{GENERAL::setSchoolYearLabel($sched['SchoolYear'],$sched['Semester'])}}</td>
                    <td class = "text-nowrap">{{GENERAL::Semesters()[$sched['Semester']]['Long']}}</td>
                    <td class = "text-nowrap text-center">SCORE: <strong>{{number_format($sched['Category'][0],2,'.','')}}</strong><br>({{number_format($sched['FiveCat'][0],2,'.','')}}) {!!GENERAL::AdjectiveRating($sched['FiveCat'][0])!!}</td>
                    <td class = "text-nowrap text-center">SCORE: <strong>{{number_format($sched['Category'][1],2,'.','')}}</strong><br>({{number_format($sched['FiveCat'][1],2,'.','')}}) {!!GENERAL::AdjectiveRating($sched['FiveCat'][1])!!}</td>
                    <td class = "text-nowrap text-center">SCORE: <strong>{{number_format($sched['Category'][2],2,'.','')}}</strong><br>({{number_format($sched['FiveCat'][2],2,'.','')}}) {!!GENERAL::AdjectiveRating($sched['FiveCat'][2])!!}</td>
                    <td class = "text-nowrap text-center">SCORE: <strong>{{number_format($sched['Category'][3],2,'.','')}}</strong><br>({{number_format($sched['FiveCat'][3],2,'.','')}}) {!!GENERAL::AdjectiveRating($sched['FiveCat'][3])!!}</td>
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
