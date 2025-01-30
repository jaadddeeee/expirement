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
              <form id = "frmImportHRMIS" class="row g-3">
                @csrf
                <div class="row g-3 align-items-center">
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
                        <button class = "btn btn-primary" type = "button" id = "btnImportHRMIS">Import Now</button>
                      </span>
                    </div>
                </div>
              </form>

          </div>
          <hr>
          <div id = "importresult" class = "row"></div>
      </div>
    </div>
  </div>
</div>

@endsection

@section('page-script')
@include('slsu.super.js')
@endsection
