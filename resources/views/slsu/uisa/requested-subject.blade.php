
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
      <div class="card-body">
        <form id = "frmRequestedSubject">
          <div class="row g-3 align-items-center">
              <div class="col-auto">

                <input type="text" class="form-control" name = "coursecode" id = "coursecode" placeholder="Course Code" autofocus>
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
                  <button class = "btn btn-primary" type = "button" id = "btnRequestedSubject">View</button>
                </span>
              </div>
          </div>
        </form>
        <hr class = "mt-4">
        <div id = "outAjax"></div>
      </div>
    </div>
  </div>
</div>


<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasoneAddStudent" aria-labelledby="offcanvasBackdropLabel">
    <div class="offcanvas-header">
      <h5 id="offcanvasBackdropLabel" class="offcanvas-title"><i class = "fa fa-plus"></i> Add Student to requested subject</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <form id = "frmAddStudentRequested">
      <div id = "SuccessMessage"></div>
      <input type = "text" id = "hiddenCode" name = "hiddenCode" hidden>
      <input type = "text" id = "hiddenSchoolyear" name = "hiddenSchoolyear" hidden>
      <input type = "text" id = "hiddenSemester" name = "hiddenSemester" hidden>
      <div class="offcanvas-body my-auto mx-0 flex-grow-0">
        <label>Student will be added to <span id = "spncc"></span></label>
        <input type="text" id="allsearch" name = "allsearch" class="mt-2 mb-2 typeahead form-control" placeholder="Student Name / Number" autofocus>
        <button type="button" id = "btnSearchRequested" class="btn btn-primary mb-2  w-100">Search</button>
      </div>
      <div class="divider">
          <div class="divider-text text-uppercase text-muted"><b> Search Result </b></div>
      </div>
      <div class="offcanvas-body my-auto mx-0 flex-grow-0" id = "searchResultRequested">

      </div>
    </form>
</div>

@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>
<script src="{{asset('storage/js/typeahead.js?id=20240422a')}}"></script>
<script src="{{asset('storage/js/uisa.js?id=20240801s')}}"></script>
@endsection
