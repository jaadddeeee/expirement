<table class="table">
  <thead>
    <tr>
      <th class = "text-nowrap">#</th>
      <th class = "text-nowrap">Action</th>
      <th class = "text-nowrap">Title</th>
      <th class = "text-nowrap">Start</th>
      <th class = "text-nowrap">End</th>
      <th class = "text-nowrap">Status</th>
    </tr>
  </thead>
  <tbody>
  @foreach($surveys as $survey)
    <tr>
      <td class = "text-nowrap">{{(isset($ctr)?++$ctr:$ctr=1)}}</td>
      <td class = "text-nowrap">
        <!-- <i class='bx bx-message-alt-edit text-success' ></i>&nbsp;<i class='bx bx-trash text-danger' ></i> -->
        <a href = "{{route('one-survey', ['id' => Crypt::encryptstring($survey->id)])}}"><i title = "Add Questions" class='bx bx-message-add text-dark'></i></a>
        <a href = "{{route('results-survey', ['id' => Crypt::encryptstring($survey->id)])}}"><i title = "View Results" class='bx bx-line-chart text-success'></i></a>
      </td>
      <td class = "text-nowrap">{{$survey->title}}</td>
      <td class = "text-nowrap">{{(empty($survey->date_start)?"":date('F j, Y', strtotime($survey->date_start)))}}</td>
      <td class = "text-nowrap">{{(empty($survey->date_end)?"":date('F j, Y', strtotime($survey->date_end)))}}</td>
      <td class = "text-nowrap"></td>
    </tr>
  @endforeach
  </tbody>
</table>
