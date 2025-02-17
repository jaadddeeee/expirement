
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
            <a href = "#" class = "btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalEvent" aria-controls="offcanvasBackdrop">New</a>
        </div>
      </div>
      <hr>
      <div class="card-body">
          <div class="table-responsive">
              <table class="table table-sm table-hover datatable">
                  <thead>
                    <tr>
                      <td class = "text-nowrap w-25">#</td>
                      <th class="text-nowrap">Event</th>
                      <th class="text-nowrap text-end">Action</th>
                    </tr>
                  </thead>
                  <tbody> 
                    @foreach($events as $event)
                    <tr>    
                      <td class = "text-nowrap">{{(isset($ctr)?++$ctr:$ctr=1)}}</td>
                      <td class = "text-nowrap">{{$event->event}}</td>
                      <td class = "text-nowrap text-end">
                        <a href = "#" class="editEvent" data-id="{{Crypt::encryptstring($event->id)}}"><i class = 'bx bx-edit text-warning'></i></a>
                        &nbsp;
                      </td>
                    </tr>
                  @endforeach
                  </tbody>
              </table>
          </div>
      </div>
    </div>
  </div>
</div>

{{-- add modal --}}
<div class="modal fade" id="modalEvent" tabindex="-1" aria-labelledby="eventModal" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="eventModalLabel">Add Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="frmAdd">
                  @csrf
                  <div id="msg"></div>
                    <div class = "form-group">
                      <input type="text" name = "event" id="event" class = "mb-4 form-control" placeholder = "Event">
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
<div class="modal fade" id="updateModalEvent" tabindex="-1" aria-labelledby="eventModal" aria-hidden="true" style="display: none;">
  <div class="modal-dialog modal-sm">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title h4 text-warning" id="eventModalLabel">Update Event</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <form id="frmUpdate">
                @csrf
                <div id="updatemsg"></div>
                <input hidden type = "text" name = "hiddentID" id="hiddentID" value="">
                  <div class = "form-group">
                    <input type="text" name = "updateEvent" id="updateEvent" class = "mb-4 form-control" placeholder = "Event">
                  </div>
              </form>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="button" class="btn btn-warning" id="btn-update">Update</button>
          </div>
      </div>
  </div>
</div>

@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@include('slsu.varsity.js')

@endsection
