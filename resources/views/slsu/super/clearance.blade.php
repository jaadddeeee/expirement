@extends('layouts/contentNavbarLayout')

@section('title', $pageTitle)

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">Super Admin </span> / {{$pageTitle}}
</h4>

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
      <hr class = "mb-0">
      <div class="card-body">
          <div class = "row">
              <form id = "frmClearance" class="row g-3">
                @csrf
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
                        <label for="Campus" class="visually-hidden">Campus</label>
                        <select class="form-select" name = "Campus" id="Campus" placeholder="Password">
                            <option value = "0">Select campus</option>
                            @foreach(GENERAL::Campuses() as $index => $campus)
                                <option value = "{{$index}}">{{$campus['Campus']}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                      <span id="passwordHelpInline" class="form-text">
                        <button class = "btn btn-primary" type = "button" id = "btnAssessClearance">Assess</button>
                      </span>
                    </div>
                </div>
              </form>

          </div>
          <hr>
          <div id = "viewclearance" class = "row"></div>
      </div>
    </div>
  </div>
</div>

@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{asset('storage/js/superadmin.js?id=20240107')}}"></script>
@endsection
