<?php

namespace App\Exports;


use Elibyy\TCPDF\Facades\TCPDF;
use App\Http\Controllers\SLSU\Report\LetterHead;
use Rmunate\Utilities\SpellNumber;

use Crypt;
use App\Models\Enrolled;
use GENERAL;
use DateTime;
use Preference;

class QFIN67 extends TCPDF
{

  protected $id;
  protected $sy;
  protected $sem;
  protected $lists;
  public function __construct(){
    $this->letter = new LetterHead();
  }

  private function generate(){

    try{

      $id = Crypt::decryptstring($this->getData()['id']);
      $sy = Crypt::decryptstring($this->getData()['sy']);
      $sem = Crypt::decryptstring($this->getData()['sem']);

    }catch(DecryptException $e){
      return GENERAL::Error("Invalid Hash");
    }


    $this->setId($id);
    $this->setSy($sy);
    $this->setSem($sem);

    session([
      'schoolyear' => $sy,
      'semester' => $sem
    ]);

    $cc = "courseoffering".$sy.$sem;
    $enrolled = new Enrolled();
    $lists = $enrolled->select($enrolled->getTable().".*", 't.coursetitle','t.courseno','t.units','t.lab','t.lec',
      'c.course_title','r.StudentYear','r.Section','r.SchoolLevel','s.StudentNo',
      's.LastName','s.FirstName','s.MiddleName','s.Sex','cc.coursecode',
      'm.course_major','s.civil_status','s.BirthDate','s.p_street', 's.p_municipality', 's.p_province',
      'sc1.tym as Time1', 'sc2.tym as Time2',
      'e.LastName as empLastName', 'e.FirstName as empFirstName', 'e.MiddleName as empMiddleName', 'e.EmploymentStatus')
      ->leftjoin($cc." as cc", $enrolled->getTable().".courseofferingid", "=", "cc.id")
      ->leftjoin("schedule_time as sc1", "cc.sched", "=", "sc1.id")
      ->leftjoin("schedule_time as sc2", "cc.sched2", "=", "sc2.id")
      ->leftjoin("students as s", $enrolled->getTable().".StudentNo", "=", "s.StudentNo")
      ->leftjoin("registration as r", $enrolled->getTable().".gradesid", "=", "r.RegistrationID")
      ->leftjoin("course as c", "r.Course", "=", "c.id")
      ->leftjoin("major as m", "r.Major", "=", "m.id")
      ->leftjoin("employees as e", "cc.teacher", "=", "e.id")
      ->leftjoin("transcript as t", $enrolled->getTable().".sched", "=", "t.id")
      ->where("cc.coursecode", $id)
      ->where("r.SchoolYear", $sy)
      ->where("r.Semester", $sem)
      ->orderBy("s.LastName")
      ->orderBy("s.FirstName")
      ->get();

      return $lists;
  }

  public function Header(){

    $this->lists = $this->generate();
    $this->letter->ReportHeader();
    $startY = 45;

    $this::setXY(17, $startY);

    $this::SetFont('cambriab','',16);
    $this::Cell(180,5,"VOLUNTARY PAYMENT FORM",0,0,'C');

    $startY += 6;
    $this::setXY(17, $startY);
    $this::SetFont('cambria','',11);
    $this::Cell(180,5,"For ".GENERAL::Semesters()[$this->getSem()]['Long']. ' Academic Year '.GENERAL::setSchoolYearLabel($this->getSy(),$this->getSem()),0,0,'C');
    $startY += 10;
    $this::setXY(15, $startY);

  }

  public function Footer(){
    $startY = 217;

    $this->letter->ReportFooter(['QC' => config('QC.IN67')]);
  }

