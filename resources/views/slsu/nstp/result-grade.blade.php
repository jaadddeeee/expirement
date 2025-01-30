<div class="table-responsive">
    <table class="table table-sm">
        <thead>
            <tr>
              <td>Subject</td>
              <td>School Year</td>
              <td>Semester</td>
              <td>Grade</td>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $g)

                <tr>
                  <td class = "text-nowrap">{{$g['Subject']}}</td>
                  <td class = "text-nowrap">{{GENERAL::setSchoolYearLabel($g['SchoolYear'],$g['Semester'])}}</td>
                  <td class = "text-nowrap">{{GENERAL::Semesters()[$g['Semester']]['Long']}}</td>
                  <td class = "text-nowrap">{!!GENERAL::GradeRemarks($g['Grade'],1,'fw-bold')!!}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
