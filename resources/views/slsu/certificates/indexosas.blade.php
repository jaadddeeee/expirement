
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
    <div class="card m-0">
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
          <form id = "frmGenerate">

              <div class="row">
                  <div class="col-lg-4 col-sm-12 mb-3">
                      <label for="Type" class="form-label">Type:</label>
                      <select class = "form-select" name = "Type" id = "Type">
                          <option value="0"></option>
                          <option value="3">Good Moral (Graduated)</option>
                          <option value="4">Good Moral (Enrolled)</option>
<!-- ang 1 og 2 naa sa registrar 5 start sa registar-->
                      </select>
                  </div>
              </div>

              <div class="row">
                  <div class="col-lg-4 col-sm-12 mb-3">
                    <label for="allsearch" class="form-label">Search Name / Student:</label>
                    <input type="text" id="allsearch" name = "allsearch" class="typeahead form-control" placeholder="Enter Name / Student" autofocus>
                  </div>
              </div>

              <div class="row">
                  <div class="col-lg-4 col-sm-12 mb-3">
                      <label for="SchoolYear" class="form-label">School Year:</label>
                      <select class = "form-select" name = "SchoolYear" id = "SchoolYear">
                          <option value="0"></option>
                          @foreach(GENERAL::SchoolYears() as $index => $sy)
                            <option value="{{$sy}}">{{$sy}}</option>
                          @endforeach
                      </select>
                  </div>
              </div>

              <div class="row">
                  <div class="col-lg-4 col-sm-12 mb-3">
                      <label for="Semester" class="form-label">Semester:</label>
                      <select class = "form-select" name = "Semester" id = "Semester">
                          <option value="0"></option>
                          @foreach(GENERAL::Semesters() as $index => $sem)
                            <option value="{{$index}}">{{$sem['Long']}}</option>
                          @endforeach
                      </select>
                  </div>
              </div>

              <div class="row">
                  <div class="col-lg-4 col-sm-12 mb-3">
                      <label for="Purpose" class="form-label">Purpose:</label>
                      <select class = "form-select" name = "Purpose" id = "Purpose">
                          <option value="0"></option>
                          @foreach($purposes as $pur)
                            <option>{{$pur->Description}}</option>
                          @endforeach
                      </select>

                  </div>
              </div>

              <div class="row">
                  <div class="col-lg-2 col-sm-6 col-xs-12 mb-3">
                      <label for="ORNo" class="form-label">ORNo</label>
                      <input type = "number" class = "form-control" name = "ORNo" id = "ORNo">
                  </div>

                  <div class="col-lg-2 col-sm-6 col-xs-12 mb-3">
                      <label for="ORDate" class="form-label">Date Paid</label>
                      <input type = "date" class = "form-control" name = "ORDate" id = "ORDate">
                  </div>

              </div>

              <div class = "row">
                  <div class="col-lg-2 col-sm-6 col-xs-12 mb-3">
                      <button id="btnGenerate" class="btn btn-primary">Generate Certificate</button>
                  </div>
              </div>
          </form>
      </div>

    </div>
  </div>
</div>


@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>
<script src="{{asset('storage/js/osas.js?id=20231215')}}"></script>
<script src="{{asset('storage/js/typeahead.js?id=20231205b')}}"></script>
@endsection
