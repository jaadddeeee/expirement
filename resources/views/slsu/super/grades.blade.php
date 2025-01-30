
<div class="row">

        <div class="table-responsive">
          <table class="table table-hover table-sm">
            <thead>
              <tr>
                <th class="text-nowrap">CourseNo</th>
                <th class="text-nowrap">Descriptive Title</th>
                <th class="text-nowrap">Instructor</th>
                <th class="text-nowrap text-center">Units</th>
                <th class="text-nowrap text-center">Credits</th>
                <th class="text-nowrap text-center">MT</th>
                <th class="text-nowrap text-center">FT</th>
                <th class="text-nowrap text-center">Re-Ex</th>
                <th class="text-nowrap text-center">Final</th>
              </tr>
            </thead>
            <tbody>
              @foreach($infos as $info)
                  <?php
                    $bg = "alert alert-primary";
                    $caption = "<strong>VALIDATED</strong> - ".
                      GENERAL::setSchoolYearLabel($info['SchoolYear'],$info['Semester'],$Campus)."-".
                      GENERAL::Semesters()[$info['Semester']]['Long'];
                    if ($info['Finalize'] != 1){
                      $bg = "alert alert-warning";
                      $caption = "<strong>PENDING</strong> - ".
                      GENERAL::setSchoolYearLabel($info['SchoolYear'],$info['Semester'],$Campus)."-".
                      GENERAL::Semesters()[$info['Semester']]['Long'];
                    }
                  ?>
                  <tr class = "{{$bg}}">
                    <td colspan = 2>{!!$caption!!}</td>
                    <td colspan = 6>{{$info['Course']}}{{empty($info['Major'])?"":($info['Major'] == "NONE"?"":"-".$info['Major'])}} {{(empty($info['StudentYear'])?'':$info['StudentYear'])}}{{(empty($info['Section'])?'':'-'.(strlen($info['Section'])==1?strtoupper($info['Section']):$info['Section']))}}</td>
                    <td>{!!$info['Status']!!}</td>
                  </tr>
                  @if(empty($info['Subjects']))
                    <tr>
                      <td colspan = 9>NO ENROLLED SUBJECT</td>
                    </tr>
                  @else
                    <?php
                      $unitsenrolled = 0;
                      $runningTotal = 0;
                      $runningUnit = 0;
                      $EarnedUnits = 0;

                    ?>
                    @foreach($info['Subjects'] as $subject)
                        <?php
                            $earned = 0;
                          if ($subject->exempt != 1){

                            if ($subject->ExcludeinAVG == 0){
                              $unitsenrolled += (empty($subject->units)?0:$subject->units);

                              $out = GENERAL::ComputeForGWA($subject->final, $subject->inc, $subject->units);
                              $runningTotal += $out['RunningTimes'];
                              $runningUnit += $out['RunningUnit'];
                              $EarnedUnits += $out['UnitsEarned'];
                              $earned = $out['UnitsEarned'];
                            }

                          }
                        ?>
                        <tr>
                          <td class="text-nowrap">{{$subject->courseno}}</td>
                          <td class="text-nowrap">{!!wordwrap($subject->coursetitle,37, "\n<br>")!!}</td>
                          <td class="text-nowrap">{{(empty($subject->LastName)?'':$subject->LastName.(empty($subject->FirstName) ?'':', '.$subject->FirstName .(empty($subject->MiddleName)?'':' '.$subject->MiddleName[0].'.')))}}</td>
                          <td class="text-nowrap text-center">{{($subject->exempt == 1?"(".$subject->units.")":($subject->ExcludeinAVG == 1?"(".$subject->units.")":$subject->units))}}</td>
                          <td class="text-nowrap text-center">{{($subject->exempt == 1?"(".$earned.")":($subject->ExcludeinAVG == 1?"(".$earned.")":$earned))}}</td>
                          <td class="text-nowrap text-center">{!!(empty($subject->midterm)?"":GENERAL::GradeRemarks($subject->midterm,1))!!}</td>
                          <td class="text-nowrap text-center">{!!(empty($subject->finalterm)?"":GENERAL::GradeRemarks($subject->finalterm,1))!!}</td>
                          <td class="text-nowrap text-center">{!!(empty($subject->inc)?"":($subject->inc=="0.0"?"":GENERAL::GradeRemarks($subject->inc,1)))!!}</td>
                          <td class="text-nowrap text-center">{!!GENERAL::GradeRemarks($subject->final,1,'fw-bolder')!!}</td>
                        </tr>
                    @endforeach
                    <?php
                      if (empty($runningUnit)){
                        $gwa = 0;
                      }else{
                        $gwa = ($runningTotal / $runningUnit);
                      }

                    ?>

                      <tr>
                          <td class="text-nowrap"></td>
                          <td class="text-nowrap text-end"></td>
                          <td class="text-nowrap text-end"></td>
                          <td class="text-nowrap text-center fw-bolder">Units Enrolled</td>
                          <td class="text-nowrap text-center fw-bolder">Units Earned</td>
                          <td class="text-nowrap text-center"></td>
                          <td class="text-nowrap text-center"></td>
                          <td class="text-nowrap text-center"></td>
                          <td class="text-nowrap text-center fw-bolder">GWA</td>
                      </tr>
                      <tr>
                          <td class="text-nowrap"></td>
                          <td class="text-nowrap text-end"></td>
                          <td class="text-nowrap text-end"></td>
                          <td class="text-nowrap text-center fw-bolder">{{$unitsenrolled}}</td>
                          <td class="text-nowrap text-center fw-bolder">{{$EarnedUnits}}</td>
                          <td class="text-nowrap text-center"></td>
                          <td class="text-nowrap text-center"></td>
                          <td class="text-nowrap text-center"></td>
                          <td class="text-nowrap text-center fw-bolder">{{number_format($gwa,3,'.','')}}</td>
                      </tr>
                  @endif
              @endforeach
            </tbody>
          </table>
        </div>

</div>
