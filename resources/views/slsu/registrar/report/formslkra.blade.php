
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
                    <form id = "frmFTE">
                      <div class="row g-3 align-items-center">
                          <div class="col-auto">
                            <select name="SchoolYearFrom" id="SchoolYearFrom" class="form-select">
                              <option value="0">Select School Year</option>
                                @foreach(GENERAL::SchoolYears() as $sy)
                                  <option value="{{$sy}}" <?=(session('schoolyear')==$sy?"Selected":"")?>>{{$sy."-".($sy+1)}}</option>
                                @endforeach
                            </select>
                          </div>
                          <div class="col-auto">
                            <select name="SchoolYearTo" id="SchoolYearTo" class="form-select">
                              <option value="0">Select School Year</option>
                                @foreach(GENERAL::SchoolYears() as $sy)
                                  <option value="{{$sy}}" <?=(session('schoolyear')==$sy?"Selected":"")?>>{{$sy."-".($sy+1)}}</option>
                                @endforeach
                            </select>
                          </div>
                          <div class="col-auto">
                            <span id="passwordHelpInline" class="form-text">
                              <button class = "searchwfte btn btn-primary" type = "button">View</button>
                            </span>
                          </div>
                      </div>
                    </form>

                  </div>
                </div>
            </div>

            <div class="row">
              <div class = "mt-2" id = "ajaxcall">
              </div>
            </div>
          </div>

    </div>
  </div>
</div>


@endsection

@section('page-script')
<script src="{{asset('storage/js/registrar.js?id=20240126')}}"></script>
@endsection
