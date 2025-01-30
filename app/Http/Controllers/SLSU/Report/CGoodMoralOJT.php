<?php

namespace App\Http\Controllers\SLSU\Report;

use Elibyy\TCPDF\Facades\TCPDF;
use App\Http\Controllers\SLSU\Report\LetterHead;
use App\Http\Controllers\SLSU\Preference;
use GENERAL;
use App\Models\CourseOffering;
use App\Models\Registration;
use App\Models\Student;

class CGoodMoralOJT extends TCPDF
{
    protected $letter;
    protected $sy;
    protected $sem;
    protected $reason;
    protected $or;
    protected $ordate;
    protected $studentno;
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
        $registrar = strtoupper("Office of the Student Affairs and Services");
      }else{
        $registrar =  strtoupper("Office of the Student Affairs and Services");
      }
      $this::setXY(17, $startY);

			$this::SetFont('cambriab','',12);
			$this::Cell(180,5,$registrar,0,0,'C');

			$startY += 10;
    }

    public function Footer(){
      $this->letter->ReportFooter(['QC' => config('QC.GoodMoral')]);
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
          ->leftjoin("course as c", "registration.Course", "=", "c.id")
          ->leftjoin("department as d", "c.Department", "=", "d.id")
          ->leftjoin("employees as e", "d.DepartmentHead", "=", "e.id")
          ->where("SchoolYear", $this->getSy())
          ->where("Semester", $this->getSem())
          ->where("finalize", 1)
          ->first();

      $startY = 65;
      // $this::setXY(17, $startY);
			// $this::SetFont('cambria','',12);
			// $this::Cell(160,5,date('F d, Y'),0,0,'R');

      // $startY += 20;
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
      $html = '<div style="text-align:justify;line-height: 20px;text-indent: 30px;">THIS IS TO CERTIFY that <b>'.($student->Sex=="M"?"MR.":"MS.").' '.strtoupper(utf8_decode($student->FirstName.(empty($student->MiddleName)?" ":" ".$student->MiddleName[0].". ").$student->LastName))."</b> is enrolled with the degree of <strong>".strtoupper($reg->course->course_title)." (".strtoupper($reg->course->accro) .")</strong>"
                  .(empty($reg->major->course_major)?"":" major in <strong>".strtoupper($reg->major->course_major)."</strong>")." in this University, Academic Year <strong>".GENERAL::setSchoolYearLabel($this->getSy(),$this->getSem()).".</strong></div>";
      $this::writeHTML($html, true, 0, true, true);

      $html = '<br><div style="text-align:justify;line-height: 20px;text-indent: 30px;">THIS CERTIFIES FURTHER that '.($student->Sex=="M"?"he":"she").' is a law-abiding student and that '.($student->Sex=="M"?"he":"she").' has not committed any undesirable act and therefore '.($student->Sex=="M"?"he":"she").' is certified to be of good moral character. However, this certification is issued on the basis only of '.($student->Sex=="M"?"his":"her").' record as a student of this institution and cannot therefore interpose as a reference for '.($student->Sex=="M"?"his":"her").' character outside the school.</div>';
      $this::writeHTML($html, true, 0, true, true);

      $html = '<br><div style="text-align:justify;line-height: 20px;text-indent: 30px;">THIS CERTIFICATION is issued upon the request of the above-mentioned name for <b>'. $this->getReason(). ' purposes</b>.</div>';
      $this::writeHTML($html, true, 0, true, true);

      $html = '<br><div style="text-align:justify;line-height: 20px;text-indent: 30px;">DONE this '.date('jS').' day of '.date('F').', '.date('Y').' at '.$this->prefs->GetDefaultValue($this->pref, "SchoolAddress").'</div>';
      $this::writeHTML($html, true, 0, true, true);

      $this::Ln(15);

      $this::SetFont('cambriab','',12);
      $this::setX(20);
			$this::Cell(100,5,$this->prefs->GetDefaultValue($this->pref, "SASHead"),0,0,'C');

      $this::setX(100);
			$this::Cell(100,5,strtoupper($reg->FirstName.(empty($reg->MiddleName)?" ":" ".$reg->MiddleName[0].". ").$reg->LastName),0,0,'C');


      $this::Ln();

      $this::SetFont('cambria','',12);
      $this::setX(20);
			$this::Cell(100,5,$this->prefs->GetDefaultValue($this->pref, "SASDesignation"),0,0,'C');

      $this::setX(100);
			$this::Cell(100,5,$reg->Designation,0,0,'C');

      $this::Ln(15);

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
}
