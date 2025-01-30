<?php
  use App\Http\Controllers\SLSU\GradeLock;
  $lock = new GradeLock(['sy' => $passsy, 'sem' => $passsem]);

  $units = 0;
  $lec = 0;
  $lab = 0;
  $old = "";
  $withpareha = [];
  foreach($lists as $list){
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
?>
<div class="table-responsive">
  <div class = "alert alert-info">Encoding will start on {{(empty($lock->getDateStart())?": NOT SET":date('F j, Y', strtotime($lock->getDateStart())))}}</div>
  <table class="table table-sm table-hover">
    <thead>
      <tr>
        <td class = "text-nowrap">Course Code</td>
        <td class = "text-nowrap">Subject</td>
        <td class = "text-nowrap">Description</td>
        <td class = "text-nowrap text-center">Units</td>
        <td class = "text-nowrap">Schedule</td>
        <td class = "text-nowrap text-center">Enrolled</td>
        @if($clicked == "grades")
        <td class = "text-nowrap">Deadline Encoding</td>
        @endif
      </tr>
    </thead>
    <tbody>
      @foreach($lists as $list)

      <?php
        $lock->setTeacherID(auth()->user()->Emp_No);
        $lock->setSchedID($list->id);
        $bg = '';
        if (in_array($list->id, $withpareha))
            $bg = 'text-warning ';

      ?>


      <tr class = '{{$bg}}'>
        <td class = "text-nowrap">{!!(empty($list->enrolled_count)?$list->coursecode:'<a href = "'.route(($clicked=='class'?'list-students':'list-grades'),['sched' => Crypt::encryptstring($list->id),'sy' => Crypt::encryptstring($passsy),'sem' => Crypt::encryptstring($passsem)]).'">'.$list->coursecode.'</a>')!!}</td>
        <td class = "text-nowrap">{{$list->subject->courseno}}</td>
        <td class = "text-nowrap">{{$list->subject->coursetitle}}{!!(!empty($bg)?" <span class = 'text-danger'>(FUSED)</span>":"")!!}</td>
        <td class = "text-nowrap text-center">{{$list->subject->units}}</td>
        <td class = "text-nowrap">{!!(isset($list->schedule->tym)?$list->schedule->tym:"").(isset($list->schedule2->tym)?'<br>'.$list->schedule2->tym:"")!!}</td>
        <td class = "text-nowrap text-center">{{$list->enrolled_count}}</td>
      </tr>
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
