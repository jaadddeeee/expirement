
<div class="header-title mt-4">
  <h4 class="card-title">{{ utf8_decode(strtoupper($tmpStudent->FirstName . (empty($tmpStudent->MiddleName)?' ':' '.$tmpStudent->MiddleName[0].'. ') .$tmpStudent->LastName)) }} {!!($tmpStudent->Sex=="M"?'<i class = "fa fa-male text-primary"></i>':'<i class = "fa fa-female text-danger"></i>')!!}</h4>
  <label>{{$tmpStudent->StudentNo}} / {{$tmpStudent->accro." (".$tmpStudent->cur_num.")"}}{{(empty($tmpStudent->course_major)?"":" / ".$tmpStudent->course_major)}}</label><br>
  <label><i class = "fa fa-mobile"></i> {!!(empty($tmpStudent->ContactNo)?"No registered mobile":'<a href = "#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasoneSMSStudent" aria-controls="offcanvasBackdrop">'.$tmpStudent->ContactNo.'</a>')!!} <i class = "fa fa-send-o"></i> {!!(empty($tmpStudent->email)?"No e-mail registered":'<a href = "mailto:'.$tmpStudent->email.'">'.$tmpStudent->email.'</a>')!!}</label>
</div>
<div class="table-responsive mt-3">
    <table id="example" class="table table-sm table-hover">
        <thead>
            <tr >
                <th>&nbsp;</th>
                <th class = 'text-nowrap'>SchoolYear</th>
                <th class = 'text-nowrap text-end'>Tuition Fees</th>
                <th class = 'text-nowrap text-end'>General Fees</th>
                <th class = 'text-nowrap text-end'>Laboratory Fees</th>
                <th class = 'text-nowrap text-end'>Requested</th>
                <th class = 'text-nowrap text-end'>Sub Total</th>
                <th class = 'text-nowrap text-end'>LESS SCHOLARSHIP</th>
                <th class = 'bg-success text-nowrap text-end'>AMOUNT DUE</th>
                <th class = 'text-nowrap text-end'>PAID</th>
                <th class = 'text-nowrap text-end'>BALANCE</th>
            </tr>
        </thead>
        <tbody>
        <?php
            $allBalance = 0;
            $balance = 0;
            foreach($allReg as $reg){
                $out = '';
                if ($reg['Finalize'] == 1){
                  $balance = ($reg['Balance']<0?'0.00':$reg['Balance']);
                  $allBalance += str_replace(',','',$balance);
                }

                if ($reg['Semester'] == 9 || $reg['Semester'] == 10){
                    if (session('campus') == 'TO')
                        $out = $reg['SchoolYear'];
                    else
                        $out = ($reg['SchoolYear']+1);
                }else{
                        $out = $reg['SchoolYear']."-".($reg['SchoolYear']+1);
                }
            ?>

            <?php
              $bg = "";
              if ($reg['Finalize'] != 1)
                $bg = "alert alert-danger";
            ?>

            <tr class = "{{$bg}}">
                <td>&nbsp;</th>
                <td class = 'text-nowrap'>{{$out}} {{GENERAL::Semesters()[$reg['Semester']]['Short']}}</td>
                <td class = 'text-nowrap text-end'><strong>{{$reg['TuitionFee']}}</strong>{!!($reg['Style']=='pUnit'?'<br><span class = "small">'.$reg['Unit'].'x'.$reg['UnitCost'].'<span>':'')!!}</td>
                <td class = 'text-nowrap text-end'><a href = "#" sid = "{{Crypt::encryptstring($reg['RegistrationID'])}}" class = "aGeneralFee">{{$reg['GeneralFees']}}</a></td>
                <td class = 'text-nowrap text-end'>{{$reg['LabFees']}}</td>
                <td class = 'text-nowrap text-end'>{{$reg['Requested']}}</td>
                <td class = 'text-nowrap text-end'>{{$reg['SubTotal']}}</td>
                <td class = 'text-nowrap text-end'><strong>{{$reg['Less']}}</strong>{!!(!empty($reg['ScholarName'])?'<br><span class = "small">'.$reg['ScholarName'].'</span>':'')!!} {!!($reg['ScholarName']=="TES"?"":'<a href = "#" class = "aUpdateSch" sid = "'.Crypt::encryptstring($reg['RegistrationID']).'" data-bs-toggle="offcanvas" data-bs-target="#offcanvasUpdateScholar" aria-controls="offcanvasBackdrop" ><i class = "fa fa-edit"></i></a>')!!}</td>
                <td class = 'bg-success text-white fw-semibold text-nowrap text-end'>{{$reg['Due']}}</td>
                <td class = 'text-nowrap text-end'>{{$reg['Paid']}}</td>
                <td class = 'text-nowrap text-end'>{!! ($balance > 0 ? "<span class = 'text-danger'>".$balance."</span>" : $balance) !!}</td>
            </tr>
            <?php
            }
            ?>
            <tr >
                <td>&nbsp;</th>
                <td colspan = "9" class = 'text-nowrap text-end mr-2 h4'>Grand Balance (VALIDATED ONLY): </td>
                <td class = 'text-nowrap text-end h4'>{{number_format($allBalance,2,'.',',')}}</td>
            </tr>
        </tbody>
    </table>
</div>
<div class="table-responsive mt-3">
    <div id = "partialout"></div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasoneSMSStudent" aria-labelledby="offcanvasBackdropLabel">
    <div class="offcanvas-header">
      <h5 id="offcanvasBackdropLabel" class="offcanvas-title"><i class = "fa fa-send"></i> Send SMS to {{ utf8_decode(strtoupper($tmpStudent->FirstName . (empty($tmpStudent->MiddleName)?' ':' '.$tmpStudent->MiddleName[0].'. ') .$tmpStudent->LastName)) }}</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <form id = "frmSendOneSMS">
      <div class="offcanvas-body my-auto mx-0 flex-grow-0">
        <input type = "hidden" name = "hidStudentID" id = "hidStudentID" value = "{{Crypt::encryptstring($tmpStudent->StudentNo)}}">
        <div id = "onesms"></div>
        <label>Enter your message. (Limited to 155 per SMS)</label>
        <textarea name = "BulkMessage" class = "form-control mb-3" rows = "10" autofocus></textarea>
        <button type="button" id = "btnsendonesms" class="btn btn-primary mb-2  w-100">Send Now</button>
        <button type="button" class="btn btn-outline-secondary  w-100" data-bs-dismiss="offcanvas">Cancel</button>
      </div>
    </form>
</div>

