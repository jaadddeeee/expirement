<?php

namespace App\Exports;


use Elibyy\TCPDF\Facades\TCPDF;
use App\Http\Controllers\SLSU\Report\LetterHead;
use Illuminate\Support\Facades\DB;
use App\Models\Enrolled;
use App\Models\Registration;
use App\Models\Student;

use GENERAL;

class RouteSlip extends TCPDF
{

  protected $id;
  protected $sy;
  protected $sem;
  protected $reg;
  protected $one;
  protected $lastY;
  protected $name;
  public function __construct(){
    $this->letter = new LetterHead();
    $this->cambria = 'cambria';
    $this->cambriabold = 'cambriab';
    $this->deffontsize = 9;
  }

  private function generate(){
    $this->reg = Registration::where("RegistrationID", $this->getId())->first();
    $this->one = Student::where("StudentNo", $this->reg->StudentNo)->first();
    $this->setName(\Str::slug($this->one->LastName.'-'.$this->one->FirstName));
  }

  public function Header(){
    $this->generate();
    $this->letter->ReportHeader();
    $startY = 36;

    $startY += 10;
    $startX = 14;
    $this::SetFont('cambriab','',12);
    $this::setXY($startX, $startY);
    $this::Cell(180,5,"ROUTE SLIP",0,0,'C');

    $this::SetFont($this->cambria,'',$this->deffontsize);
    $startX = 30;
    $startY += 10;
    $this::setXY($startX, $startY);
    $this::Cell(30,5,"Name: ",0,0,'L');
    $this::setXY($startX + 15, $startY);
    $this::SetFont($this->cambriabold,'',10);
    $this::Cell(30,5,mb_strtoupper(utf8_decode($this->one['FirstName']) .(empty($this->one['MiddleName'])?" ":" ".utf8_decode($this->one['MiddleName'])).' '.utf8_decode($this->one['LastName'])),0,0,'L');

    $startX = 120;
    $this::setXY($startX, $startY);
    $this::SetFont($this->cambria,'',$this->deffontsize);
    $this::Cell(30,5,"Student ID Number: ",0,0,'L');
    $this::setXY($startX+32, $startY);
    $this::SetFont($this->cambriabold,'',10);
    $this::Cell(30,5,$this->one->StudentNo,0,0,'L');

    $this->setLastY($startY);

  }

  public function Footer(){
    // $startY = $this->getLastY();
    $startY = 260;
    $startX = 15;
    $this::SetFont($this->cambria,'',7.5);
    $this::setY($startY);
    $this::setX($startX);
    $this::MultiCell(0, 3, "Note: This route slip is system generated, hence signatures are not evident.",0,"L");
    $this->letter->ReportFooter(['QC' => config('QC.RE05')]);
  }

