<?php
    function getData($data, $info = []){

        $units = 0;
        foreach($data as $d2){
            foreach($d2 as $d){

              if ($d['Course'] == $info['Course'] and $d['SchoolYear'] == $info['SchoolYear'] and $d['Semester'] == $info['Semester']){
                  $units = $d['SumLab'] + $d['SumLec'];
                  break;
              }
            }
        }

        return $units;
    }

    function geHeads($data, $info = []){

      $heads = 0;
      foreach($data as $d2){
          foreach($d2 as $d){

            if ($d['Course'] == $info['Course'] and $d['SchoolYear'] == $info['SchoolYear'] and $d['Semester'] == $info['Semester']){
                $heads = $d['CountHead'] ;
                break;
            }
          }
      }

      return $heads;
  }

?>

<div class = "table-responsive">
    <table class = "mt-3 table table-sm table-bordered">
        <thead>
          <tr>
            <td rowspan = "3" class = "text-nowrap">Program/Course</td>
            @for($start=$From;$start<=$To;$start++)
                <td colspan = "4" class = "text-center text-nowrap">School year {{$start."-".($start+1)}}</td>
            @endfor
          </tr>
          <tr>

          @for($start=$From;$start<=$To;$start++)
            @foreach($Sems as $sem)
              <td colspan = "2" class = "text-center text-nowrap">{{GENERAL::Semesters()[$sem]['Short']}} Sem</td>
            @endforeach
          @endfor
          </tr>
          <tr>

          @for($start=$From;$start<=$To;$start++)
            @foreach($Sems as $sem)
              <td  class = "text-center text-nowrap">Total Head Count</td>
              <td  class = "text-center text-nowrap">Total Enrolled Units</td>
            @endforeach
          @endfor
          </tr>


        </thead>
        <tbody>
          <tr>
              <td colspan = "9" class = "fw-bolder">a. Undergraduate (UG)</td>
          </tr>
          @foreach($courses as $course)
            @if($course->lvl == "Under Graduate")
              <tr>
                  <td class = "text-nowrap">{{$course->course_title}}</td>
                  @for($start=$From;$start<=$To;$start++)
                      @foreach($Sems as $sem)
                          <?php

                              $sumUnits = getData($out, ['Course' => $course->id, 'SchoolYear' => $start, 'Semester' => $sem]);
                              $Countheads =  geHeads($heads_array, ['Course' => $course->id, 'SchoolYear' => $start, 'Semester' => $sem]);;
                          ?>
                          <td class = "text-nowrap text-center">{{$Countheads}}</td>
                          <td class = "text-nowrap text-center">{{$sumUnits}}</td>

                      @endforeach
                  @endfor
              </tr>
            @endif
          @endforeach

          <tr>
              <td colspan = "9" class = "fw-bolder">b. Graduate Studies(GS)</td>
          </tr>
          @foreach($courses as $course)
            @if($course->lvl != "Under Graduate")
            <tr>
                  <td class = "text-nowrap">{{$course->course_title}}</td>
                  @for($start=$From;$start<=$To;$start++)
                      @foreach($Sems as $sem)
                          <?php

                              $sumUnits = getData($out, ['Course' => $course->id, 'SchoolYear' => $start, 'Semester' => $sem]);
                              $Countheads =  geHeads($heads_array, ['Course' => $course->id, 'SchoolYear' => $start, 'Semester' => $sem]);;
                          ?>
                          <td class = "text-nowrap text-center">{{$Countheads}}</td>
                          <td class = "text-nowrap text-center">{{$sumUnits}}</td>

                      @endforeach
                  @endfor
              </tr>
            @endif
          @endforeach


        </tbody>
    </table>

</div>
