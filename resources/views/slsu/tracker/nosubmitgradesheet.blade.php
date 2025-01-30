
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
          <div class="row">
              <div class="table-responsive">
                  <table class="table table-sm">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th>Faculty</th>
                          <th>Course Code</th>
                          <th>Schedule</th>
                          <th>SY/Sem</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($CClists as $list)
                          <tr>
                            <td>{{isset($ctr)?++$ctr:$ctr=1}}</td>
                            <td>{{$list->LastName.', '.$list->FirstName}}</td>
                            <td>{{$list->coursecode}}</td>
                            <td>{{$list->tym}}</td>
                            <td>{{$sy.'/'.$sem}}</td>
                          </tr>
                        @endforeach
                      </tbody>
                  </table>
              </div>
          </div>
      </div>
    </div>
  </div>
</div>


@endsection

@section('page-script')
@endsection
