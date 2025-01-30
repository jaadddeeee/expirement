
@if (count($tmps) <= 0)
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
                    <td>&nbsp;</td>
                    <td class = 'text-nowrap'>Course/Program</td>
                    <td class = 'text-nowrap'>Academic Year Taken</td>
                    <td class = 'text-nowrap'>1st Sem. Grade</td>
                    <td class = 'text-nowrap'>Academic Year Taken</td>
                    <td class = 'text-nowrap'>2nd Sem. Grade</td>
                    <td class = 'text-nowrap'>Academic Year Taken</td>
                    <td class = 'text-nowrap'>Summer Grade</td>
                    <td class = 'text-nowrap'>Gender (M/F)</td>
                    <td class = 'text-nowrap'>Birthdate (YYYY-MM-DD)</td>
                    <td class = 'text-nowrap'>Municipality/City</td>
                    <td class = 'text-nowrap'>Province</td>
                    <td class = 'text-nowrap'>Contact Number</td>
                    <td class = 'text-nowrap'>Email Address</td>
                    <td class = 'text-nowrap'>NSTP Serial Number</td>
                  </tr>
              </thead>
              <tbody>

          @foreach($tmps as $student)
              @php

                $cont = false;
                $bdate = "";
                if (!empty($student->BirthDate)){
                    $tmp = explode(" ", $student->BirthDate);
                    $bdate = $tmp[2].'-'.str_pad($tmp[0],2,"0",STR_PAD_LEFT).'-'.str_pad($tmp[1],2,"0",STR_PAD_LEFT);
                }

                if ($student->grade1 <= 3  and $student->grade2 <= 3)
                  $cont = true;

              @endphp

              @if($cont)
                  <tr>

                    <td class = 'text-nowrap'>{{(isset($ctr)?++$ctr:$ctr=1)}}</td>
                    <td class = 'text-nowrap'>{{ucwords(strtolower(utf8_decode($student->LastName)))}}</td>
                    <td class = 'text-nowrap'>{{ucwords(strtolower(utf8_decode($student->FirstName)))}}</td>
                    <td class = 'text-nowrap'>{{ucwords(strtolower(utf8_decode($student->MiddleName)))}}</td>
                    <td class = 'text-nowrap'>&nbsp;</td>
                    <td class = 'text-nowrap'>{{$student->accro.(empty($student->course_major)?"":" - ".$student->course_major)}}</td>
                    <td class = 'text-nowrap'>{{(empty($student->sy1)?"":$student->sy1."-".($student->sy1+1))}}</td>
                    <td class = 'text-nowrap'>{{number_format($student->grade1, 1, '.','')}}</td>
                    <td class = 'text-nowrap'>{{(empty($student->sy2)?"":$student->sy2."-".($student->sy2+1))}}</td>
                    <td class = 'text-nowrap'>{{number_format($student->grade2, 1, '.','')}}</td>
                    <td class = 'text-nowrap'>N/A</td>
                    <td class = 'text-nowrap'>N/A</td>
                    <td class = 'text-nowrap'>{{$student->Sex}}</td>
                    <td class = 'text-nowrap'>{{$bdate}}</td>
                    <td class = 'text-nowrap'>{{ucwords(strtolower($student->p_municipality))}}</td>
                    <td class = 'text-nowrap'>{{ucwords(strtolower($student->p_province))}}</td>
                    <td class = 'text-nowrap'>{{$student->ContactNo}}</td>
                    <td class = 'text-nowrap'>{{$student->email}}</td>
                    <td class = 'text-nowrap'>&nbsp;</td>

                  </tr>
              @endif
          @endforeach
              </tbody>
          </table>


      </div>
    </div>

  </div>
@endif

