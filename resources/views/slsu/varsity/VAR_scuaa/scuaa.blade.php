@extends('layouts/contentNavbarLayout')

@section('title', $pageTitle)


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
                        <h4 class="card-title">{{ $pageTitle ?? 'List' }}</h4>
                        <h5 class="card-title">{{ $title ?? 'List' }}</h5>
                    </div>
                    <div class="card-action d-flex align-items-center gap-2">
                        <form method="GET" action="/varsity/scuaa">
                            @csrf
                            <div class="d-flex align-items-center gap-2">
                                <input type="search" name="search" id="search" class="form-control form-control-sm"
                                    placeholder="Search" value="{{ request('search') }}">
                                <select name="filterEvent" id="filterEvent" class="form-select form-select-sm w-auto">
                                    <option value="0">Select Event</option>
                                    @foreach ($Events as $Event)
                                        <option value="{{ $Event->id }}"
                                            {{ request('filterEvent') == $Event ? 'selected' : '' }}>{{ $Event->event }}
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
