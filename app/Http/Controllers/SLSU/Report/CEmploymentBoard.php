<?php

namespace App\Http\Controllers\SLSU\Report;

use Elibyy\TCPDF\Facades\TCPDF;
use App\Http\Controllers\SLSU\Report\LetterHead;
use App\Http\Controllers\SLSU\Preference;
use GENERAL;
use App\Models\CourseOffering;
use App\Models\Registration;
use App\Models\Student;

class CEmploymentBoard extends TCPDF
{
    protected $letter;
    protected $sy;
    protected $sem;
    protected $reason;
    protected $or;
    protected $ordate;
    protected $studentno;
    protected $docor;
    protected $docordate;
    protected $prefs;
    protected $pref;

    public function __construct(){
        $this->letter = new LetterHead();
        $this->prefs = new Preference();
        $this->pref = $this->prefs->GetDefaults();
    }

    public function Header(){

      // dd($this->data[0]->subject);
      $this->letter->ReportHeader();

      $startY = 45;

      if (strtolower(session('campus')) == "sg"){
        $registrar = strtoupper("Office of the University Registrar");
      }else{
        $registrar =  strtoupper("Office of the Registrar");
      }
      $this::setXY(17, $startY);

			$this::SetFont('cambriab','',12);
			$this::Cell(180,5,$registrar,0,0,'C');

			$startY += 10;
    }

    public function Footer(){
      $this->letter->ReportFooter();
    }

    function getYear($year){
        $out = "";
        switch($year){
          case 1:
            $out = "first";
            break;
          case 2:
            $out = "second";
            break;
          case 3:
            $out = "third";
            break;
          case 4:
            $out = "fourth";
            break;
          case 5:
            $out = "fifth";
            break;
        }

        return $out;
    }

    function Content()
		{

      $student = Student::where("StudentNo", $this->getStudentno())->first();
      $reg = Registration::where("StudentNo", $this->getStudentno())
          ->where("SchoolYear", $this->getSy())
          ->where("Semester", $this->getSem())
          ->where("finalize", 1)
          ->first();

      $startY = 65;
      $this::setXY(17, $startY);
			$this::SetFont('cambria','',12);
			$this::Cell(160,5,date('F d, Y'),0,0,'R');

      $startY += 20;
      $this::setXY(17, $startY);
			$this::SetFont('cambriab','',16);
			$this::Cell(180,5,"C E R T I F I C A T I O N",0,0,'C');

      $startY += 20;
      $this::setXY(18, $startY);
			$this::SetFont('cambria','',12);
			$this::Cell(180,5,"TO WHOM IT MAY CONCERN:",0,0,'L');

      $startY += 10;
      $this::setXY(25, $startY);
			$this::SetFont('cambria','',12);
      $this::setCellPadding(0);
      $this::Ln();
      $html = '<div style="text-align:justify;line-height: 35px;text-indent: 30px;">THIS IS TO CERTIFY that <b>'.($student->Sex=="M"?"MR.":"MS.").' '.strtoupper(utf8_decode($student->FirstName.(empty($student->MiddleName)?" ":" ".$student->MiddleName[0].". ").$student->LastName))."</b> has graduated with the degree of <strong>".strtoupper($reg->course->course_title)." (".strtoupper($reg->course->accro) .")</strong>"
                  .(empty($reg->major->course_major)?"":" major in <strong>".strtoupper($reg->major->course_major)."</strong>")." in this University, Academic Year <strong>".GENERAL::setSchoolYearLabel($this->getSy(),$this->getSem()).".</strong></div>";
      $this::writeHTML($html, true, 0, true, true);

      $html = '<div style="text-align:justify;line-height: 35px;text-indent: 30px;">This certifies further that this University being a Government Institution is exempted from issuing the <b>Special Order</b> to the graduates.</div>';
      $this::writeHTML($html, true, 0, true, true);

      $html = '<div style="text-align:justify;line-height: 35px;text-indent: 30px;">This Certification is issued to the above named graduate for '. $this->getReason(). ' purpose only.</div>';
      $this::writeHTML($html, true, 0, true, true);

      $this::Ln(10);

      $this::SetFont('cambriab','',12);
      $this::setX(100);
			$this::Cell(100,5,$this->prefs->GetDefaultValue($this->pref, "RegistrarName"),0,0,'C');
      $this::Ln();

      $this::SetFont('cambria','',12);
      $this::setX(100);
			$this::Cell(100,5,$this->prefs->GetDefaultValue($this->pref, "RegistrarLevel"),0,0,'C');

      $this::Ln(6);

      $this::Cell(100,5,"Not Valid w/o",0,0,'L');
      $this::Ln();
      $this::Cell(100,5,"School Seal",0,0,'L');
      $this::Ln();
      $this::Cell(100,5,"O.R. #".$this->getOr(),0,0,'L');
      $this::Ln();
      $this::Cell(100,5,"Date: ".date('m-d-Y', strtotime($this->getOrdate())),0,0,'L');
      $this::Ln();
      $this::Cell(100,5,(empty(auth()->user()->UserAlias)?"":"/".auth()->user()->UserAlias),0,0,'L');
      // reset pointer to the last page

      $this::Ln(6);
      $this::SetFont('cambria','',9.75);
      $this::multiCell(40,5,"DOC. STAMP PAID\nunder O.R. No. ".$this->getDocor()."\nDate: ".date('m-d-Y', strtotime($this->getDocordate())), 1, "C");


      $this::lastPage();

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
     * Get the value of ordate
     */
    public function getOrdate()
    {
        return $this->ordate;
    }

    /**
     * Set the value of ordate
     *
     * @return  self
     */
    public function setOrdate($ordate)
    {
        $this->ordate = $ordate;

        return $this;
    }

    /**
     * Get the value of or
     */
    public function getOr()
    {
        return $this->or;
    }

    /**
     * Set the value of or
     *
     * @return  self
     */
    public function setOr($or)
    {
        $this->or = $or;

        return $this;
    }

    /**
     * Get the value of reason
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Set the value of reason
     *
     * @return  self
     */
    public function setReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * Get the value of studentno
     */
    public function getStudentno()
    {
        return $this->studentno;
    }

    /**
     * Set the value of studentno
     *
     * @return  self
     */
    public function setStudentno($studentno)
    {
        $this->studentno = $studentno;

        return $this;
    }

    /**
     * Get the value of docor
     */
    public function getDocor()
    {
        return $this->docor;
    }

    /**
     * Set the value of docor
     *
     * @return  self
     */
    public function setDocor($docor)
    {
        $this->docor = $docor;

        return $this;
    }

    /**
     * Get the value of docordate
     */
    public function getDocordate()
    {
        return $this->docordate;
    }

    /**
     * Set the value of docordate
     *
     * @return  self
     */
    public function setDocordate($docordate)
    {
        $this->docordate = $docordate;

        return $this;
    }
}
