<?php

    function getInfo($data, $StudentNo){
        $out = ['Age' => 0, "Sex" => 0];
        foreach($data as $d){
            if ($d->StudentNo == $StudentNo){
              $age = 0;
              if (!empty($d->BirthDate)){
                $bdate = explode(" ", $d->BirthDate);
                $age = \Carbon\Carbon::parse($bdate[2]."-".$bdate[0]."-".$bdate[1])->diff(\Carbon\Carbon::now())->format('%y');
              }

              $out = ['Age' => $age, "Sex" => (strtolower($d->Sex) == "f"?2:1)];
            }
        }
        return $out;
    }

    function getGroup2($data, $info = []){

        $rating = 0;
        foreach($data as $d2){
            if ($d2->StudentNo == $info['StudentNo'] and $d2->QuestionID == $info['QuestionID']){
              $rating = $d2->Rating;
            }
        }

        return $rating;
    }

    function getGroup1($sub, $data, $info = []){
        $rating = 0;
        foreach($data as $d2){
          if ($d2->StudentNo == $info['StudentNo'] and $d2->QuestionID == $info['QuestionID']){
              foreach($sub as $s){
                  if ($s->id == $d2->Rating){
                    $rating = $s->Rating;
                  }
              }
          }
        }
        return $rating;
    }

?>

<div class = "table-responsive">
    <table class = "mt-3 table table-sm table-bordered">
        <thead>
          <tr>
              <td class = "text-nowrap text-center">Client</td>
              <td class = "text-nowrap text-center">Service Availed</td>
              <td class = "text-nowrap text-center">Type</td>
              <td class = "text-nowrap text-center">Sex</td>
              <td class = "text-nowrap text-center">Age</td>
              <td class = "text-nowrap text-center">Region</td>
              <td class = "text-nowrap text-center">CC1</td>
              <td class = "text-nowrap text-center">CC2</td>
              <td class = "text-nowrap text-center">CC3</td>
              <td class = "text-nowrap text-center">SQD0</td>
              <td class = "text-nowrap text-center">SQD1</td>
              <td class = "text-nowrap text-center">SQD2</td>
              <td class = "text-nowrap text-center">SQD3</td>
              <td class = "text-nowrap text-center">SQD4</td>
              <td class = "text-nowrap text-center">SQD5</td>
              <td class = "text-nowrap text-center">SQD6</td>
              <td class = "text-nowrap text-center">SQD7</td>
              <td class = "text-nowrap text-center">SQD8</td>
          </tr>
        </thead>
        <tbody>
          @foreach($groupResults as $res)
            <?php
                $info = getInfo($listStudents, $res[0]->StudentNo);

            ?>
            <tr>
                <td class = "text-nowrap text-center">{{(isset($ctr)?++$ctr:$ctr=1)}}</td>
                <td class = "text-nowrap text-center">1</td>
                <td class = "text-nowrap text-center">1</td>
                <td class = "text-nowrap text-center">{{$info['Sex']}}</td>
                <td class = "text-nowrap text-center">{{$info['Age']}}</td>
                <td class = "text-nowrap text-center">8</td>
                @foreach($g1 as $grp1)
                <td class = "text-nowrap text-center">{{getGroup1($subs, $results,[
                    'StudentNo' => $res[0]->StudentNo,
                    'QuestionID' => $grp1->id
                    ])}}</td>
                @endforeach
                @foreach($g2 as $grp2)
                <td class = "text-nowrap text-center">{{getGroup2($results,[
                    'StudentNo' => $res[0]->StudentNo,
                    'QuestionID' => $grp2->id
                  ])}}</td>
                @endforeach
            </tr>
          @endforeach
        </tbody>
    </table>

</div>
