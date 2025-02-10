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
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">{{ $pageTitle ?? 'List' }}</h4>
                    </div>
                    <div class="card-action">
                        {!! $headerAction ?? '' !!}
                        <a href = "#" class = "btn btn-sm btn-success" data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvasAddScholar" aria-controls="offcanvasBackdrop">New</a>
                    </div>
                </div>
                <hr>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover datatable">
                            <thead>
                                <tr>
                                    <td class="text-nowrap">#</td>
                                    <td class="text-nowrap">Scholarship Name</td>
                                    <td class="text-nowrap" style="width: 250px">Type</td>
                                    <td class="text-nowrap" style="width: 250px">External Type</td>
                                    <td class="text-nowrap" style="width: 150px">Actions</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($scholarships as $scholarship)
                                    <tr>
                                        <td class="text-nowrap">{{ $loop->iteration }}</td>
                                        <td class="text-nowrap">{{ $scholarship->sch_name }}</td>
                                        <td class="text-nowrap">
                                            {{ GENERAL::ScholarshipsNew()[$scholarship->sch_type]['Description'] ?? 'Unknown' }}
                                        </td>
                                        <td class="text-nowrap">
                                            @if ($scholarship->sch_type == 1)
                                                N/A
                                            @else
                                                {{ GENERAL::ExternalSchType()[$scholarship->ext_type]['Description'] ?? 'Unknown' }}
                                            @endif
                                        </td>
                                        <td class="text-nowrap">
                                            <!-- Add Button Icon -->
                                            <i class="fa fa-plus-circle text-success me-2" style="cursor: pointer;"
                                                title="Add"></i>

                                            <!-- Edit Button Icon -->
                                            <i class="fa fa-edit text-warning me-2" style="cursor: pointer;"
                                                onclick="editScholarship('{{ Crypt::encryptString($scholarship->id) }}')"
                                                title="Edit"></i>

                                            <!-- Delete Button Icon -->
                                            <i class="fa fa-trash text-danger" style="cursor: pointer;"
                                                onclick="deleteScholarship('{{ Crypt::encryptString($scholarship->id) }}')"
                                                title="Delete"></i>
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

    {{-- Saving new scholarship --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddScholar" aria-labelledby="offcanvasBackdropLabel">
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

                <button class="mb-3 btn btn-primary" id="btnSaveScholar">Save</button>
                <div id="msg"></div>
            </div>
        </form>
    </div>

    {{-- edit scholarship --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEditScholar" aria-labelledby="offcanvasBackdropLabel">
        <div class="offcanvas-header">
            <h5 id="offcanvasBackdropLabel" class="offcanvas-title"><i class="fa fa-edit"></i> Edit Scholarship</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <hr>
        <form id="frmEditScholarship">
            @csrf
            <div class="offcanvas-body my-auto mx-0 flex-grow-0">
                <input hidden type="text" name="ScholarshipId" id="editScholarshipId">

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

                <button type="button" class="mb-3 btn btn-primary" id="btnUpdateScholar">Update</button>
                <div id="editMsg"></div>
            </div>
        </form>
    </div>
@endsection

@section('page-script')
    @include('slsu.scholarshipnew.js')
@endsection
