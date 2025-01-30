<?php
    use App\Http\Controllers\SLSU\GradeLock;
    $lock = new GradeLock(['sy' => $passsy, 'sem' => $passsem]);

?>
@extends('layouts/contentNavbarLayout')

@section('title', 'Encode Grade')

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
      <div class="card-body">
        <div class="row g-3 align-items-center">
            <div class="col-auto">
              <select name="SchoolYear" id="SchoolYear" class="form-select">
                <option value="0">Select School Year</option>
                  @foreach(GENERAL::SchoolYears() as $sy)
                    <option value="{{$sy}}" <?=(session('schoolyear')==$sy?"Selected":"")?>>{{$sy."-".($sy+1)}}</option>
                  @endforeach
              </select>
            </div>
            <div class="col-auto">
              <select name="Semester" id="Semester" class="form-select">
                <option value="0">Select Semester</option>
                  @foreach(GENERAL::Semesters() as $index => $sem)
                    <option value="{{$index}}" <?=(session('semester')==$index?"Selected":"")?>>{{$sem['Long']}}</option>
                  @endforeach
              </select>
            </div>
            <div class="col-auto">
              <span id="passwordHelpInline" class="form-text">
                <button class = "btn btn-primary" type = "button" id = "btnViewGrades">View</button>
              </span>
            </div>
        </div>
        <hr class = "mt-4">
        <div id = "outAjax">
          @if (!empty($Error))
            {!!GENERAL::Error($Error);!!}
          @else
          <div class="table-responsive">
            @if($clicked == "grades")
            <div class = "alert alert-info">Encoding will start on {{(empty($lock->getDateStart())?": NOT SET":date('F j, Y', strtotime($lock->getDateStart())))}}</div>
            @endif
            <table class="table table-sm table-hover">
              <thead>
                <tr>
                  <td class = "text-nowrap">Course Code</td>
                  <td class = "text-nowrap">Subject</td>
                  <td class = "text-nowrap">Description</td>
                  <td class = "text-nowrap text-center">Units</td>
                  <td class = "text-nowrap">Schedule</td>
                  <td class = "text-nowrap text-center">Enrolled</td>
                  <td class = "text-nowrap">Deadline Encoding</td>
                </tr>
              </thead>
              <tbody>
                @foreach($lists as $list)
                  @php
                    $lock->setTeacherID(auth()->user()->Emp_No);
                    $lock->setSchedID($list->id);
                  @endphp
                  <tr>
                    <td class = "text-nowrap">{!!(empty($list->enrolled_count)?$list->coursecode:'<a href = "'.route(($clicked=='class'?'list-students':'list-grades'),['sched' => Crypt::encryptstring($list->id),'sy' => Crypt::encryptstring($passsy),'sem' => Crypt::encryptstring($passsem)]).'">'.$list->coursecode.'</a>')!!}</td>
                    <td class = "text-nowrap">{{$list->subject->courseno}}</td>
                    <td class = "text-nowrap">{{$list->subject->coursetitle}}</td>
                    <td class = "text-nowrap text-center">{{$list->subject->units}}</td>
                    <td class = "text-nowrap">{!!(isset($list->schedule->tym)?$list->schedule->tym:"").(isset($list->schedule2->tym)?'<br>'.$list->schedule2->tym:"")!!}</td>
                    <td class = "text-nowrap text-center">{{$list->enrolled_count}}</td>
                    <td class = "text-nowrap">
                        @if (empty($lock->getDateEnd()))
                            <i class = 'text-danger fa fa-ban'></i> Not set
                        @else
                          @if($lock->isOKToEncode())
                            <i class = 'text-success fa fa-unlock-alt'></i> {!! date('M-j-Y', strtotime($lock->getDateEnd())) !!}
                          @else
                            <i class = 'text-danger fa fa-lock'></i> {!! date('M-j-Y', strtotime($lock->getDateEnd())) !!}
                          @endif
                        @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>


@endsection

@section('page-script')
<script src="{{asset('storage/js/teacher.js?id=20230820')}}"></script>
@endsection
