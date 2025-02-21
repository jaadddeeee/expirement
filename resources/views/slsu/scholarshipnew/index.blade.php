@extends('layouts/contentNavbarLayout')

@section('title', $pageTitle)

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-style1">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Home</a>
            </li>
            <li class="breadcrumb-item">
                <a href="javascript:void(0);">{{ $pageTitle }}</a>
            </li>
        </ol>
    </nav>

    {{-- table --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h3 class="card-title mb-0"> <strong style="color: #66a6ea;">{{ $pageTitle ?? 'List' }}</strong></h3>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <form class="d-flex align-items-center" id="filterForm" method="GET"
                            action="{{ route('scholarships') }}">
                            <input type="search" id="searchScholarship" name="search" class="form-control form-control-sm"
                                placeholder="Search" style="width: 250px; min-width: 200px;">

                            <select class="form-select form-select-sm ms-2" id="filterScholarshipType"
                                name="scholarshipType">
                                <option value="">Scholarship Types</option>
                                @foreach (GENERAL::ScholarshipsNew() as $index => $sch)
                                    <option value="{{ $index }}">{{ $sch['Description'] }}</option>
                                @endforeach
                            </select>
                            <select class="form-select form-select-sm ms-2" id="filterExternalType" name="externalType">
                                <option value="">External Types</option>
                                @foreach (GENERAL::ExternalSchType() as $index => $sch)
                                    <option value="{{ $index }}">{{ $sch['Description'] }}</option>
                                @endforeach
                            </select>
                            <div>
                                <button type="submit" class="btn btn-sm btn-warning ms-2 me-4 d-flex align-items-center"
                                    id="filterButtonScholarships">
                                    <i class="fa fa-filter me-1"></i> Filter
                                </button>
                            </div>
                        </form>

                        {{-- back button --}}
                        {!! $headerAction ?? '' !!}

                        {{-- add scholarship button --}}
                        <a href="#" class="btn btn-sm btn-success" data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvasAddScholarship" aria-controls="offcanvasBackdrop"> <i
                                class="fa fa-plus"></i> New</a>
                    </div>
                </div>

                <hr>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover datatable">
                            <thead style="background-color: #66a6ea; color: white;">
                                <tr>
                                    <th class="text-nowrap" style="color: white;">#</th>
                                    <th class="text-nowrap" style="color: white;">Scholarship Name</th>
                                    <th class="text-nowrap" style="color: white;">Type</th>
                                    <th class="text-nowrap" style="color: white;">External Type</th>
                                    <th class="text-nowrap" style="color: white;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @include('_partials.scholarships-table')
                            </tbody>
                        </table>
                        <!-- Pagination on Bottom Right -->
                        <div class="d-flex justify-content-end mt-3">
                            <div class="pagination-sm">
                                {{ $scholarships->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Saving new scholarship --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddScholarship"
        aria-labelledby="offcanvasBackdropLabel">
        <div class="offcanvas-header">
            <h5 id="offcanvasBackdropLabel" class="offcanvas-title"><i class="fa fa-plus"></i> New Scholarship</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <hr>
        <form id="frmAddScholarship">
            @csrf
            <div class="offcanvas-body my-auto mx-0 flex-grow-0">
                <label>Scholarship Name</label>
                <input type="text" name="ScholarshipName" class="mb-4 form-control" placeholder="Scholarship Name">

                <label>Scholarship Type</label>
                <select class="mb-4 form-select" name="ScholarshipType" id="scholarshipType">
                    <option value="" disabled selected>Select Scholarship Type</option>
                    @foreach (GENERAL::ScholarshipsNew() as $index => $sch)
                        <option value="{{ $index }}">{{ $sch['Description'] }}</option>
                    @endforeach
                </select>

                <!-- Additional Select Field for "External" -->
                <div id="externalOptions" style="display: none;">
                    <label>External Scholarship Type</label>
                    <select class="mb-4 form-select" name="ExternalScholarshipType">
                        <option value="" disabled selected>Select External Type</option>
                        @foreach (GENERAL::ExternalSchType() as $index => $sch)
                            <option value="{{ $index }}">{{ $sch['Description'] }}</option>
                        @endforeach
                    </select>
                </div>

                <button style="background-color: #66a6ea; color: white;" type="button" class="w-100 mb-3 btn mt-1"
                    id="btnSaveScholarship">Save</button>

                <div id="saveScholarshipMsg"></div>
            </div>
        </form>
    </div>

    {{-- update scholarship --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEditScholarship"
        aria-labelledby="offcanvasBackdropLabel">
        <div class="offcanvas-header">
            <h5 id="offcanvasBackdropLabel" class="offcanvas-title"><i class="fa fa-edit"></i> Edit Scholarship</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <hr>
        <form id="frmEditScholarship">
            @csrf
            <div class="offcanvas-body my-auto mx-0 flex-grow-0">
                <input hidden type="text" name="updateScholarshipID" id="id">

                <label>Scholarship Name</label>
                <input type="text" id="editScholarshipName" name="ScholarshipName" class="mb-4 form-control"
                    placeholder="Scholarship Name">

                <label>Scholarship Type</label>
                <select id="editScholarshipType" name="ScholarshipType" class="mb-4 form-select">
                    <option value="" disabled selected>Select Scholarship Type</option>
                    @foreach (GENERAL::ScholarshipsNew() as $index => $sch)
                        <option value="{{ $index }}">{{ $sch['Description'] }}</option>
                    @endforeach
                </select>

                <!-- Additional Select Field for "External" -->
                <div id="editExternalOptions" style="display: none;">
                    <label>External Scholarship Type</label>
                    <select id="editExternalScholarshipType" name="ExternalScholarshipType" class="mb-4 form-select">
                        <option value="" disabled selected>Select External Type</option>
                        @foreach (GENERAL::ExternalSchType() as $index => $sch)
                            <option value="{{ $index }}">{{ $sch['Description'] }}</option>
                        @endforeach
                    </select>
                </div>

                <button style="background-color: #66a6ea; color: white;" type="button" class="w-100 mb-3 btn mt-1"
                    id="btnUpdateScholarship">Update</button>

                <div id="editMsg"></div>
            </div>
        </form>
    </div>
@endsection

@section('page-script')
    @include('slsu.scholarshipnew.js')
@endsection
