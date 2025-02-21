
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
          <form method="GET" action="{{ route('coaches') }}">
            <div class="d-flex align-items-center gap-2">
              <input type="search" name="search" id="search" class="form-control form-control-sm" placeholder="Search">
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
            <a href = "#" class = "btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalCoach" aria-controls="offcanvasBackdrop">New</a>
        </div>
      </div>
      <hr>
      <div class="card-body">
          <div class="table-responsive">
              <table class="table table-sm table-hover datatable">
                  <thead>
                    <tr>
                      <td class = "text-nowrap">#</td>
                      <th class="text-nowrap">Coach Name</th>
                      <th class="text-nowrap">Coach Type</th>
                      <th class="text-nowrap">Coach Event</th>
                      <th class="text-nowrap text-end">Action</th>
                    </tr>
                  </thead>
                  <tbody id="data"> 
                    @include('_partials.coach-table')
                  </tbody>
              </table>
              <div class="d-flex justify-content-end mt-2">
                <nav aria-label="Page navigation">
                  <ul class="pagination pagination-sm">
                    {{ $coaches->appends(request()->query())->links() }}
                  </ul>
                </nav>
              </div>
          </div>
      </div>
    </div>
  </div>
</div>

{{-- add modal --}}
<div class="modal fade" id="modalCoach" tabindex="-1" aria-labelledby="coachModal" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-m">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="eventModalLabel">Add Coach</h5>
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
                          <label class="form-label">Employee</label>
                         <!-- Employee Input Field -->
                          <input type="text" name="Emp" id="Emp" class="form-control" placeholder="Enter employee name">
                          <div id="EmployeeDropdown" class="dropdown-menu w-80 shadow bg-white" style="display: none; position: absolute; z-index: 1000; max-height: 200px; overflow-y: auto;">
                            <ul id="EmployeeList" class="list-group list-group-flush"></ul>
                          </div>
                      </div>
                    </div>
                  </div>
                  <div class="row all mt-2">
                    <div class="form-group">
                      <div class="col-auto">
                          <label class="form-label">Coach Type</label>
                          <select name="coachType" id="coachType" class="form-select">
                              <option value="0">Select Type</option>
                              @foreach(GENERAL::CoachType() as $index => $type)
                              <option value = "{{$index}}">{{$type['Type']}}</option>
                            @endforeach
                          </select> 
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
<div class="modal fade" id="updateModalCoach" tabindex="-1" aria-labelledby="eventModal" aria-hidden="true" style="display: none;">
  <div class="modal-dialog modal-m">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title h4 text-warning" id="eventModalLabel">Update Coach</h5>
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
                      <label class="form-label">Employee</label>
                     <!-- Employee Input Field -->
                      <input type="text" name="updateEmp" id="updateEmp" class="form-control" readonly>
                  </div>
                </div>
              </div>
              <div class="row mt-2">
                <div class="form-group">
                  <div class="col-auto">
                      <label class="form-label">Coach Type</label>
                      <select name="updateCT" id="updateCT" class="form-select">
                        <option value="0"></option>
                        @foreach(GENERAL::CoachType() as $index => $type)
                        <option value = "{{$index}}">{{$type['Type']}}</option>
                      @endforeach
                    </select>
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
@include('slsu.varsity.jscoach')

@endsection
