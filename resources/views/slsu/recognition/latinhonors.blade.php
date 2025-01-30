
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
        @if (!empty($Error))
          <div class = "alert alert-danger">{{$Error}}</div>
        @else
          <div class="row g-3 align-items-center">
              <div class="col-auto">
                <select name="Course" id="Course" class="form-select">
                  <option value="0">Select Course</option>
                    @foreach($courses as $c)
                      <option value="{{$c->id}}">{{$c->accro}}</option>
                    @endforeach
                </select>
              </div>

              <div class="col-auto">
                <span id="passwordHelpInline" class="form-text">
                  <button class = "btn btn-primary" type = "button" id = "btnViewLatin">View</button>
                </span>
              </div>
          </div>

          <div id = "ajaxout"></div>
        @endif
      </div>
    </div>
  </div>
</div>


@endsection

@section('page-script')
<script src="{{asset('storage/js/recognition.js?id=20240224')}}"></script>
@endsection
