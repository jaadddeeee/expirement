
@extends('layouts/contentNavbarLayout')

@section('title', $pageTitle)


@section('content')

<nav aria-label="breadcrumb">
  <ol class="breadcrumb breadcrumb-style1">
    <li class="breadcrumb-item">
      <a href="{{route('home')}}">Home</a>
    </li>
    <li class="breadcrumb-item">
      <a href="/surveys">View</a>
    </li>
    <li class="breadcrumb-item active">
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
          <p>Duration: {{date('F j, Y', strtotime($survey->date_start)). ' to '. date('F j, Y', strtotime($survey->date_end)) }}</p>
        </div>
        <div class="card-action">
            {!! $headerAction ?? '' !!}

        </div>
      </div>
    </div>
  </div>
</div>

@foreach($survey->questions as $question)
  <div class="card mt-2">
    <div class="card-header d-flex justify-content-between">
      <div class="header-title">
        <h5 class="card-title">{{ (isset($ctr)?++$ctr:$ctr=1) .'. '.$question->question }}</h5>

      </div>
      <div class="card-action">
          Edit
      </div>
    </div>
    <div class="card-body">
      @if($question->type == 'text')
          <div style = "border-style: dashed; border-top: 0px; border-left: 0px; border-right: 0px">Short answer text</div>
      @elseif($question->type == 'number')
          <div style = "border-style: dashed; border-top: 0px; border-left: 0px; border-right: 0px">Number answer</div>
      @elseif($question->type == 'decimal')
          <div style = "border-style: dashed; border-top: 0px; border-left: 0px; border-right: 0px">Amount answer</div>
      @elseif($question->type == 'radio')
          @foreach($question->answers as $answer)
          <div class="form-check mt-3">
            <input name="q-{{$question->id}}" class="form-check-input" type="radio" value="" id="radio-{{$answer->id}}" />
            <label class="form-check-label" for="radio-{{$answer->id}}">
            {{ $answer->answer }}
            </label>
          </div>
          @endforeach
      @elseif($question->type == 'checkbox')
          @foreach($question->answers as $answer)
          <div class="form-check mt-3">
            <input class="form-check-input" type="checkbox" value="" id="check-{{$answer->id}}">
            <label class="form-check-label" for="check-{{$answer->id}}">
            {{ $answer->answer }}
            </label>
          </div>
          @endforeach
      @endif
    </div>
  </div>
@endforeach

<div class="card mt-2 bg-warning">
    <div class="card-header d-flex justify-content-between">
      <div class="header-title">
        <h5 class="card-title text-white">Add new question</h5>

      </div>
      <div class="card-action">

      </div>

    </div>
    <div class="card-body">
          <a type="button" id="addQuestion" data-bs-toggle = "modal" data-bs-target = "#modalSurveyQuestion" class = "btn btn-primary text-white">New Question</a>
    </div>
</div>


@include('surveys.modal-question')
@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@include('surveys.js');
@endsection



