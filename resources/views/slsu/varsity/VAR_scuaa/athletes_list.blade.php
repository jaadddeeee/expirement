@extends('layouts/contentNavbarLayout')

@section('title', $pageTitle)

<style>
/* From Uiverse.io by TimTrayler */ 
    .switch {
    --secondary-container: #3a4b39;
    --primary: #84da89;
    font-size: 17px;
    position: relative;
    display: inline-block;
    width: 3.7em;
    height: 1.8em;
    }

    .switch input {
    display: none;
    opacity: 0;
    width: 0;
    height: 0;
    }

    .slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #313033;
    transition: .2s;
    border-radius: 30px;
    }

    .slider:before {
    position: absolute;
    content: "";
    height: 1.4em;
    width: 1.4em;
    border-radius: 20px;
    left: 0.2em;
    bottom: 0.2em;
    background-color: #aeaaae;
    transition: .4s;
    }

    input:checked + .slider::before {
    background-color: var(--primary);
    }

    input:checked + .slider {
    background-color: var(--secondary-container);
    }

    input:focus + .slider {
    box-shadow: 0 0 1px var(--secondary-container);
    }

    input:checked + .slider:before {
    transform: translateX(1.9em);
    }
</style>

@section('content')

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-style1">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Home</a>
            </li>
            <li class="breadcrumb-item">
                <a href="javascript:void(0);">{{ $page }}</a>
            </li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between pb-1">
                    <div class="header-title">
                        <h4 class="card-title" id="pageTitle">{{ $pageTitle ?? 'List' }}</h4>
                        <h5 class="card-title">{{ $title_athlete ?? 'List' }}</h5>
                    </div>
                    <div class="card-action d-flex align-items-center gap-2">
                        <form method="GET" action="{{ route('scuaa-athletes') }}">
                            @csrf
                            <div class="d-flex align-items-center gap-2">
                                <input type="search" name="searchAthletes" id="searchAthletes" class="form-control form-control-sm"
                                    placeholder="Search" value="{{ request('searchAthletes') }}">
                                <select name="filterEvent" id="filterEvent" class="form-select form-select-sm w-auto">
                                    <option value="0">Select Event</option>
                                    @foreach ($Events as $Event)
                                        <option value="{{ $Event->id }}"
                                            {{ request('filterEvent') == $Event->id ? 'selected' : '' }}>{{ $Event->event }}
                                        </option>
                                    @endforeach
                                </select>
                                <select class="form-select form-select-sm w-auto" name="filterSchoolYear"
                                    id="filterSchoolYear">
                                    <option value="0">Select SchoolYear</option>
                                    @foreach (GENERAL::SchoolYears() as $index => $sy)
                                        <option value="{{ $sy }}"
                                            {{ request('filterSchoolYear') == $sy ? 'selected' : '' }}>{{ $sy }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-warning btn-sm" id="btn-filter"><i
                                        class='bx bxs-filter-alt'></i></button>
                            </div>
                            <div class="d-flex mt-1 gap-2 justify-content-end">
                                <a href="" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#scuaaHistory"><i class='bx bx-history'></i></a>
                                {!! $headerAction ?? '' !!}
                            </div>
                        </form>
                    </div>
                </div>
                <div class="d-flex justify-content-start container-xxl gap-2">
                    <a href="#" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#scuaaModal"
                        id="btn-list"><i class='bx bx-cog'></i>Set SCUAA</a>
                    <a href="#" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modalList"
                        id="btn-list"><i class='bx bx-cog'></i>Set Event</a>
                        @if (request('filterEvent') != 0)
                        <div class="dropdown">
                            <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                Generate Reports
                            </button>
                            <ul class="dropdown-menu p-0 m-0" aria-labelledby="dropdownMenuButton">
                                <li>
                                    <a class="dropdown-item" href="{{ route('generate.list', ['filterEvent' => request('filterEvent'), 'filterSchoolYear' => request('filterSchoolYear')]) }}" 
                                        id="btnGenerateList" >
                                        Generate Official List
                                    </a>
                                </li>
                                <hr class="dropdown-divider p-0 m-0">
                                <li>
                                    <a class="dropdown-item" href="{{ route('generate.checklist', ['filterEvent' => request('filterEvent'), 'filterSchoolYear' => request('filterSchoolYear')]) }}" 
                                        id="btnGenerateChecklist">
                                        Generate Check list
                                    </a>
                                </li>
                            </ul>
                        </div>
                    @endif
                </div>
                <hr>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover datatable">
                            <thead>
                                <tr>
                                    <td class = "text-nowrap">#</td>
                                    <th class="text-nowrap">Varsity Name</th>
                                    <th class="text-nowrap">Varsity Event</th>
                                    <th class="text-nowrap">SchoolYear</th>
                                    <th class="text-nowrap">Action</th>
                                </tr>
                            </thead>
                            <tbody id="data">
                                @include('_partials.scuaa-table')
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-end mt-2">
                            <nav aria-label="Page navigation">
                                <ul class="pagination pagination-sm">
                                    {{ $Lists->appends(request()->query())->links() }}
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('slsu.varsity.VAR_scuaa.modal_scuaa')

@endsection

@section('page-script')
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    @include('slsu.varsity.VAR_scuaa.js')

@endsection
