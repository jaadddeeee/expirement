
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
        <input type="text" id="allsearch" name = "allsearch" class="mb-4 typeahead form-control" placeholder="Enter Name / Student" autofocus>
        <div class="divider">
          <div class="divider-text">OR Select from the list</div>
        </div>
        <div id = "ajaxout">
          <div class="table-responsive">
            {{$registrations->links()}}
            <table class="table table-sm table-hover">
              <thead>
                <tr>
                  <th>#</th>
                  <th>StudentNo</th>
                  <th>Student Name</th>
                  <th>Course / Major</th>
                  <th>Date Requested</th>
                </tr>
              </thead>
              <tbody>
                @foreach($registrations as $reg)
                  <tr>
                    <td>{{isset($ctr)?++$ctr:$ctr=1}}</td>
                    <td><a href = "{{route('one-tes',['id' => Crypt::encryptstring($reg->StudentNo)])}}">{{$reg->StudentNo}}</a></td>
                    <td>{{utf8_decode($reg->LastName.', '.$reg->FirstName)}}</td>
                    <td>{{$reg->accro.(empty($reg->course_major)?'':' - '.$reg->course_major)}}</td>
                    <td>{{date('F j, Y', strtotime($reg->DateEnrolled))}}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
            {{$registrations->links()}}
          </div>
        </div>
        <!-- <div id = "partialout"></div> -->
      </div>

    </div>
  </div>
</div>


@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>
<script src="{{asset('storage/js/typeahead.js?id=20240419a')}}"></script>
<script src="{{asset('storage/js/tes.js?id=20241225a')}}"></script>
<script src="{{asset('storage/js/sms.js?id=20240419')}}"></script>
@endsection
