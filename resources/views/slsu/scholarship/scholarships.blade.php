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
                                    <td class = "text-nowrap">#</td>
                                    <td class = "text-nowrap">Scholarship Name</td>
                                    <td class = "text-nowrap text-end pe-5">Amount</td>
                                    <td class = "text-nowrap" style = "width: 250px">Type</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($scholars as $scholar)
                                    <tr>
                                        <td class = "text-nowrap">{{ isset($ctr) ? ++$ctr : ($ctr = 1) }}</td>
                                        <td class = "text-nowrap">{{ $scholar->scholar_name }}</td>
                                        <td class = "text-nowrap text-end pe-5">
                                            {{ empty($scholar->amount) ? '' : ($scholar->typ == 1 ? $scholar->amount . '%' : number_format($scholar->amount, 2, '.', ',')) }}
                                        </td>
                                        <td class = "text-nowrap">
                                            {{ GENERAL::Scholarships()[$scholar->typ]['Description'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddScholar" aria-labelledby="offcanvasBackdropLabel">
        <div class="offcanvas-header">
            <h5 id="offcanvasBackdropLabel" class="offcanvas-title"><i class = "fa fa-plus"></i> New Scholarship</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <hr>
        <form id = "frmAddScholarship">
            <div class="offcanvas-body my-auto mx-0 flex-grow-0">
                <label>Scholarship Name</label>
                <input type="text" name = "ScholarshipName" class = "mb-4 form-control" placeholder = "Scholarship Name">

                <label>Amount / Value</label>
                <input type="number" name = "Amount" class = "mb-4 form-control" placeholder = "Amount">

                <label>Type</label>
                <select class = "mb-4 form-select" name = "ScholarshipType">
                    <option value = "0"></option>
                    @foreach (GENERAL::Scholarships() as $index => $sch)
                        <option value = "{{ $index }}">{{ $sch['Description'] }}</option>
                    @endforeach
                </select>
                <button class = "mb-3 btn btn-primary" id = "btnSaveScholar">Save</button>
                <div id = "msg"></div>
            </div>
        </form>
    </div>

@endsection

@section('page-script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('storage/js/scholarship.js?id=20240418') }}"></script>
@endsection
