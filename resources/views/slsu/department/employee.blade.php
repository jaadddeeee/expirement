
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
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th class = "text-center">Action</th>
                      <th>Name</th>
                      <th>Rank</th>
                      <th>Status</th>
                      <th>Rate</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($lists as $list)
                      @if(str_contains($list->FirstName, "COS") or str_contains($list->LastName, "COS") or str_contains($list->LastName, "CCSIT"))
                      @else
                      <tr>
                        <td class = "text-nowrap">{{isset($ctr)?++$ctr:$ctr=1}}</td>
                        <td class = "text-nowrap text-center">
                          <a href = "#" class = "editemp" eid = "{{Crypt::encryptstring($list->id)}}">
                            <i class='text-success bx bx-edit'></i>
                          </a>
                          &nbsp;
                          <a href = "#" class = "deleteemp" eid = "{{Crypt::encryptstring($list->id)}}">
                            <i class='text-danger bx bx-trash'></i>
                          </a>
                        </td>
                        <td class = "text-nowrap">{!!$list->Sex=="Male"?"<i class='text-danger bx bx-male'></i>":"<i class='text-success bx bx-female'></i>"!!}{{utf8_decode(strtoupper($list->LastName.', '.$list->FirstName.(empty($list->MiddleName)?"":" ".$list->MiddleName[0])))}}</td>
                        <td class = "text-nowrap">{{(empty($list->CurrentItem)?"":$list->CurrentItem)}}</td>
                        <td class = "text-nowrap">{{(empty($list->CurrentItem)?"":$list->EmploymentStatus)}}</td>
                        <td class = "text-nowrap"></td>
                      </tr>
                      @endif
                    @endforeach
                  </tbody>
                </table>
            </div>
      </div>
    </div>
  </div>
</div>

@include('slsu.department.modal');
@endsection

@section('page-script')
@include('slsu.department.js')
@endsection
