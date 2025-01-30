@extends('layouts/contentNavbarLayout')

@section('title', $pageTitle)

@section('page-style')
<!-- Page -->
<style>


.dropdown-container {
  width: 100%;
  margin: auto 0;
  font-size: 14px;
  font-family: sans-serif;
  overflow: auto;
  border-radius: 5px;
  -webkit-box-shadow: 0px 10px 30px -4px rgba(0, 0, 0, 0.15);
  -moz-box-shadow: 0px 10px 30px -4px rgba(0, 0, 0, 0.15);
  box-shadow: 0px 10px 30px -4px rgba(0, 0, 0, 0.15); }

.dropdown-button {
  float: left;
  width: 100%;
  background: #fff;
  padding: 15px 20px;
  cursor: pointer;
  border: none;
  -webkit-box-sizing: border-box;
  box-sizing: border-box; }
  .dropdown-button .dropdown-label, .dropdown-button .dropdown-quantity {
    float: left;
    color: gray;
    font-weight: 700; }
  .dropdown-button .dropdown-quantity {
    margin-left: 4px;
    color: #ff5959; }
  .dropdown-button .fa {
    margin-top: 3px;
    float: right;
    font-size: 16px;
    color: #ff5959; }

.dropdown-list {
  float: left;
  width: 100%;
  border-top: none;
  -webkit-box-sizing: border-box;
  box-sizing: border-box;
  padding: 10px 20px;
  background: #fff; }
  .dropdown-list input[type="search"] {
    padding: 5px 10px;
    width: 100%;
    border: none;
    border-radius: 4px;
    background: rgba(0, 0, 0, 0.05); }
    .dropdown-list input[type="search"]:focus {
      -webkit-box-shadow: none;
      box-shadow: none;
      outline: none; }
  .dropdown-list ul {
    margin: 20px 0 0 0;
    max-height: 200px;
    overflow-y: auto;
    padding: 0; }
    .dropdown-list ul input[type="checkbox"] {
      position: relative;
      top: 2px; }
    .dropdown-list ul li {
      list-style: none; }

.checkbox-wrap {
  display: block;
  position: relative;
  padding-left: 35px;
  margin-bottom: 12px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 500;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none; }

/* Hide the browser's default checkbox */
.checkbox-wrap input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  height: 0;
  width: 0; }

/* Create a custom checkbox */
.checkmark {
  position: absolute;
  top: 0;
  left: 0; }

/* Create the checkmark/indicator (hidden when not checked) */
.checkmark:after {
  content: "\f0c8";
  font-family: "FontAwesome";
  position: absolute;
  color: rgba(0, 0, 0, 0.1);
  font-size: 20px;
  margin-top: -4px;
  -webkit-transition: 0.3s;
  -o-transition: 0.3s;
  transition: 0.3s; }
  @media (prefers-reduced-motion: reduce) {
    .checkmark:after {
      -webkit-transition: none;
      -o-transition: none;
      transition: none; } }

/* Show the checkmark when checked */
.checkbox-wrap input:checked ~ .checkmark:after {
  display: block;
  content: "\f14a";
  font-family: "FontAwesome";
  color: #ff5959;
  border: none;
}

</style>
@endsection


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
            <a href="#" class="regsearchstudent btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target = "#modalManualSearchStudent"><i class = "mdi mdi-account-search-outline"></i> Search</a>
        </div>
      </div>
      <hr>
      <div class="card-body">
        <div class="row justify-content-left">
          <div class="col-md-12 col-lg-5 col-sm-12 d-flex justify-content-left align-items-left">
            <div class="dropdown-container">
              <div class="dropdown-button noselect w-100">
                <div class="dropdown-label">Programs</div>
                <div class="dropdown-quantity">(<span class="quantity">Any</span>)</div>
                <i class="fa fa-chevron-down"></i>
              </div>
              <div class="dropdown-list" style="display: none;">
                <input type="search" placeholder="Search programs" class="dropdown-search"/>
                <form id = "frmRegSearchList">
                  <ul id = "coursesList"></ul>
                </form>
                <button class = "reglistsearch btn btn-sm btn-primary mt-4">Search</button>
              </div>

            </div>
          </div>
        </div>

        <div id="regselectresult" class = "mt-3 mb-2"></div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalManualSearchStudent" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel1">Search Enrollee</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <form id = "frmManualSearchStudent">
            @csrf
            <div id = "manualmsg"></div>
            <div class="row">
              <div class="col mb-3">
                <label for="str" class="form-label">Search Name / Student:</label>
                <input type="text" id="str" name = "str" class="typeahead form-control" placeholder="Enter Name / Student" autofocus>
              </div>
            </div>

            <div class="row g-2">
              <div class="col mb-0">
                <label for="Description" class="form-label">School Year:</label>
                <select class = "form-select" name = "SchoolYear" id = "SchoolYear">
                    <option value="0"></option>
                    @foreach(GENERAL::SchoolYears() as $index => $sy)
                      <option value="{{$sy}}">{{$sy}}</option>
                    @endforeach
                </select>
              </div>

              <div class="col mb-0">
                <label for="Semester" class="form-label">Semester:</label>
                <select class = "form-select" name = "Semester" id = "Semester">
                    <option value="0"></option>
                    @foreach(GENERAL::Semesters() as $index => $sem)
                      <option value="{{$index}}">{{$sem['Long']}}</option>
                    @endforeach
                </select>
              </div>

            </div>
          </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" id = "btnManualSearchStudent" class="btn btn-primary"><i class = "mdi mdi-account-search-outline"></i> Search</button>
      </div>
    </div>
  </div>
</div>

@endsection

@section('page-script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/3.5.0/lodash.min.js"></script>
<script src="{{asset('storage/js/typeahead.js?id=20230823')}}"></script>
@include('slsu.enrol.courses-js')
@include('slsu.registrar.js')
@endsection
