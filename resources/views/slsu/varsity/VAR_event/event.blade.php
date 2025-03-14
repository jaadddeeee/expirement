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
                <div class="card-header d-flex justify-content-between pb-1">
                    <div class="header-title">
                        <h4 class="card-title">{{ $pageTitle ?? 'List' }}</h4>
                    </div>
                    <div class="card-action">
                        {!! $headerAction ?? '' !!}
                        <a href = "#" class = "btn btn-sm btn-success" data-bs-toggle="modal"
                            data-bs-target="#modalEvent" aria-controls="offcanvasBackdrop">New</a>
                    </div>
                </div>
                <hr>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover datatable">
                            <thead>
                                <tr>
                                    <td class = "text-nowrap w-25">#</td>
                                    <th class="text-nowrap">Event</th>
                                    <th class="text-nowrap text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($events as $event)
                                    <tr>
                                        <td class = "text-nowrap">{{ isset($ctr) ? ++$ctr : ($ctr = 1) }}</td>
                                        <td class = "text-nowrap">{{ $event->event }}</td>
                                        <td class = "text-nowrap text-end">
                                            <a href = "#" class="editEvent"
                                                data-id="{{ Crypt::encryptstring($event->id) }}"><i
                                                    class = 'bx bx-edit text-warning'></i></a>
                                            &nbsp;
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

    @include('slsu.varsity.VAR_event.modal_event')

@endsection

@section('page-script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('slsu.varsity.VAR_event.js')

@endsection
