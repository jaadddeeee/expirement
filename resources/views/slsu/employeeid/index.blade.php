@extends('layouts/contentNavbarLayout')

@section('title', $pageTitle)


@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-style1">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Home</a>
            </li>
            <li class="breadcrumb-item active">
                <a href="javascript:void(0);">{{ $pageTitle }}</a>
            </li>
        </ol>
    </nav>

    <div class="card">
        <div class="container mt-4">
            <div class="row">
                <div class="col-md-6 mb-3 d-flex align-items-center">
                    <h5 class="mb-0 flex-grow-1">{{ $pageTitle }}</h5>
                </div>
                <div class="col-md-6 mb-3 d-flex justify-content-end align-items-center">
                    <label for="searchInput" class="me-2 mb-0">Search</label>
                    <input class="form-control" type="search" placeholder="Search Employee ID or Employee Name"
                        id="search" style="max-width: 300px;" value="{{ request('search') }}" />
                </div>
            </div>
        </div>

        <div id="employee-table" class="table-responsive mb-4">
            @include('_partials.employeeid.employee-table')
        </div>
    </div>

@endsection

@section('page-script')
    @include('slsu.employeeid.js')
@endsection
