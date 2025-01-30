<label>Student Number: </label>
<div class = "mb-2 fw-bold ">{{$one->StudentNo}}</div>
<label>FIRST NAME: </label>
<div class = "mb-2 fw-bold ">{{strtoupper(utf8_decode($one->FirstName))}}</div>
<label>MIDDLE NAME: </label>
<div class = "mb-2 fw-bold ">{{strtoupper(utf8_decode($one->MiddleName))}}</div>
<label>LAST NAME: </label>
<div class = "mb-2 fw-bold ">{{strtoupper(utf8_decode($one->LastName))}}</div>
<label>Course: </label>
<div class = "mb-2 fw-bold ">{{$one->course->accro}}</div>

<div class = "fw-bold alert alert-danger ">Disability:<br>{{(empty($one->Disability)?"NONE":$one->Disability)}}</div>
<button class = "mt-1 btn btn-sm btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasoneSMSStudent" aria-controls="offcanvasBackdrop">Send SMS</button>

