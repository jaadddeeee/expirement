
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
          <div id = "bulksms"></div>
          <div class = "table-responsive">
            <div class="row">
                  <div class="col-lg-12 col-sm-12">
                    <form id = "frmUnencoded">
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
                            <select name="department" id="department" class="form-select">
                                <option value = "{{Crypt::encryptstring(0)}}">All Department</option>
                                @foreach($departments as $dept)
                                  <option value = "{{Crypt::encryptstring($dept->id)}}">{{$dept->Description}}</option>
                                @endforeach
                            </select>
                          </div>
                          <div class="col-auto">
                            <span id="passwordHelpInline" class="form-text">
                              <button class = "searchdepartment btn btn-primary" type = "button">View</button>
                            </span>
                          </div>
                      </div>
                    </form>

                  </div>
                </div>
            </div>

            <div class="row">
              <div id = "ajaxcall">

              </div>
            </div>
          </div>

    </div>
  </div>
</div>


@endsection

@section('page-script')
<script src="{{asset('storage/js/registrar.js?id=20231229')}}"></script>
@endsection
