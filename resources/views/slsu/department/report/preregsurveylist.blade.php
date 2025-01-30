
<div class = "table-responsive">
  <table class = "table table-sm table-hover">
    <thead>
      <tr>
        <td class = "text-nowrap">Major</td>
        <td class = "text-nowrap">Year Level</td>
        <td class = "text-nowrap">Section</td>
        <td class = "text-nowrap">Limit</td>
        <td class = "text-nowrap text-center">Action</td>
      </tr>
    </thead>
    <tbody>
      @foreach($limits as $limit)
        <?php
            $res = 0;
            foreach($counts as $count){
                if ($count->Major==$limit->Major){
                  if ($count->StudentYear==$limit->StudentYear){
                    if ($count->Section==$limit->Section){
                      $res = $count->cCount;
                    }
                  }
                }
            }

            $resStatus = "<span>".$res."</span>";
            if ($res>0 and $res<$limit->MaxLimit)
              $resStatus = "<span class = 'text-primary fw-bolder'>".$res."</span>";
            elseif ($res >= $limit->MaxLimit){
              $resStatus = "<span class = 'text-danger fw-bold'>".$res."</span>";
            }
        ?>
        <tr>
          <td class = "text-nowrap">{{$limit->course_major}}</td>
          <td class = "text-nowrap">{{$limit->StudentYear}}</td>
          <td class = "text-nowrap">{{$limit->Section}}</td>
          <td class = "text-nowrap">{!!$resStatus." / ".$limit->MaxLimit!!}</td>
          <td class = "text-nowrap text-center"><a class = "showprereglist" sy = "{{Crypt::encryptstring($SchoolYear)}}" sem = "{{Crypt::encryptstring($Semester)}}" href = "{{Crypt::encryptstring($limit->id)}}"><i class='bx bx-list-ul' ></i></a></td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
