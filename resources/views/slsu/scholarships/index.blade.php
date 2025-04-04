@extends('layouts.contentNavbarLayout')

@section('title', $pageTitle)

@section('content')
    {{-- Nav-breadcrumb --}}
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

    {{-- Card/Table Section --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="card shadow-sm">
                <!-- Card Header -->
                <div class="card-header d-flex justify-content-between align-items-center bg-light">
                    <h3 class="card-title mb-0 text-secondary">
                        <strong>{{ $pageTitle ?? 'List' }}</strong>
                    </h3>

                    <div class="d-flex align-items-center gap-2">
                        {!! $headerAction ?? '' !!}

                        <a href="#" class="btn btn-sm btn-success" data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvasAddScholarship" aria-controls="offcanvasBackdrop">
                            <i class="fa fa-plus"></i> New
                        </a>

                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                            data-bs-target="#largeModal">
                            <i class="fa fa-plus"></i> New
                        </button>
                    </div>
                </div>

                <!-- Card Body -->
                <div class="card-body mt-3">
                    <div class="d-flex flex-wrap align-items-center gap-2 w-100">
                        <!-- Search and Filters -->
                        <form class="row w-100 g-2" id="filterForm">
                            @csrf
                            <div class="col-12 col-md-auto">
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text" id="basic-addon-search31"><i
                                            class="bx bx-search"></i></span>
                                    <input type="search" id="searchScholarship" name='searchScholarship'
                                        class="form-control form-control-sm" placeholder="Search Scholarships..."
                                        aria-label="Search Scholarships..." aria-describedby="basic-addon-search31"
                                        style="width: 250px; min-width: 200px;" />
                                </div>
                            </div>

                            <div class="col-12 col-md-auto">
                                <select style="cursor: pointer; height: 31px;" class="form-select form-select-sm"
                                    id="filterScholarshipType" name="scholarshipType">
                                    <option value="">Scholarship Types</option>
                                    @foreach (GENERAL::ScholarshipsNew() as $index => $sch)
                                        <option value="{{ $index }}">{{ $sch['Description'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-auto">
                                <select style="cursor: pointer; height: 31px;" class="form-select form-select-sm"
                                    id="filterExternalType" name="externalType">
                                    <option value="">External Types</option>
                                    @foreach (GENERAL::ExternalSchType() as $index => $sch)
                                        <option value="{{ $index }}">{{ $sch['Description'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-auto">
                                <button style="height: 31px;" type="submit" class="btn btn-sm btn-warning w-100"
                                    id="filterBtnScholarships">
                                    <i class="fa fa-filter"></i> Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Divider -->
                <hr class="my-0">

                <!-- Card Body -->
                <div class="card-body pb-2">
                    <div id="loadingIndicator" class="text-center py-3" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 mb-0">Loading scholarships...</p>
                    </div>

                    <div id="scholarshipTable" class="table-responsive text-nowrap">
                        @include('slsu.scholarships._partials._scholarships-table')
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Store Scholarship --}}
    {{-- <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddScholarship"
        aria-labelledby="offcanvasBackdropLabel">
        <div class="offcanvas-header">
            <h5 id="offcanvasBackdropLabel" class="offcanvas-title"><i class="fa fa-plus"></i> New Scholarship</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <hr>
        <form id="frmAddScholarship">
            @csrf
            <div class="offcanvas-body my-auto mx-0 flex-grow-0">
                <!-- Scholarship Name -->
                <div class="mb-4">
                    <label for="scholarshipName" class="form-label">Scholarship Name</label>
                    <input id="scholarshipName" type="text" name="ScholarshipName" class="form-control"
                        placeholder="Enter Scholarship Name">
                </div>

                <!-- Scholarship Acronym -->
                <div class="mb-4">
                    <label for="schAcronym" class="form-label">Scholarship Acronym</label>
                    <input id="schAcronym" type="text" name="SchAcronym" class="form-control"
                        placeholder="Enter Acronym">
                </div>

                <!-- Scholarship Requirements -->
                <div class="mb-4">
                    <label for="schRequirements" class="form-label">Scholarship Requirements</label>
                    <div id="requirementsContainer"
                        style="max-height: 150px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; border-radius: 5px;">
                        <div class="input-group mb-3 requirement-item">
                            <!-- Quantity Input -->
                            <input type="number" name="SchRequirementQuantities[]"
                                class="form-control quantity-input ms-2" placeholder="Qty" min="1">

                            <!-- Requirement Input -->
                            <input type="text" name="SchRequirements[]" class="form-control"
                                placeholder="Enter a requirement">

                            <!-- Remove Button -->
                            <button type="button" class="btn btn-danger btnRemoveRequirement ms-2">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-success mt-2" id="btnAddRequirement">
                        <i class="fa fa-plus"></i> Add Requirement
                    </button>
                </div>

                <!-- Scholarship Type -->
                <div class="mb-4">
                    <label for="scholarshipType" class="form-label">Scholarship Type</label>
                    <select class="form-select" name="ScholarshipType" id="scholarshipType">
                        <option value="" disabled selected>Select Scholarship Type</option>
                        @foreach (GENERAL::ScholarshipsNew() as $index => $sch)
                            <option value="{{ $index }}">{{ $sch['Description'] }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- External Scholarship Type -->
                <div id="externalOptions" class="mb-4" style="display: none;">
                    <label for="externalScholarshipType" class="form-label">External Scholarship Type</label>
                    <select class="form-select" name="ExternalScholarshipType" id="externalScholarshipType">
                        <option value="" disabled selected>Select External Type</option>
                        @foreach (GENERAL::ExternalSchType() as $index => $sch)
                            <option value="{{ $index }}">{{ $sch['Description'] }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Scholarship Provider -->
                <div class="mb-4">
                    <label for="schProvider" class="form-label">Scholarship Provider</label>
                    <input id="schProvider" type="text" name="SchProvider" class="form-control"
                        placeholder="Enter Scholarship Provider">
                </div>

                <!-- Submit Button -->
                <div class="mt-4">
                    <button type="button" class="btn btn-primary w-100 btn" id="btnStoreScholarship">
                        Create Scholarship
                    </button>
                </div>
            </div>
        </form>
    </div> --}}

    <!-- Large Modal -->
    <div class="modal fade" id="largeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel3">
                        <i class="fa fa-plus me-2"></i> New Scholarship
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <!-- Modal Body -->
                <div class="modal-body">
                    <form id="frmAddScholarship">
                        @csrf
                        <div class="row g-2 mb-3">
                            <!-- Scholarship Name -->
                            <div class="col mb-0">
                                <label for="scholarshipName" class="form-label">Scholarship Name</label>
                                <input id="scholarshipName" type="text" name="ScholarshipName" class="form-control"
                                    placeholder="Enter Scholarship Name">
                            </div>

                            <!-- Scholarship Acronym -->
                            <div class="col mb-0">
                                <label for="schAcronym" class="form-label">Scholarship Acronym</label>
                                <input id="schAcronym" type="text" name="SchAcronym" class="form-control"
                                    placeholder="Enter Acronym">
                            </div>
                        </div>

                        <!-- Scholarship Requirements -->
                        <div class="mb-3">
                            <label for="schRequirements" class="form-label">Scholarship Requirements</label>
                            <div id="requirementsContainer" class="border rounded p-3"
                                style="max-height: 300px; overflow-y: auto;">
                                <div class="input-group mb-2 requirement-item">
                                    <!-- Quantity Input -->
                                    <input type="number" name="SchRequirementQuantities[]" class="form-control"
                                        placeholder="Qty" min="1" style="width: 80px; flex: 0 0 auto;">

                                    <!-- Requirement Input -->
                                    <input type="text" name="SchRequirements[]" class="form-control ms-2"
                                        placeholder="Enter a requirement">

                                    <!-- Remove Button -->
                                    <button type="button" class="btn btn-danger btnRemoveRequirement ms-2">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-success mt-2" id="btnAddRequirement">
                                <i class="fa fa-plus"></i> Add Requirement
                            </button>
                        </div>

                        <!-- Scholarship Type -->
                        <div class="mb-3">
                            <label for="scholarshipType" class="form-label">Scholarship Type</label>
                            <select style="cursor: pointer;" class="form-select" name="ScholarshipType"
                                id="scholarshipType">
                                <option value="" disabled selected>Select Scholarship Type</option>
                                @foreach (GENERAL::ScholarshipsNew() as $index => $sch)
                                    <option value="{{ $index }}">{{ $sch['Description'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- External Scholarship Type -->
                        <div id="externalOptions" class="mb-3" style="display: none;">
                            <label for="externalScholarshipType" class="form-label">External Type</label>
                            <select style="cursor: pointer;" class="form-select" name="ExternalScholarshipType"
                                id="externalScholarshipType">
                                <option value="" disabled selected>Select External Type</option>
                                @foreach (GENERAL::ExternalSchType() as $index => $sch)
                                    <option value="{{ $index }}">{{ $sch['Description'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Scholarship Provider -->
                        <div class="mb-3" style="display: none;">
                            <label for="schProvider" class="form-label">Scholarship Provider</label>
                            <input id="schProvider" type="text" name="SchProvider" class="form-control"
                                placeholder="Enter Scholarship Provider">
                        </div>
                    </form>
                </div>
                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btnStoreScholarship">
                        <i class="fa fa-save me-1"></i> Create Scholarship
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit/Update Scholarship --}}
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
                <input type="hidden" name="id" id="scholarshipId">

                <label>Scholarship Name</label>
                <input type="text" id="editScholarshipName" name="editScholarshipName" class="mb-4 form-control"
                    placeholder="Scholarship Name">

                <label>Scholarship Type</label>
                <select id="editScholarshipType" name="editScholarshipType" class="mb-4 form-select">
                    <option value="" disabled selected>Select Scholarship Type</option>
                    @foreach (GENERAL::ScholarshipsNew() as $index => $sch)
                        <option value="{{ $index }}">{{ $sch['Description'] }}</option>
                    @endforeach
                </select>

                <!-- Additional Select Field for "External" -->
                <div id="editExternalOptions" style="display: none;">
                    <label>External Scholarship Type</label>
                    <select id="editExternalScholarshipType" name="editExternalScholarshipType" class="mb-4 form-select">
                        <option value="" disabled selected>Select External Type</option>
                        @foreach (GENERAL::ExternalSchType() as $index => $sch)
                            <option value="{{ $index }}">{{ $sch['Description'] }}</option>
                        @endforeach
                    </select>
                </div>

                <button style="background-color: #66a6ea; color: white;" type="button" class="w-100 mb-3 btn mt-1"
                    id="btnUpdateScholarship">Update Scholarship</button>

                <div id="editScholarshipMsg"></div>
            </div>
        </form>
    </div>
@endsection

@section('page-script')
    @include('slsu.scholarships.js.js')
@endsection
