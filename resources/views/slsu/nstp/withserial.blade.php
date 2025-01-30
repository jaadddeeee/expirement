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
      </div><hr class = "m-0">
      <div class="card-body">
        @if (count($students) <= 0)
                {!! GENERAL::Error("No student enrolled") !!}
        @else
          <div class = "row">
            <div class = 'col-xs-12 col-lg-12'>
              <div class = "table-responsive">

                  <table class = 'mb-5 table table-hover table-sm'>
                      <thead>
                          <tr>
                            <td>#</td>
                            <td>StudentNo</td>
                            <td>Name</td>
                            <td>Course</td>
                            <td>Major</td>
                            <td>Serial Number</td>
                          </tr>
                      </thead>
                      <tbody>

                  @foreach($students as $enrolled)
                      <tr>
                        <td class = 'text-nowrap'>{{(isset($ctr)?++$ctr:$ctr=1)}}</td>
                        <td class = 'text-nowrap'>{{$enrolled->StudentNo}}</td>
                        <td class = 'text-nowrap'>{{ucwords(strtolower(utf8_decode($enrolled->LastName . ', '. $enrolled->FirstName)))}}</td>
                        <td class = 'text-nowrap'>{{(empty($enrolled->course->accro)?"":$enrolled->course->accro)}}</td>
                        <td class = 'text-nowrap'>{{(isset($enrolled->major->course_major)?$enrolled->major->course_major:"")}}</td>
                        <td class = 'text-nowrap'>{{$enrolled->NSTPSerial}}</td>
                      </tr>
                  @endforeach
                      </tbody>
                  </table>
                  {{$students->links()}}

              </div>
            </div>

          </div>
        @endif
      </div>
    </div>
  </div>
</div>

@endsection

@section('page-script')
<script src="{{asset('storage/js/teacher.js?id=20230819a')}}"></script>
@endsection
