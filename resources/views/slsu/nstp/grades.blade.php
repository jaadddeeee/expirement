
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
                <div class="col-lg-6 col-sm-12">
                    <!-- <input type = "text" class = "form-control" id = "clearancesearch" > -->
                    <div class="input-group mb-3">
                      <span class="input-group-text bg-primary text-white">Search</span>
                      <input type="text" class="form-control studentname typeahead" placeholder = "Enter Student Number" id = "allsearch">
                      <span class="input-group-text"><i class = "fa fa-search"></i></span>
                    </div>
                </div>
              </div>
              <div id = "ajaxcall">
              <br><br><br><br><br><br><br><br><br><br><br><br><br>
              </div>
              <br>

          </div>
      </div>
    </div>
  </div>
</div>

@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>
@include('slsu.nstp.js')
<script src="{{asset('storage/js/typeahead.js?id=20230823')}}"></script>
@endsection
