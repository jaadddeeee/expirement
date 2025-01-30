
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
        <form id = "frmGraduationList" action = "{{route('grad.pdf')}}" method = "POST">
          @csrf
          <div class="row g-3 align-items-center">

            <div class="col-auto">
              <select name="YearOfGraduation" id="YearOfGraduation" class="form-select">
                <option value="0">Year of Graduation</option>
                  @for($year = $Year; $year>=2009;$year--)
                  <option value="<?=$year?>" <?=($year==$Year?"Selected":"")?>>{{$year}}</option>
                  @endfor
              </select>
            </div>

            <div class="col-auto">
              <select name="DateOfGraduation" id="DateOfGraduation" class="form-select">
                <option value="0">Date of Graduation</option>
                  @foreach($listgraduations as $date)
                    <option value="{{$date->grad}}" {{($date->grad == $DateGrad ?"Selected":"")}}>{{$date->grad . " (".$date->cGrad.")"}}</option>
                  @endforeach
              </select>
            </div>

            <div class="col-auto">
              <span id="passwordHelpInline" class="form-text">
                <button class = "btn btn-primary" type = "button" id = "btnViewGraduateList">View</button>
              </span>
            </div>

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
                <button class = "btn btn-success" type = "submit" id = "btnPrintGraduateList"><i class = "fa fa-print"></i> Print</button>
              </span>
            </div>

          </div>
          <div class="table-responsive" id = "bodyGraduation">
              @include('slsu.ched.lists')
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection

@section('page-script')
@include('slsu.ched.js')
@endsection
