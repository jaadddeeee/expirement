<table class = "table table-sm mt-3">
  <thead>
    <tr>
      <th class = "text-nowrap">No</th>
      <th class = "text-nowrap">Last_Name</th>
      <th class = "text-nowrap">First_Name</th>
      <th class = "text-nowrap">Middle_Name</th>
      <th class = "text-nowrap">Birth_Date</th>
      <th class = "text-nowrap">Course</th>
      <th class = "text-nowrap">School</th>
      <th class = "text-nowrap">Date_Grad</th>
      <th class = "text-nowrap">SO_No</th>
      <th class = "text-nowrap">SO_Date</th>
    </tr>
  </thead>
  <tbody>
    @foreach($lists as $list)
      <?php
      $datebirth = "";
      if (!empty($list->BirthDate)){
        $tmp = explode(" ",$list->BirthDate);
        $datebirth = $tmp[2]."-".str_pad($tmp[0],2,"0",STR_PAD_LEFT)."-".str_pad($tmp[1],2,"0",STR_PAD_LEFT);
      }

      $dategrad = $list->grad;
      // var_dump($dategrad);
      if (!empty($list->grad)){
        $dategrad = date('Y-m-d', strtotime($list->grad));
      }

      ?>
      <tr>
        <td class = "text-nowrap">{{(isset($ctr)?++$ctr:$ctr=1)}}</td>
        <td class = "text-nowrap">{{strtoupper(utf8_decode($list->LastName))}}</td>
        <td class = "text-nowrap">{{strtoupper(utf8_decode($list->FirstName))}}</td>
        <td class = "text-nowrap">{{strtoupper(utf8_decode($list->MiddleName))}}</td>
        <td class = "text-nowrap">{{$datebirth}}</td>
        <td class = "text-nowrap">{{$list->course_title}}</td>
        <td class = "text-nowrap">Southern Leyte State University - {{GENERAL::Campuses()[session('campus')]['Campus']}}</td>
        <td class = "text-nowrap">{{$dategrad}}</td>
        <td class = "text-nowrap">BOR NO. {{$list->bor}}</td>
				<td class = "text-nowrap">{{$list->bordate}}</td>
      </tr>
    @endforeach
  </tbody>
</table>