  public function Body()
  {

      $this::SetFont('cambria','',11);
      $clsprefs = new Preference();
      $prefs = $clsprefs->GetDefaults(['RequestAmountPT','RequestAmountRG']);

      $divisor = count($this->lists);
      $honorariumpt = $clsprefs->GetDefaultValue($prefs, "RequestAmountPT");
      $honorariumrg = $clsprefs->GetDefaultValue($prefs, "RequestAmountRG");

      $job = "pt";
      if (!empty($this->lists[0]->EmploymentStatus)){
          if (strtolower($this->lists[0]->EmploymentStatus) == "permanent - faculty"){
            $job = "reg";
          }

          if (strtolower($this->lists[0]->EmploymentStatus) == "temporary - faculty"){
            $job = "reg";
          }
      }


      if ($job == 'pt'){
        if (empty($honorariumpt)){
          $feeperstudent = 0;
        }else{
          $lab = (empty($this->lists[0]->lab)?0:$this->lists[0]->lab);
          $lec = (empty($this->lists[0]->lec)?0:$this->lists[0]->lec);
          $hours = ($lab * 3) + $lec ;
          $feeperstudent = (($honorariumpt * $hours) * 18) / $divisor;
        }
      }else{
        if (empty($honorariumrg)){
          $feeperstudent = 0;
        }else{
          $lab = (empty($this->lists[0]->lab)?0:$this->lists[0]->lab);
          $lec = (empty($this->lists[0]->lec)?0:$this->lists[0]->lec);
          $hours = ($lab * 3) + $lec;
          $feeperstudent = (($honorariumrg * $hours) * 18) / $divisor;
        }
      }

      // dd();
      // $feeperstudent += .05;
      // // Load the existing Word document

      foreach($this->lists as $index => $list){

        if (empty($list['BirthDate'])){
            $age = 0;
        }else{
            $tmpage = explode(" ", $list['BirthDate']);
            $bdate2 = $tmpage[2]."-".$tmpage[0]."-".$tmpage[1];
            $from = new DateTime($bdate2);
            $to   = new DateTime('today');
            $age = $from->diff($to)->y;
        }
        $startY = 60;
        $this::setXY(30, $startY);
        $this::writeHTML('<span style="text-align:justify;">
            I, <strong>'.strtoupper(utf8_decode($list->FirstName. (empty($list->MiddleName)? " " : " ".$list->MiddleName[0].". "). $list->LastName)).'</strong>, Filipino,
            <strong>'.$list->civil_status.'</strong>, <strong>'.$age.'</strong> years old, and a resident of <strong>'.strtoupper(utf8_decode($list->p_street. ', '.$list->p_municipality.', '.$list->p_province)).'</strong>, after having been sworn in accordance with law, do hereby depose and state that:</span>
        ');
        $spell = "";
        $tmp = explode(".",$feeperstudent);
        if (sizeof($tmp) == 1){
          $spell = SpellNumber::value(str_replace(",","",number_format($feeperstudent)))->locale('en')->toLetters(). " Pesos";
        }else{
          if (empty($tmp[1])){
            $spell = SpellNumber::value(str_replace(",","",number_format($feeperstudent)))->locale('en')->toLetters(). " Pesos";
          }else{
            $spell = SpellNumber::value(str_replace(",","",number_format($feeperstudent,2)))
              ->locale('en')
              ->currency('Pesos')
              ->toMoney();
          }
        }
        $this::writeHTML('<span style="text-align:justify;"><ol>
            <li>I am enrolled or intending to enroll in the degree <strong>'.$list->course_title.'</strong> with the Southern Leyte State University - <strong>'.GENERAL::Campuses()[session('campus')]['Campus'].'</strong>;</li><br>
            <li>I am fully aware of my benefits and responsibilities under the Republic Act No. 10931 otherwise known as the <em>“Universal Access to Quality Tertiary Education Act of 2017”</em> including but not limited to free tuition and school fees subsidy;</li><br>
            <li>In relation to Section 4 of R.A. 10931 and Section 9 of the
                Implementing Rules and Regulations <em>(IRR)</em>,
                I am making a voluntary financial contribution to
                Southern Leyte State University in the amount of
                <strong>'.strtoupper($spell).'</strong> (Php. <strong>'.number_format($feeperstudent,2).'</strong>)
                for the <strong>'.GENERAL::Semesters()[$this->getSem()]['Long'].'</strong> of Academic Year <strong>'.GENERAL::setSchoolYearLabel($this->getSy(),$this->getSem()).'</strong> for the
                <strong>honorarium of the faculty handling petitioned subject ('.$list->courseno.")</strong>".';</li><br>
            <li>I understand and acknowledge that the term <em>“other similar or related fees”</em> as defined by law and to which payment I am entitled to exemption shall refer and be restricted only to the following or synonymous thereto:
                <br>
                <table width = "100%">
                    <tr>
                        <td width = "33%">a.	Library fees,</td>
                        <td width = "33%">f.	Admission fees,</td>
                        <td width = "33%">k.	Registration fees,</td>
                    </tr>

                    <tr>
                        <td width = "33%">b.	Computer fees,</td>
                        <td width = "33%">g.	Development fees,</td>
                        <td width = "33%">l.	Medical and Dental</td>
                    </tr>

                    <tr>
                        <td width = "33%">c.	Laboratory fees,</td>
                        <td width = "33%">h.	Guidance fees,</td>
                        <td width = "33%">&nbsp;&nbsp;&nbsp;fees, and</td>
                    </tr>

                    <tr>
                        <td width = "33%">d.	School ID fees,</td>
                        <td width = "33%">i.	Handbook fees,</td>
                        <td width = "33%">m.	Cultural fees;</td>
                    </tr>

                    <tr>
                        <td width = "33%">e.	Athletic fees,</td>
                        <td width = "33%">j.	Entrance fees,</td>
                        <td width = "33%"></td>
                    </tr>

                </table>

                </li>
                <li>As a consequence, I may also be required to pay the fees not falling under the definition provided by law for <em>“tuition fee” and “other similar or related fees”</em>;</li><br>
                <li>I have the financial capacity to make this voluntary contribution;</li><br>
                <li>I have read and understood the substance of this Voluntary Contribution Form, and that I was not coerced and I have knowingly and voluntarily made this decision to make such contribution in favor to Southern Leyte State University;</li><br>
                <li>I undertake to abide by all the terms of the aforesaid law, the Voluntary Contribution Mechanism created by the University, the University policies, and other applicable rules and regulations;</li><br>
                <li>I am executing this document to attest to the truth of the foregoing and for all legal intents and purposes it may serve.</li>
        </ol></span>');
      //   // Add values to the document
      //   $templateProcessor->setValue('SY2', ." ");
      //   $templateProcessor->setValue('SY', );
      //   $templateProcessor->setValue('SEM', );
      //   $templateProcessor->setValue('Name', ));
      //   $templateProcessor->setValue('CivilStatus', );
      //   $templateProcessor->setValue('Age', $age);
      //   $templateProcessor->setValue('Address', );
      //   $templateProcessor->setValue('Course', );
      //   $templateProcessor->setValue('Campus', );
      //   $templateProcessor->setValue("Reason", );

      //   $spell = ;
      //   $templateProcessor->setValue('Words', strtoupper($spell));
      //   $templateProcessor->setValue('Figure', number_format($feeperstudent,2));

      //   $public = "public";
      //   $directoryPath = 'out-out-qfin67/'.session('campus');
      //   if (!Storage::exists($public."/".$directoryPath)) {
      //     Storage::makeDirectory($public."/".$directoryPath);
      //   }

      //   // Save the modified document
      //   $outputPath = storage_path('app/public/'.$directoryPath.'/'.\Str::slug($list->FirstName.'-'.$list->LastName).'.docx');
      //   $templateProcessor->saveAs($outputPath);
        if ($index < count($this->lists) -1)
        $this::AddPage();
      }

      // return response()->download($outputPath);
  }

  /**
   * Get the value of data
   */
  public function getData()
  {
    return $this->data;
  }

  /**
   * Set the value of data
   *
   * @return  self
   */
  public function setData($data)
  {
    $this->data = $data;

    return $this;
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
}

?>
