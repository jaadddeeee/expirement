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

        <div id = "outAjax"></div>
      </div>
    </div>
  </div>
</div>


@endsection

@section('page-script')
<script>
  var str = "{{$str}}";
  $.ajax({
      url: "/search/global-res-search",
      method: 'post',
      cache: false,
      data: {str},
      beforeSend:function(){
        $("#outAjax").html("<i class = 'fa fa-circle-o-notch fa-spin'></i> Searching...");
      },
      success:function(data){
        $("#outAjax").html(data);
      }
    });
  </script>
@endsection
