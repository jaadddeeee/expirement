<div class="row">
    <div class="col-sm-12">
      <div class="d-flex align-items-start align-items-sm-center gap-4">
        <div class="button-wrapper">
            <div style = "font-size: 20px; font-weight: 600">{{$one->StudentNo}} | {{strtoupper($one->FirstName. (empty($one->MiddleName)?' ':' '.$one->MiddleName[0].'. ') . $one->LastName)}}</div>
            @if(!empty($reg))
            <small>{{$reg->course->accro." (".$CurNum.")"}}</small>{!!(empty($reg->major->course_major)?"":"<small> | ".$reg->major->course_major."</small>")!!}<br>
            @else
            <small>{{$one->course->accro." (".$CurNum.")"}}</small>{!!(empty($one->Major->course_major)?"":"<small> | ".$one->Major->course_major."</small>")!!}<br>
            @endif
        </div>
      </div>
    </div>

    <div class = "row">

      <div class="col-sm-12">
        <label class = "mt-3">Please select subject</label>
        <select class = "form-select" name = "RequestedSubject" id = "RequestedSubject">
            <option value = "0">Select Course No</option>
            @foreach($pros as $pro)
              @if ($Major == $pro->major_in or empty($pro->major_in))
                <option value = "{{$pro->pri}}" <?=($getSched->id==$pro->id?"Selected":(strtolower($getSched->courseno)==strtolower($pro->courseno)?"Selected":""))?>>{{$pro->courseno}} - {{$pro->coursetitle}}</option>
              @endif
            @endforeach
        </select>
      </div>

    </div>
    <div class = "mt-2" id = "ErrorRequested"></div>
    <div class="row mt-2">
      <div class="col-sm-12">
        <button id = "btnSaveRequested" class = "btn btn-primary">Save</button>
      </div>
    </div>
</div>
