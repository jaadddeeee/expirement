

<table class = "table table-sm">
    <tr>
        <td>Action</td>
        <td>Student Name</td>
        <td>Course / Major</td>
        <td>SY/Sem</td>
        <td>Reason</td>
    </tr>

    @foreach($depts as $dept)

        <tr>
            <td>
              <a href = "#" class = "removeclearance" sid = "{{Crypt::encryptstring($dept->id)}}">
                <i class = "fa fa-trash text-danger"></i>
              </a>


            </td>
            <td class = "text-nowrap">{!!"<strong>".$dept->StudentNo. '</strong> ' . utf8_decode($dept->student->LastName.', '.$dept->student->FirstName . ' '.(empty($dept->student->MiddleName)?"":$dept->student->MiddleName[0]))!!}</td>
            <td class = "text-nowrap">{{utf8_decode($dept->student->course->accro.(isset($dept->student->Major->course_major)?" - ".$dept->student->Major->course_major:""))}}</td>
            <td class = "text-nowrap">{{\GENERAL::setSchoolYearLabel($dept->SchoolYear,$dept->Semester) . " - " . \GENERAL::Semesters()[$dept->Semester]['Long']}}</td>
            <td class = "text-nowrap">{{$dept->Description}}</td>
        </tr>
    @endforeach
</table>
