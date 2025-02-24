
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
        <div class="card-action d-flex align-items-center gap-2">
            <form method="GET" action="{{ route('varsity') }}">
              <div class="d-flex align-items-center gap-2">
                <input type="search" name="search" id="search" class="form-control form-control-sm" placeholder="Search" value="{{ request('search') }}">
                <select class="form-select form-select-sm w-auto" name="filterSchoolYear" id="filterSchoolYear">
                  <option value="0">Select SchoolYear</option>
                  @foreach(GENERAL::SchoolYears() as $index => $sy)
                      <option value="{{$sy}}" {{ request('filterSchoolYear') == $sy ? 'selected' : '' }}>{{$sy}}</option>
                  @endforeach
              </select>
              <select class="form-select form-select-sm w-auto" name="filterSemester" id="filterSemester">
                  <option value="0">Select Semester</option>
                  @foreach(GENERAL::Semesters() as $index => $sem)
                      <option value="{{$index}}" {{ request('filterSemester') == $index ? 'selected' : '' }}>{{$sem['Long']}}</option>
                  @endforeach
              </select>
                <select name="filterCampus" id="filterCampus" class="form-select form-select-sm w-auto">
                  <option value="0">Select campus</option>
                  @foreach(GENERAL::Campuses() as $campus)
                    <option value="{{$campus['ID']}}" {{ request('filterCampus') == $campus['ID'] ? 'selected' : '' }}>
                      {{$campus['Campus']}}
                    </option>
                  @endforeach
                </select>
                <button type="submit" class="btn btn-warning btn-sm" id="btn-filter">Filter</button>
              </div>
              <div class="d-flex mt-1 gap-2 justify-content-end">
                {!! $headerAction ?? '' !!}
                <a href="#" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalVarsity" aria-controls="offcanvasBackdrop">New</a>
                <a href="#" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalVarsity" aria-controls="offcanvasBackdrop">New</a>
            </div>
            </form>
        </div>
      </div>
      <hr>
      <div class="card-body">
          <div class="table-responsive">
              <table class="table table-sm table-hover datatable">
                  <thead>
                    <tr>
                      <td class="text-nowrap">#</td>
                      <th class="text-nowrap">Varsity Name</th>
                      <th class="text-nowrap">Varsity Event</th>
                      <th class="text-nowrap">SY/Sem</th>
                      <th class="text-nowrap">Action</th>
                    </tr>
                  </thead>
                  <tbody id="data">
                    @include('_partials.var_student-table')
                  </tbody>
              </table>
              <div class="d-flex justify-content-end mt-2">
                <nav aria-label="Page navigation">
                  <ul class="pagination pagination-sm">
                    {{ $varsities->appends(request()->query())->links() }}
                  </ul>
                </nav>
              </div>
          </div>
      </div>
    </div>
  </div>
</div>

@include('slsu.varsity.VAR_student.modal_student')

@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@include('slsu.varsity.VAR_student.js')

@endsection
