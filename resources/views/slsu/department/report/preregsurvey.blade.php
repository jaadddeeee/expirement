
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
        <form id = "frmPreReg">
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
                <button class = "btn btn-primary" type = "button" id = "btnViewSurveyPreReg">View</button>
              </span>
            </div>

          </div>
        </form>
        <hr class = "mt-4 mb-4">
        <div id = "ajaxout"></div>
      </div>
    </div>
  </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasoneSMSStudent" aria-labelledby="offcanvasBackdropLabel">
    <div class="offcanvas-header">
      <h5 id="offcanvasBackdropLabel" class="offcanvas-title"><i class = "fa fa-send"></i> <span id = "smsnamereceiver"></span></h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <form id = "frmSendOneSMS">
      <div class="offcanvas-body my-auto mx-0 flex-grow-0">
        <input type = "hidden" name = "hidStudentID" id = "hidStudentID">
        <div id = "onesms"></div>
        <label>Enter your message. (Limited to 155 per SMS)</label>
        <textarea name = "BulkMessage" class = "form-control mb-3" rows = "10" autofocus></textarea>
        <button type="button" id = "btnsendonesms" class="btn btn-primary mb-2  w-100">Send Now</button>
        <button type="button" class="btn btn-outline-secondary  w-100" data-bs-dismiss="offcanvas">Cancel</button>
      </div>
    </form>
</div>

@endsection

@section('page-script')
<script src="{{asset('storage/js/department.js?id=20240704')}}"></script>
<script src="{{asset('storage/js/sms.js?id=20240704')}}"></script>
@endsection
