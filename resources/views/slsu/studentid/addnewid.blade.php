@extends('layouts/contentNavbarLayout')

@section('title', $pageTitle)

<style>
    .id-card {
        border: 1px solid #000;
        padding: 20px;
        max-width: 400px;
        margin: auto;
        text-align: center;
    }

    .id-card img {
        max-width: 100px;
        margin-bottom: 15px;
    }

    .id-card h2 {
        font-size: 1.5rem;
        margin-bottom: 15px;
    }

    .id-card .info {
        margin-bottom: 15px;
    }

    .id-card .info p {
        margin: 5px 0;
    }

    .id-card .signature {
        margin-top: 20px;
        border-top: 1px solid #000;
        padding-top: 10px;
    }
</style>

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-style1">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Home</a>
            </li>
            <li class="breadcrumb-item">
                <a href="/request/student-id">School Card ID</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
        </ol>
    </nav>

    <div class="content-wrapper">
        <div class="card p-4">
            <form action="" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="profilePicture" class="form-label">Upload Profile Picture</label>
                    <input class="form-control" type="file" id="profilePicture" name="profilePicture" accept="image/*">
                </div>
                <img id="previewProfile" src="" alt="Profile Picture" class="d-block mx-auto border rounded-circle"
                    style="display: none;">

                <h2>Student Name</h2>
                <div class="info">
                    <p>ID: 123456</p>
                    <p>Course: BS Information Technology</p>
                </div>

                <div class="mb-3">
                    <label for="signature" class="form-label">Upload Signature</label>
                    <input class="form-control" type="file" id="signature" name="signature" accept="image/*">
                </div>
                <img id="previewSignature" src="" alt="Signature" class="signature d-block mx-auto"
                    style="display: none; width: 100px;">

                <button type="submit" class="btn btn-primary mt-3">Save ID Card</button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('profilePicture').addEventListener('change', function(event) {
            let reader = new FileReader();
            reader.onload = function(e) {
                let img = document.getElementById('previewProfile');
                img.src = e.target.result;
                img.style.display = 'block';
            };
            reader.readAsDataURL(event.target.files[0]);
        });

        document.getElementById('signature').addEventListener('change', function(event) {
            let reader = new FileReader();
            reader.onload = function(e) {
                let img = document.getElementById('previewSignature');
                img.src = e.target.result;
                img.style.display = 'block';
            };
            reader.readAsDataURL(event.target.files[0]);
        });
    </script>
@endsection
