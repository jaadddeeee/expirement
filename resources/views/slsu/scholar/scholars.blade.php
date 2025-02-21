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
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="header-title">
                        <h4 class="card-title">{{ $pageTitle ?? 'List' }}</h4>
                    </div>
                    <div class="d-flex">
                        <div class="me-3">
                            {!! $headerAction ?? '' !!}
                        </div>
                        <div>
                            <input type="text" id="searchScholar" class="form-control form-control-sm"
                                placeholder="Search Scholar..." />
                        </div>
                    </div>
                </div>
                <hr>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th class="text-nowrap" style="width: 75px">#</th>
                                    <th class="text-nowrap" style="width: 300px">Student Name</th>
                                    <th class="text-nowrap" style="width: 200px">Scholarship</th>
                                    <th class="text-nowrap" style="width: 150px">Date Awarded</th>
                                    <th class="text-nowrap" style="width: 200px">Remarks</th>
                                    <th class="text-nowrap" style="width: 150px">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($scholars as $scholar)
                                    <tr>
                                        <td class="text-nowrap">{{ $loop->iteration }}</td>
                                        <td class="text-nowrap">{{ $scholar->student->LastName ?? 'N/A' }},
                                            {{ $scholar->student->FirstName ?? '' }}</td>
                                        <td class="text-nowrap">{{ $scholar->scholarship->sch_name ?? 'N/A' }}</td>
                                        <td class="text-nowrap">{{ $scholar->date_awarded ?? 'N/A' }}</td>
                                        <td class="text-nowrap">{{ $scholar->remarks }}</td>
                                        <td class="text-nowrap">
                                            {{-- <!-- Add Button Icon -->
                                            <i class="fa fa-plus-circle text-success me-2" style="cursor: pointer;"
                                                title="Add"></i> --}}

                                            <!-- Edit Button Icon -->
                                            <i class="fa fa-edit text-warning me-2" style="cursor: pointer;"
                                                {{-- onclick="editScholarship('{{ Crypt::encryptString($scholarship->id) }}')" --}} title="Edit"></i>

                                            <!-- Delete Button Icon -->
                                            <i class="fa fa-trash text-danger" style="cursor: pointer;"
                                                {{-- onclick="deleteScholarship('{{ Crypt::encryptString($scholarship->id) }}')" --}} title="Delete"></i>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-end mt-3">
                            {{ $scholars->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    @include('slsu.scholar.js')
@endsection
