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
                </div>

                <!-- Back Image Container -->
                <div class="border"
                    style="width: 50%; height: 1000px; background-image: url('{{ asset('images/back.png') }}'); 
                    background-size: cover; background-position: center;">
                </div>
            </div>
        </div>
    </div>
@endsection
