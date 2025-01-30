
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
            <a href = "#" class = "btn btn-sm btn-success" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddScholar" aria-controls="offcanvasBackdrop">New</a>
        </div>
      </div>
      <hr>
      <div class="card-body">
        <input type="text" id="allsearch" name = "allsearch" class="mb-2 typeahead form-control" placeholder="Enter Name / Student" autofocus>

        <div id = "ajaxout"></div>
        <!-- <div id = "partialout"></div> -->
      </div>

    </div>
  </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasUpdateScholar" aria-labelledby="offcanvasBackdropLabel">
  <div class="offcanvas-header">
    <h5 id="offcanvasBackdropLabel" class="offcanvas-title"><i class = "fa fa-edit"></i> Update Scholarship</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <hr>
  <form id = "frmUpdateScholarship">
      @csrf
      <div class="row m-2">
        <div class = "form-group">
        <div class="header-title">
          <label>Current Assigned Scholarship</label>
          <h4 class="card-title curSch">NONE</h4>
        </div>
          <input type = "hidden" value = "" name = "hiddenID" id = "hiddenID">
          <label>Scholarship Name</label>
          <input type="text" id="allschs" name = "allschs" class="mb-2 typeahead form-control" placeholder="Scholarship Name" autofocus>
        </div>

        <div class = "form-group mt-2">
            <button class = "btn btn-primary" id = "btnUpdateScholar">Update Scholarship</button>
        </div>

        <div id = "errorscholarship" class = "mt-3"></div>
      </div>
  </form>
</div>

@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>
<script src="{{asset('storage/js/typeahead.js?id=20240419a')}}"></script>
<script src="{{asset('storage/js/cashier.js?id=20240422')}}"></script>
<script src="{{asset('storage/js/sms.js?id=20240419')}}"></script>
@endsection
