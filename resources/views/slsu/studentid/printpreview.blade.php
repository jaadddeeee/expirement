@extends('layouts/contentNavbarLayout')

@section('title', $pageTitle)

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-style1">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Home</a>
            </li>
            <li class="breadcrumb-item">
                <a href="/request/student-id">School Card ID</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ url('request/process-id/' . Crypt::encryptString($student->StudentNo)) }}">Process ID</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                {{ $pageTitle }}
            </li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title m-0">{{ $pageTitle }}</h5>
                <div class="ms-3">
                    {!! $headerAction ?? '' !!}
                </div>
            </div>
            <hr>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-center gap-4">
                <!-- Front Image Container -->
                <div class="border"
                    style="width: 50%; height: 1000px; background-image: url('{{ asset('images/front.png') }}'); 
                    background-size: cover; background-position: center;">

                    <div class="container" style="margin-top: 88px;">
                        <h3 class=""
                            style="font-family: 'Trajan Pro', serif; font-size: 34px; color: rgb(0, 0, 0); position: relative; left: 190px;">
                            Southern Leyte
                        </h3>

                        <h3 class="d-flex justify-content-center"
                            style="font-family: 'Trajan Pro', serif; font-size: 28px; color: rgb(0, 0, 0); position: relative; left:18px; top: -20px;">
                            State
                            University
                        </h3>

                        <p class="d-flex justify-content-center"
                            style="font-family: Arial, serif; position: relative; left: 61px; top: -35px; color: rgb(0, 0, 0);">
                            Main Campus | San Roque, Sogod, Southern Leyte
                        </p>

                    </div>
                </div>

                <!-- Back Image Container -->
                <div class="border"
                    style="width: 50%; height: 1000px; background-image: url('{{ asset('images/back.png') }}'); 
                    background-size: cover; background-position: center;">
                </div>
            </div>
        </div>

        <div class="card-footer">
            <div class="text-end">
                <hr>
                <a href="{{ route('student.print', Crypt::encryptString($student->StudentNo)) }}"
                    class="btn btn-primary mt-2 mb-2">
                    <i class='bx bxs-printer me-1'></i><span>Print</span>
                </a>
            </div>
        </div>
    </div>
@endsection
