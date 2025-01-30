<?php
  use App\Http\Controllers\SLSU\GradeLock;
  $lock = new GradeLock(['sy' => $passsy, 'sem' => $passsem]);

  $units = 0;
  $lec = 0;
  $lab = 0;
  $old = "";
  $withpareha = [];
  foreach($lists as $list){
    if (empty($list->schedule)){
      if ($list->subject->exempt != 1){
        $units += (isset($list->subject->units)?(empty($list->subject->units)?0:$list->subject->units):0);
        $lec += (isset($list->subject->lec)?(empty($list->subject->lec)?0:$list->subject->lec):0);
        $lab += (isset($list->subject->lab)?(empty($list->subject->lab)?0:$list->subject->lab):0);
      }
    }else{
      if ($old!=$list->schedule->tym){
        if ($list->subject->exempt != 1){
          $units += (isset($list->subject->units)?(empty($list->subject->units)?0:$list->subject->units):0);
          $lec += (isset($list->subject->lec)?(empty($list->subject->lec)?0:$list->subject->lec):0);
          $lab += (isset($list->subject->lab)?(empty($list->subject->lab)?0:$list->subject->lab):0);
        }
        $old=$list->schedule->tym;
      }else{
        $withpareha[] = $list->id;
      }
    }
  }
?>
<div class="table-responsive">
  <div class = "alert alert-info">Encoding will start on {{(empty($lock->getDateStart())?": NOT SET":date('F j, Y', strtotime($lock->getDateStart())))}}</div>
  <table class="table table-sm table-hover">
    <thead>
      <tr>
        <th class = "text-nowrap"></th>
        @foreach($days as $day)
        <th class = "text-nowrap text-center">{{strtoupper(date('l', strtotime($day)))}}</th>
        @endforeach
      </tr>
    </thead>
    <tbody>
      
      @foreach($times as $time)
        <tr>
          <th class = 'text-nowrap'>{{date('h:i A', strtotime($time))}}</th>
          @foreach($days as $day)
              <?php
                $naasched = false;
              ?>
              @foreach($allDays as $allDay)
                @if (strtolower($allDay['day']) == strtolower($day))
                   @if (strtotime($time) >= $allDay['TimeInt'] and strtotime($time)<= $allDay['TimeEndInt'])
                    <?php
                      $naasched = true;
                    ?>
                    <td class = "bg-primary text-white text-center"><small>{{$allDay['Room']}}</small> / <small>{{$allDay['CourseCode']}}</small><br><strong>{{$allDay['Subject']}}</strong></td>
                    @break
                  @endif

                @endif
              @endforeach
              @if(!$naasched)
              <td></td>
              @endif
          @endforeach

      @endforeach
    </tbody>
  </table>
  @if($clicked == "class")
  <h5 class = "mt-4">Summary</h5>
  <table class="table table-sm table-hover">
    <thead>
      <tr>
        <th class = "text-nowrap" style = "width: 250px">Number of subjects</td>
        <td class = "text-nowrap" style = "font-weight: 900">: {{count($lists)}}</td>
      </tr>

      <tr>
        <th class = "text-nowrap" style = "width: 250px">Total units</td>
        <td class = "text-nowrap" style = "font-weight: 900">: {{$units}}</td>
      </tr>

      <tr>
        <th class = "text-nowrap" style = "width: 250px">Total Lec hours</td>
        <td class = "text-nowrap" style = "font-weight: 900">: {{$lec}}</td>
      </tr>

      <tr>
        <th class = "text-nowrap" style = "width: 250px">Total Lab hours</td>
        <td class = "text-nowrap" style = "font-weight: 900">: {{$lab*3}}</td>
      </tr>

    </thead>
  </table>
  <button id = "workload" filename = "{{'workload-'.$passsy.'-'.$passsem}}.pdf" href = "#" sid = "{{Crypt::encryptstring(Auth::user()->Emp_No)}}" sy = "{{Crypt::encryptstring($passsy)}}" sem = "{{Crypt::encryptstring($passsem)}}" class = "btn btn-primary mt-2"><i class = "fa fa-print"></i> Print Preview</button>
  @endif
</div>