  public function List()
  {
    $header = array('PROCESS', 'OFFICE', 'DATE', 'TIME', 'SIGNATURE');
    $w = array(167, 80, 70, 65, 65, 80);
    $steps = [
      ['step' => 'Check Credentials', 'office' => 'SAS Office','table' => '','time' => '', 'by' => ''],
      ['step' => 'Assign/Generate Student ID Number', 'office' => 'UISA Office','table' => '','time' => '', 'by' => ''],
      ['step' => 'Evaluate subjects (for transferee, shiftee and returnee) Encode/Approved subjects registered', 'office' => 'Department','table' => 'DateEnrolled','time' => 'TimeEnrolled', 'by' => 'EnrollingOfficer'],
      ['step' => 'Check FHE status', 'office' => 'FHE Office','table' => 'TESDate','time' => 'TimeTES', 'by' => 'TESBy'],
      ['step' => 'Assess fee / Receive payment', 'office' => 'Cashier’s Office','table' => 'CashierDate','time' => 'TimeCashier', 'by' => 'Cashier'],
      ['step' => 'Receive, validate credentials and registration forms Issue ORF and Assessment Slip', 'office' => 'Registrar’s Office','table' => 'DateValidated','time' => 'TimeValidated', 'by' => 'ValidatedBy']
    ];

    $this::SetFont($this->cambria,'',10);
    $startY = $this->getLastY()+7;
    $this::setXY(14,$startY);
    // for($i=0;$i<count($header);$i++)
    // {
    //   $this::Cell($w[$i],15,$header[$i],1,0,'C');
    // }

    $des = '
        <table border="1" cellpadding="7" cellspacing="0">
          <thead>
            <tr>
              <td style="font-size: 10px" width="'.$w[0].'" align="center" valign = "middle" rowspan = "2">PROCESS</td>
              <td style="font-size: 10px" width="'.$w[1].'" align="center" valign = "middle" rowspan = "2">OFFICE</td>
              <td style="font-size: 10px" width="'.$w[2].'" align="center" valign = "middle" rowspan = "2">DATE</td>
              <td style="font-size: 10px" width="'.$w[3]+$w[4].'" valign = "middle" align="center" colspan = "2">TIME</td>
              <td style="font-size: 10px" width="'.$w[5].'" align="center" valign = "middle" rowspan = "2">SIGNATURE</td>
            </tr>

            <tr>
              <td style="font-size: 10px" width="'.$w[3].'" align="center" valign = "middle">Log-In</td>
              <td style="font-size: 10px" width="'.$w[4].'" align="center" valign = "middle">Log-Out</td>
            </tr>

          </thead>
          <tbody>';
      $tr = '';
      foreach($steps as $index => $step){
          $valTable = $step['table'];
          $valTime = $step['time'];
          $valBy = $step['by'];
          $tr .= '<tr>
              <td style="font-size: 8" width="'.$w[0].'">'.($index+1).". ".$step['step'].'</td>
              <td style="font-size: 8" width="'.$w[1].'">'.$step['office'].'</td>
              <td style="font-size: 8" width="'.$w[2].'">'.(empty($step['table'])?'':$this->reg->$valTable).'</td>
              <td style="font-size: 8" width="'.$w[3].'">'.(empty($step['time'])?'':$this->reg->$valTime).'</td>
              <td style="font-size: 8" width="'.$w[4].'"></td>
              <td style="font-size: 8" width="'.$w[5].'">'.(empty($step['by'])?'':$this->reg->$valBy).'</td>
            </tr>';
      }

    $des .= $tr.'</tbody></table>';
    $des .= '<br><br>&nbsp;&nbsp;&nbsp;&nbsp;<strong>Steps to be followed for:</strong><br>
              &nbsp;&nbsp;New Student/ Transferee / Cross Enrollee – 1,2,3,4,5,6,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Returnee / Shiftee / Continuing Student - 3,4,5,6';
    $this::writeHTML($des);

  }

  /**
   * Get the value of id
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Set the value of id
   *
   * @return  self
   */
  public function setId($id)
  {
    $this->id = $id;

    return $this;
  }

  /**
   * Get the value of sy
   */
  public function getSy()
  {
    return $this->sy;
  }

  /**
   * Set the value of sy
   *
   * @return  self
   */
  public function setSy($sy)
  {
    $this->sy = $sy;

    return $this;
  }

  /**
   * Get the value of sem
   */
  public function getSem()
  {
    return $this->sem;
  }

  /**
   * Set the value of sem
   *
   * @return  self
   */
  public function setSem($sem)
  {
    $this->sem = $sem;

    return $this;
  }

  /**
   * Get the value of lastY
   */
  public function getLastY()
  {
    return $this->lastY;
  }

  /**
   * Set the value of lastY
   *
   * @return  self
   */
  public function setLastY($lastY)
  {
    $this->lastY = $lastY;

    return $this;
  }

  /**
   * Get the value of name
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Set the value of name
   *
   * @return  self
   */
  public function setName($name)
  {
    $this->name = $name;

    return $this;
  }
}

?>
