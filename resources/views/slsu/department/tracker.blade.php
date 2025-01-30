
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
          <h4 class="card-title">{{ $pageTitle }}</h4>
        </div>
        <div class="card-action">
            {!! $headerAction ?? '' !!}
        </div>
      </div>
      <hr>
      <div class="card-body">
        <form id = "frmTracker">
          <div class="row g-3 align-items-center">
            <div class="col-auto">
              <input type="text" class = "form-control" name = "StudentNo" id = "StudentNo" autofocus>
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
                <button class = "btn btn-primary" type = "button" id = "btnTrack"><i class = 'bx bx bx-target-lock'></i> Track</button>
              </span>
            </div>
          </div>
        </form>
        <div class="divider">
          <div class="divider-text">Tracker Result</div>
        </div>
        <div id="trackermsg"></div>
      </div>
    </div>
  </div>
</div>

@endsection

@section('page-script')
@include('slsu.department.js')
@endsection
