@foreach($coaches as $coach)
<tr class="coaches-row">    
  <td class = "text-nowrap">{{(isset($ctr)?++$ctr:$ctr=1)}}</td>
  <td class = "text-nowrap">{{utf8_decode(strtoupper($coach->LastName.', '.$coach->FirstName.(empty($coach->MiddleName)?"":" ".$coach->MiddleName[0])))}}</td>
  <td class = "text-nowrap">
      {{ isset(GENERAL::CoachType()[$coach->CoachType]) ? GENERAL::CoachType()[$coach->CoachType]['Type'] : 'N/A' }}
  </td>
  <td class="text-nowrap">
    {{ $coach->event->event}}
</td>
  <td class = "text-nowrap text-end">
    <a href = "#" class="editCoach" cid="{{Crypt::encryptstring($coach->id)}}"><i class = 'bx bx-edit text-warning'></i></a>
    &nbsp;
      <a href = "#" class = "deleteCoach" cid = "{{Crypt::encryptstring($coach->id)}}">
        <i class='text-danger bx bx-trash'></i>
      </a>
  </td>
</tr>
@endforeach

@if ($coaches->isEmpty())
  <tr>
    <td colspan="5" class="text-center table-warning  fw-bold rounded-pill">No records found</td>
  </tr>
@endif