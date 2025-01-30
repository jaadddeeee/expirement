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

        <div class="alert alert-info">
          Step 1. Generate payments from tuition through link first.<br>
          Step 2. Generate accounts receivable through link.
        </div>

        <div class="row g-3 align-items-center">
            <div class="col-auto">
              <label>Campus</label>
              <select name="Campus" id="Campus" class="form-select">
                <option value="0">Select Campus</option>
                  @foreach(GENERAL::Campuses() as $index => $campus)
                    <option value="{{$index}}">{{$campus['Campus']}}</option>
                  @endforeach
              </select>
            </div>

            <div class="col-auto">
              <label>Date From</label>
              <input type = "date" class = "form-control" id = "datefrom" name = "datefrom">
            </div>

            <div class="col-auto">
              <label>Date From</label>
              <input type = "date" class = "form-control" id = "dateto" name = "dateto">
            </div>


            <div class="col-auto">
              <label><br><br></label>
              <span id="passwordHelpInline" class="form-text">
                <button class = "btn btn-primary" type = "button" id = "btnGenerateTable">Generate</button>
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
@include('slsu.ar.js')
@endsection
