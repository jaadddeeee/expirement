
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
                      <td class = "text-nowrap">#</td>
                      <td class = "text-nowrap">Event</td>
                      <td class = "text-nowrap text-end pe-5">Status</td>
                      <td class = "text-nowrap" style = "width: 250px">Date</td>
                    </tr>
                  </thead>
                  <tbody> 
                    @php
                        $id = 1;
                    @endphp
                    @foreach($events as $event)
                    <tr>    
                      <td class = "text-nowrap">{{$id++}}</td>
                      <td class = "text-nowrap">{{$event->event}}</td>
                      <td class = "text-nowrap text-end pe-5">{{$event->status}}</td>
                      <td class="text-nowrap">{{ date('F j, Y', strtotime($event->date)) }}</td>
                    </tr>
                  @endforeach
                  </tbody>
              </table>
          </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalEvent" tabindex="-1" aria-labelledby="eventModal" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="eventModalLabel">Add Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="frmEmployee">
                  @csrf
                  <input hidden type = "text" value = "" name = "hiddentID" id = "hiddentID">
                    <div class = "form-group">
                        <label class = "text-dark" for = "FirstName">Event:</label>
                        <select class = "form-control">
                          <option></option>
                          <option value = "Part Timer">COS Faculty</option>
                        </select>
                    </div>
                    <div class = "form-group">
                        <label class = "text-dark" for = "MiddleName">Date:</label>
                        <input  type="date" class = "form-control" name="MiddleName" id="MiddleName">
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

@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{asset('storage/js/scholarship.js?id=20240418')}}"></script>

@endsection
