<?php

namespace App\Exports;


use Elibyy\TCPDF\Facades\TCPDF;
use App\Http\Controllers\SLSU\Report\LetterHead;
use Illuminate\Support\Facades\DB;
use App\Models\Enrolled;
use App\Models\Registration;
use App\Models\Student;

use GENERAL;

class DataPrivacy extends TCPDF
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
    $this::SetFont('cambriab','',10);
    $this::setXY($startX, $startY);
    $this::Cell(180,5,"DATA PRIVACY CONSENT FORM",0,0,'C');

    $startY += 10;
    $startX = 14;
    $this::SetFont('cambriab','',10);
    $this::setXY($startX, $startY);
    $this::MultiCell(180,4,"STUDENT CONSENT FORM FOR THE PROCESSING,\nRELEASE AND RETENTION OF PERSONAL INFORMATION",0,'C');

    $this->setLastY($startY);

  }

  public function Footer(){
    // $startY = $this->getLastY();
    $startY = 260;
    $startX = 15;
    $this::SetFont($this->cambria,'',7.5);
    $this::setY($startY);
    $this::setX($startX);
    $this::MultiCell(0, 3, "Note: This consent form is system generated, thus, signature of the student is not necessary.",0,"L");
    $this->letter->ReportFooter();
  }

  public function List()
  {
    $startY = $this->getLastY() + 10;

    $this::setY($startY);

    $this::SetFont($this->cambria,'',10);
    $des = '<span style="text-align:justify;">
    <p>I, <strong>'.mb_strtoupper(utf8_decode($this->one['FirstName']) .(empty($this->one['MiddleName'])?" ":" ".utf8_decode($this->one['MiddleName'])).' '.utf8_decode($this->one['LastName'])).'</strong>, am fully aware that Southern Leyte State University (SLSU) or its designated representative is duty bound and obligated under the Data Privacy Act of 2012 to protect all my personal and sensitive information that it collects, processes, and retains upon my enrolment and during my stay in the University.</p>

    <p>Student personal information includes any information about my identity, academics, medical conditions, or any documents containing my identity. This includes but not limited to my name, address, names of my parents or guardians, date of birth, grades, attendance, disciplinary records, and other information necessary for basic administration and instruction.</p>

    <p>I understand that my personal information cannot be disclosed without my consent. I understand that the information that was collected and processed relates to my enrolment and to be used by SLSU '.GENERAL::Campuses()[session('campus')]['Campus'].' to pursue its legitimate interests as an educational institution. Likewise, I am fully aware that SLSU may share such information to affiliated or partner organizations as part of its contractual obligations, or with government agencies pursuant to law or legal processes. In this regard, I hereby allow SLSU to collect, process, use and share my personal data in the pursuit of its legitimate interests as an educational institution.</p>

    <p>By submitting this form, I acknowledge that I consent to have photographs taken during school organized events/activities. The school may use or share these photographs with its members and staff for newsletter, email, communications, marketing and/or website publications, yearbook, and for any other legitimate purpose and intent of the institution.</p>

    <p>Furthermore, I expressly give my consent to SLSU to process, use, and disclose to my parents and/or guardian named below the foregoing information relative to my education status-grades, program enrolled, year level, subjects taken or enrolled.</p></span><br><br>';

    if (empty($this->one->ConsentFather)){
      $des .= '(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;) Father’s Name:&nbsp;&nbsp;&nbsp;_____________________________<br>';
    }else{
      $des .=  '( x ) Father’s Name:&nbsp;&nbsp;&nbsp;'.utf8_decode($this->one->ConsentFather).'<br>';
    }

    if (empty($this->one->ConsentMother)){
      $des .=  '(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;) Mother’s Name:&nbsp;&nbsp;&nbsp;_____________________________<br>';
    }else{
      $des .=  '( x ) Mother’s Name:&nbsp;&nbsp;&nbsp;'.utf8_decode($this->one->ConsentMother).'<br>';
    }

    if (empty($this->one->ConsentGuardian)){
      $des .=  '(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;) Guardian’s Name:&nbsp;&nbsp;&nbsp;_____________________________<br>';
    }else{
      $des .=  '( x ) Guardian’s Name:&nbsp;&nbsp;&nbsp;'.utf8_decode($this->one->ConsentGuardian).'<br>';
    }

    if (empty($this->one->ConsentOthers)){
      $des .=  '(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;) Others, please specify:&nbsp;&nbsp;&nbsp;_____________________________&nbsp;&nbsp;&nbsp;Relationship: ____________________________<br>';
    }else{
      $des .=  '( x ) Others, please specify:&nbsp;&nbsp;&nbsp;'.utf8_decode($this->one->ConsentOthers).'&nbsp;&nbsp;&nbsp;Relationship:&nbsp;&nbsp;&nbsp;'.utf8_decode($this->one->ConsentRelation).'<br>';
    }

    $des .= '<br><br><br>Upon clicking/signing, I hereby give my consent for the processing, release, and retention of personal information.';

    $des .= '<br><br><br><br>Student ID Number: '. $this->reg->StudentNo.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;___________________________________________';
    $des .= '<br>Date Signed / Sign in: '.$this->reg->DateEncoded.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Student Signature Over Printed Name';
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
