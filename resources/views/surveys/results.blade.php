<?php
  use App\Http\Controllers\SLSU\SurveyController;
  $surveyC = new SurveyController();
  function getInfo($data = [], $snum){
    $out = '';
    foreach($data as $d){
      if ($d->StudentNo == $snum){
        $out = $d;
        break;
      }
    }

    return $out;
  }

  function getResponses($data = [], $snum){
    $out = '';
    foreach($data as $d){
      if ($d->StudentNo == $snum){
        $out = $d;
        break;
      }
      return $out;
    }
  }

?>
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


  <div class="card mt-2">
    <div class="row m-2">
        <div class="table-responsive">
          <table class = "table table-sm table-bordered">
            <thead>
              <tr>
                <th class = "text-nowrap">#</th>
                <th class = "text-nowrap">StudentNo</th>
                <th class = "text-nowrap">Last Name</th>
                <th class = "text-nowrap">First Name</th>
                <th class = "text-nowrap">Sex</th>
                <th class = "text-nowrap">Course</th>
                <th class = "text-nowrap">Major</th>
                @foreach($survey->questions as $question)
                <th>{{$question->question}}</th>
                @endforeach
              </tr>
            </thead>
            <tbody>
              @foreach($respondents as $respondent)
                  <?php
                      set_time_limit(0);
                      $student = getInfo($studentinfo, $respondent->student_id);
                      $aC = $surveyC->getResponses(['StudentID' => $respondent->student_id,'SID' => $survey->id]);
                  ?>
                <tr>
                  <td>{{isset($ctr)?++$ctr:$ctr=1}}</td>
                  <td class = "text-nowrap">{{utf8_decode($student->StudentNo)}}</td>
                  <td class = "text-nowrap">{{utf8_decode($student->LastName)}}</td>
                  <td class = "text-nowrap">{{utf8_decode($student->FirstName)}}</td>
                  <td class = "text-nowrap">{{$student->Sex}}</td>
                  <td class = "text-nowrap">{{$student->accro}}</td>
                  <td class = "text-nowrap">{{$student->course_major}}</td>
                  @foreach($survey->questions as $question)
                      <?php
                      $iyanswer = [];
                      $loc = '';
                      foreach($aC as $ans){
                        if ($ans->question_id == $question->id){
                          if (!empty($ans->answer))
                          $iyanswer[] = $ans->answer;
                          else{
                            $loc = '';
                            if ($question->type == 'decimal'){
                              // dd($ans->response);
                              $loc = 'text-end';
                              if (is_numeric($ans->response)){
                                $iyanswer[] = number_format($ans->response,2);
                              }else{
                                $iyanswer[] = $ans->response;
                              }
                            }else{
                              $iyanswer[] = $ans->response;
                            }

                          }

                        }
                      }
                      ?>
                      <td class = "text-nowrap {{$loc}}">{!!implode("<br>",$iyanswer)!!}</td>
                  @endforeach
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
    </div>
  </div>

@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@include('surveys.js');
@endsection



