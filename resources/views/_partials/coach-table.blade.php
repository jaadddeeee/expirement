@foreach($coaches as $coach)
<tr class="coaches-row">
  <td class = "text-nowrap">{{(isset($ctr)?++$ctr:$ctr=1)}}</td>
  <td class = "text-nowrap">{{utf8_decode(strtoupper($coach->LastName.', '.$coach->FirstName.(empty($coach->MiddleName)?"":" ".$coach->MiddleName[0])))}}</td>
  <td class = "text-nowrap">
      {{ isset(GENERAL::CoachType()[$coach->CoachType]) ? GENERAL::CoachType()[$coach->CoachType]['Type'] : 'N/A' }}
  </td>
  <td class="text-nowrap">
    {{ $coach->event_name}}
</td>
  <td class = "text-nowrap">
    <a href = "#" class="editCoach" cid="{{Crypt::encryptstring($coach->id)}}"><i class = 'bx bx-edit text-warning'></i></a>
    &nbsp;
      <a href = "#" class = "deleteCoach" cid = "{{Crypt::encryptstring($coach->EmpNo)}}">
        <i class='text-danger bx bx-trash'></i>
      </a>
      &nbsp;
      @if (session('campus') == 'SG')
          <a href="#" class="addCoach" 
            data-bs-toggle="tooltip" 
            data-bs-offset="0,4"
            data-bs-placement="right" 
            data-bs-html="true" 
            data-bs-original-title="
            @if ($coach->alreadyExists)
              <i class='bx bxs-user-check text-success' ></i> <span class='small'>Already added</span>
            @else
              <i class='bx bxs-user-plus text-info' ></i> <span class='small'>Add coach for SCUAA</span>
            @endif
            "
            cid="{{ Crypt::encryptString($coach->EmpNo) }}" 
            data-exists="{{ $coach->alreadyExists ? 'true' : 'false' }}">
            @if ($coach->alreadyExists)
                <i class="bx bx-check-circle text-success"></i> <!-- Already exists -->
            @else
                <i class="bx bx-plus-circle"></i> <!-- Can be added -->
            @endif
          </a>   
      @endif

  </td>
</tr>
@endforeach

@if ($coaches->isEmpty())
  <tr>
    <td colspan="5" class="text-center table-warning  fw-bold rounded-pill">No records found</td>
  </tr>
@endif