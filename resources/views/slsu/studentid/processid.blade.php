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
            <li class="breadcrumb-item active" aria-current="page">
                {{ $pageTitle }}
            </li>
        </ol>
    </nav>

    <div class="card shadow">
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
            <form action="" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <!-- Left Side: Profile Picture & Signature -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <img src="{{ asset('images/face-male.jpg') }}" id="previewProfile" alt="Profile Picture"
                                class="d-block border mx-auto mb-3"
                                style="display: none; width: 100%; height: 600px; object-fit: cover;">
                            <label for="profilePicture" class="form-label">Upload Profile Picture</label>
                            <input class="form-control" type="file" id="profilePicture" name="profilePicture"
                                accept="image/*">
                        </div>

                        <div class="mb-3">
                            <img id="previewSignature" src="" alt="Signature" class="d-block border mx-auto mb-3"
                                style="display: none; width: 100%; height: 100px; object-fit: contain;">
                            <label for="signature" class="form-label">Upload Signature</label>
                            <input class="form-control" type="file" id="signature" name="signature" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label for="province" class="form-label">SY - Sem</label>
                            <input type="text" name="province" id="province" class="form-control"
                                placeholder="Enter School Year and Semester">
                        </div>
                    </div>

                    <!-- Right Side: Additional Fields -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="studentName" class="form-label">Name</label>
                            <br>
                            <h4 class="text-uppercase">{{ $student->FirstName }}
                                {{ Str::substr($student->MiddleName, 0, 1) . '.' }}
                                {{ $student->LastName }}</h4>
                        </div>

                        <div class="mb-3">
                            <label for="studentID" class="form-label">Student ID</label>
                            <br>
                            <h4 class="text-uppercase">{{ $student->StudentNo }}</h4>
                        </div>

                        <div class="mb-3">
                            <label for="course" class="form-label">Course</label>
                            <h4>{{ $student->Course }}</h4>
                        </div>

                        <div class="mb-4">
                            <label for="specialization" class="form-label">Specialization</label>
                            <h4>{{ $student->major }}</h4>
                        </div>

                        <label class="form-label mb-3" style="color: #39DA8A; ">PERSONAL INFORMATION</label>
                        <div class="mb-3">
                            <label for="blood_type" class="form-label">BLOOD TYPE</label>
                            <input type="text" name="blood_type" id="blood_type" class="form-control"
                                placeholder="Enter Blood Type">
                        </div>

                        <div class="mb-4">
                            <label for="allergy" class="form-label">Allergy/Allergies</label>
                            <input type="text" name="allergy" id="allergy" class="form-control"
                                placeholder="Enter Allergy/Allergies">
                        </div>

                        <label class="form-label mb-3" style="color: #39DA8A; ">EMERGENCY CONTACT INFORMATION</label>
                        <div class="mb-3">
                            <label for="contact_name" class="form-label">Contact Name</label>
                            <input type="text" name="contact_name" id="contact_name" class="form-control"
                                placeholder="Enter Contact Name" value="{{ $student->emer_name }}">
                        </div>
                        <div class="mb-3">
                            <label for="contact_number" class="form-label">Contact Number</label>
                            <input type="contact" name="contact_number" id="contact_number" class="form-control"
                                placeholder="Enter Contact Number" value="{{ $student->emer_contact }}">
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="barangay" class="form-label">Barangay</label></label>
                                <input type="text" name="barangay" id="barangay" class="form-control"
                                    placeholder="Enter Barangay">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="municipality" class="form-label">Municipality</label>
                                <input type="text" name="municipality" id="municipality" class="form-control"
                                    placeholder="Enter Municipality">
                            </div>
                            <div class="col-md-4 mb-4">
                                <label for="province" class="form-label">Province</label>
                                <input type="text" name="province" id="province" class="form-control"
                                    placeholder="Enter Province">
                            </div>
                        </div>
                        <div class="row">
                            <label class="form-label mb-3" style="color: #39DA8A; ">Payment</label>
                            <div class="col-md-6 mb-3">
                                <label for="province" class="form-label">OR No.</label>
                                <input type="text" name="province" id="province" class="form-control"
                                    placeholder="Enter OR Number">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="date_paid" class="form-label">Date Paid</label>
                                <input type="date" name="date_paid" id="date_paid" class="form-control">
                            </div>
                        </div>


                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary mt-3"><i
                            class='bx bx-search-alt-2 me-1'></i><span>Print
                            Preview</span></button>
                </div>
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
