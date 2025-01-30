
@extends('layouts/contentNavbarLayout')

@section('title', $pageTitle)


@section('content')
<?php
  use App\Http\Controllers\SLSU\FacultyEvaluationController;
  $fe = new FacultyEvaluationController();
  $stars = [5,4,3,2,1];
?>
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

          <div class = "table-responsive">
              <table class="table table-sm table-hover table-bordered">
                <thead>
                  <tr>
                    <th class = "text-nowrap text-center">#</th>
                    <th class = "text-nowrap text-center">Action</th>
                    <th>Name</th>
                    @foreach($forms as $form)
                    <th colspan = 5>{{$form->Question}}</th>
                    @endforeach
                  </tr>
                  <tr>
                    <th class = "text-nowrap text-center"></th>
                    <th class = "text-nowrap text-center"></th>
                    <th></th>
                    @foreach($forms as $form)
                    @foreach($stars as $star)
                    <th>{{$star}}</th>
                    @endforeach
                    @endforeach
                  </tr>
                </thead>
                <tbody>
                  @foreach($faculties as $fac)
                  <tr>
                    <td class = "text-nowrap text-center">{{isset($ctr)?++$ctr:$ctr=1}}</td>
                    <td class = "text-nowrap text-center"><i class='bx bx-show'></i></td>
                    <td class = "text-nowrap">{{utf8_decode($fac->LastName.', '.$fac->FirstName)}}</td>
                    @foreach($forms as $form)
                      @foreach($stars as $star)
                      <td class = "text-nowrap">{{$fe->count($results, $fac->id, $form->id, $star)}}</td>
                      @endforeach
                    @endforeach
                  </tr>
                  @endforeach
                </tbody>
              </table>
          </div>
      </div>
    </div>
  </div>
</div>

@include('surveys.modal')
@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@endsection
