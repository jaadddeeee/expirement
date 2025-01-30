@extends('layouts/contentNavbarLayout')

@section('title', 'My Class')

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
              <select name="ViewType" id="ViewType" class="form-select">
                <option value="0">Select View</option>
                <option value="1" <?=$ViewType==1?"Selected":""?>>Daily View</option>
                <option value="2" <?=$ViewType==2?"Selected":""?>>Standard View</option>
                <option value="3" <?=$ViewType==3?"Selected":""?>>Combo View</option>
                <option value="4" <?=$ViewType==4?"Selected":""?>>Block View</option>
              </select>
            </div>

            <div class="col-auto">
              <span id="passwordHelpInline" class="form-text">
                <button class = "btn btn-primary" type = "button" id = "btnViewClassess">View</button>
              </span>
            </div>
        </div>
        <hr class = "mt-4">
        <div id = "outAjax"></div>
      </div>
    </div>
  </div>
</div>


@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{asset('storage/js/teacher.js?id=20240909')}}"></script>
<script src="{{asset('storage/js/report.js?id=20230828')}}"></script>
@endsection
