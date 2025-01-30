
@if (count($ress) <= 0)
    {!! GENERAL::Error("No student enrolled") !!}
@else
  <div class = "row">
    <div class = 'col-xs-12 col-lg-12'>
      <div class = "table-responsive">

          <table class = 'table table-hover table-sm'>
              <thead>
                  <tr>
                    <td>#</td>
                    <td class = 'text-nowrap'>Surname</td>
                    <td class = 'text-nowrap'>First name</td>
                    <td class = 'text-nowrap'>Middle name</td>
                    <td class = 'text-nowrap'>Course/Program</td>
                    <td class = 'text-nowrap'>Major</td>
                    <td class = 'text-nowrap'>Gender (M/F)</td>
                    <td class = 'text-nowrap'>Contact Number</td>
                    <td class = 'text-nowrap'>Email Address</td>
                    <td class = 'text-nowrap'>Province</td>
                    <td class = 'text-nowrap'>Municipality/City</td>

                    <td class = 'text-nowrap'>Barangay</td>
                  </tr>
              </thead>
              <tbody>

          @foreach($ress as $student)

                  <tr>

                    <td class = 'text-nowrap'>{{(isset($ctr)?++$ctr:$ctr=1)}}</td>
                    <td class = 'text-nowrap'>{{ucwords(strtolower(utf8_decode($student->LastName)))}}</td>
                    <td class = 'text-nowrap'>{{ucwords(strtolower(utf8_decode($student->FirstName)))}}</td>
                    <td class = 'text-nowrap'>{{ucwords(strtolower(utf8_decode($student->MiddleName)))}}</td>
                    <td class = 'text-nowrap'>{{$student->accro}}</td>
                    <td class = 'text-nowrap'>{{(empty($student->course_major)?"":$student->course_major)}}</td>
                    <td class = 'text-nowrap'>{{$student->Sex}}</td>
                    <td class = 'text-nowrap'>{{$student->ContactNo}}</td>
                    <td class = 'text-nowrap'>{{$student->email}}</td>
                    <td class = 'text-nowrap'>{{ucwords(strtolower($student->p_province))}}</td>
                    <td class = 'text-nowrap'>{{ucwords(strtolower($student->p_municipality))}}</td>

                    <td class = 'text-nowrap'>{{ucwords(strtolower($student->p_street))}}</td>
                    <td class = 'text-nowrap'>&nbsp;</td>

                  </tr>
          @endforeach
              </tbody>
          </table>


      </div>
    </div>

  </div>
@endif

