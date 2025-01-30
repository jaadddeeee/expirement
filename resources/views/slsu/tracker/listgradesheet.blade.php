<div class="table-responsive">
<table class = "table table-hover table-striped">
  <thead>
    <tr>
      <td class = "text-nowrap">CourseNo</td>
      <td class = "text-nowrap">Description</td>
      <td class = "text-nowrap">CourseCode</td>
      <td class = "text-nowrap">Gradesheet Generated</td>
      <td class = "text-nowrap">Submitted to Registrar's Office</td>
      <td class = "text-nowrap">Accepted by</td>
      <td class = "text-nowrap">Action</td>
    </tr>
  </thead>
  <tbody>
    @foreach($ex as $sub)
      <tr>
        <td class = "text-nowrap">{{$sub->courseno}}</td>
        <td class = "text-nowrap">{{$sub->coursetitle}}</td>
        <td class = "text-nowrap">{{$sub->coursecode}}</td>
        <td class = "text-nowrap">{{(empty($sub->DateGenerated)?"":date('F j, Y', strtotime($sub->DateGenerated)))}}</td>
        <td class = "text-nowrap">{{(empty($sub->DateAccepted)?"":date('F j, Y', strtotime($sub->DateAccepted)))}}</td>
        <td class = "text-nowrap">{{(empty($sub->AcceptedBy)?"":$sub->FirstName." ".$sub->LastName)}}</td>
        <td class = "text-nowrap">{!! (!empty($sub->AcceptedBy)?"":(empty($sub->DateGenerated)?"":"<a class = 'acceptgradesheet btn btn-success btn-sm' href = '".Crypt::encryptstring($sub->id)."'><i class = 'fa fa-thumbs-up'></i> Accept</a>"))!!}</td>
      </tr>
    @endforeach
  </tbody>
</table>
</div>
