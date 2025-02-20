
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
            </form>
            {!! $headerAction ?? '' !!}
            <a href = "#" class = "btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalVarsity" aria-controls="offcanvasBackdrop">New</a>
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
                    @include('_partials.varsity-table')
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

{{-- add modal --}}
<div class="modal fade" id="modalVarsity" tabindex="-1" aria-labelledby="coachModal" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-m">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="eventModalLabel">Add Varsity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="frmAdd">
                  @csrf
                  <div class="text-center p-1" id="msg"></div>
                  <div class="row mt-1  ">
                    <div class = "form-group">
                      <div class="col-auto">
                        <label class="form-label">Campus <span class = 'text-danger'>*</span></label>
                        <select name="Campus" id="Campus" class="form-select">
                          <option value="0">Select Campus</option>
                            @foreach(GENERAL::Campuses() as $index => $campus)
                              <option value="{{$index}}" <?=$index==$campus?"Selected":""?>>{{$campus['Campus']}}</option>
                            @endforeach
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="row all mt-2">
                    <div class="form-group">
                      <div class="col-auto">
                          <label class="form-label">Student</label>
                         <!-- Employee Input Field -->
                          <input type="text" name="Stud" id="Stud" class="form-select" placeholder="Enter student name">
                          <div id="StudentDropdown" class="dropdown-menu w-80 shadow bg-white" style="display: none; position: absolute; z-index: 1000; max-height: 200px; overflow-y: auto;">
                            <ul id="StudentList" class="list-group list-group-flush"></ul>
                          </div>
                      </div>
                    </div>
                  </div>
                  <div class="row all mt-2">
                    <div class="form-group">
                      <div class="col-auto">
                          <label class="form-label">Event</label>
                          <select name="Event" id="Event" class="form-select">
                              <option value="0">Select Event</option>
                          </select>
                      </div>
                    </div>
                  </div>
                  <div class="row all mt-2">
                    <div class="form-group">
                      <div class="col-auto">
                        <label for="Description" class="form-label">School Year:</label>
                        <select class = "form-select" name = "SchoolYear" id = "SchoolYear">
                            <option value="0"></option>
                            @foreach(GENERAL::SchoolYears() as $index => $sy)
                              <option value="{{$sy}}">{{$sy}}</option>
                            @endforeach
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="row all mt-2">
                    <div class="form-group">
                      <div class="col-auto">
                        <label for="Description" class="form-label">Semester:</label>
                        <select class = "form-select" name = "Semester" id = "Semester">
                            <option value="0"></option>
                            @foreach(GENERAL::Semesters() as $index => $sem)
                              <option value="{{$index}}">{{$sem['Long']}}</option>
                            @endforeach
                        </select>
                      </div>
                    </div>
                  </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btn-save">Save</button>
            </div>
        </div>
    </div>
</div>

{{-- update modal --}}
<div class="modal fade border" id="updateModalVar" tabindex="-1" aria-labelledby="eventModal" aria-hidden="true" style="display: none;">
  <div class="modal-dialog modal-m">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title h4 text-warning" id="eventModalLabel">Update Varsity</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="frmUpdate">
              @csrf
              <div class="text-center p-1" id="updatemsg"></div>
              <input hidden type = "text" name = "hiddentID" id="hiddentID" value="">
              <div class="row mt-1">
                <div class = "form-group">
                  <div class="col-auto">
                    <label class="form-label">Campus</label>
                    <select name="updateCampus" id="updateCampus" class="form-control" disabled>
                      <option value="0"></option>
                        @foreach(GENERAL::Campuses() as $index => $campus)
                          <option value="{{$campus['ID']}}">{{$campus['Campus']}}</option>
                        @endforeach
                    </select>
                  </div>
                </div>
              </div>
              <div class="row mt-2">
                <div class="form-group">
                  <div class="col-auto">
                      <label class="form-label">Student</label>
                     <!-- Employee Input Field -->
                      <input type="text" name="updateStud" id="updateStud" class="form-control" readonly>
                  </div>
                </div>
              </div>
              <div class="row mt-2">
                <div class="form-group">
                  <div class="col-auto">
                      <label class="form-label">Event</label>
                      <select name="updateEvent" id="updateEvent" class="form-select">
                      </select>
                  </div>
                </div>
              </div>
              <div class="row mt-2">
                <div class="form-group">
                  <div class="col-auto">
                      <label class="form-label">School Year</label>
                      <select name="updateSY" id="updateSY" class="form-select">
                        <option value="0"></option>
                        @foreach(GENERAL::SchoolYears() as $index => $sy)
                        <option value="{{$sy}}">{{$sy}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>
              <div class="row mt-2">
                <div class="form-group">
                  <div class="col-auto">
                    <label for="Description" class="form-label">Semester:</label>
                    <select class = "form-select" name = "updateSem" id = "updateSem">
                        <option value="0"></option>
                        @foreach(GENERAL::Semesters() as $index => $sem)
                          <option value="{{$index}}">{{$sem['Long']}}</option>
                        @endforeach
                    </select>
                  </div>
                </div>
              </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-warning" id="btn-update">Update Coach</button>
        </div>
      </div>
  </div>
</div>

@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@include('slsu.varsity.jsvarsity')

@endsection
