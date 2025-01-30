<?php

namespace App\Http\Controllers\SLSU\Report;

use Elibyy\TCPDF\Facades\TCPDF;
use App\Http\Controllers\SLSU\Report\LetterHead;
use GENERAL;
use App\Models\Student;
use App\Models\Registration;
use Preference;

class CTCController extends TCPDF
{
    protected $letter;
    protected $id;
    protected $schoolname;
    protected $address;
    protected $orno;

    public function __construct(){
        $this->letter = new LetterHead();
    }

    private function getData(){
        $id = $this::getId();
        $list = Student::where("StudentNo", $id)->first();
        return $list;
    }

    public function Header(){

      $this->data = $this::getData();
      $this->letter->ReportHeader();
			$startY = 40;
      $this::setXY(17, $startY);

			$this::SetFont('cambria','',10);
      if (strtolower(session('campus')) == "sg"){
        $term = "Office of the University Registrar";
      }else{
        $term = "Office of the Registrar";
      }


			$this::Cell(180,4,$term,0,0,'C');

			$startY += 8;
			$this::setXY(17, $startY);
			$this::SetFont('cambriab','',10);
			$this::Cell(180,5,"CERTIFICATE OF TRANSFER CREDENTIALS",0,0,'C');

    }

    public function Footer(){
      // $startY = 247;

      $this->letter->ReportFooter(['QC' => config('QC.RO13')]);

    }

    function List()
		{
      $clsprefs = new Preference();
      $prefs = $clsprefs->GetDefaults();

      $reg = Registration::where("StudentNo", $this::getId())
          ->select("c.accro", 'registration.StudentYear','registration.Section')
          ->leftjoin("course as c", "registration.Course", '=', 'c.id')
          ->where("finalize", 1)
          ->orderby("SchoolYear", "DESC")
          ->orderby("Semester", "DESC")
          ->first();
      // $honorariumrg = $clsprefs->GetDefaultValue($prefs, "RequestAmountRG");

      $startY = 57;
			$this::setXY(150, $startY);
			$this::SetFont('cambria','',10);
			$this::Cell(30,5,date('F j, Y'),0,0,'C');
      $startY += 3;
			$this::setXY(150, $startY);
			$this::Cell(30,5,"Date",0,0,'C');

      $this::SetFont('cambria','',10);
      $startY += 5;
			$this::setXY(17, $startY);
			$this::Cell(180,5,"TO WHOM IT MAY CONCERN:",0,0,'L');

      $startY += 10;
      $html = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;THIS IS TO CERTIFY that <strong>" .mb_strtoupper(utf8_decode($this->data->LastName)).', '.mb_strtoupper(utf8_decode($this->data->FirstName)).(empty($this->data->MiddleName)?'':' '.mb_strtoupper(utf8_decode($this->data->MiddleName)))."</strong> a student of this university, is hereby granted Transfer Credential effective this date.<br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".($this->data->Sex=="M"?"His":"Her")." Official Transcript of Record will be forwarded upon receipt of the request below properly accomplished.";
      $this::writeHTMLCell(180, 5, 17, $startY, $html, 0, 0, 0, true, 'J', true);

      $startY += 30;
      $this::setXY(140, $startY);
			$this::SetFont('cambriab','',10);
			$this::Cell(40,5,strtoupper($clsprefs->GetDefaultValue($prefs, "RegistrarName")),0,0,'C');
      $startY += 3;
      $this::SetFont('cambria','',10);
			$this::setXY(140, $startY);
			$this::Cell(40,5,$clsprefs->GetDefaultValue($prefs, "RegistrarLevel"),0,0,'C');

      $startY += 10;
      $this::setXY(140, $startY);
      $this::SetFont('cambria','',9.75);
      $this::multiCell(40,5,"DOC. STAMP PAID\nunder O.R. No. ".$this->getDocor()."\nDate: ".date('m-d-Y', strtotime($this->getDocordate())), 1, "C");

      $startY += 15;
      $this::SetFont('cambria','',10);
			$this::setXY(17, $startY);
			$this::Cell(180,5,"--------------------------------------------------------------------------------------------CUT HERE--------------------------------------------------------------------------------------------",0,0,'C');
      $startY += 10;
      $this::setXY(47, $startY);
      $this::Cell(120,5,$this->getSchoolname(),0,0,'C');
      $startY += 5;
      $this::setXY(47, $startY);
      $this::Cell(120,5,"(Name of School)","T",1,'C');

      $startY += 10;
      $this::setXY(47, $startY);
      $this::Cell(120,5,$this->getAddress(),0,0,'C');
      $startY += 5;
      $this::setXY(47, $startY);
      $this::Cell(120,5,"(Address)","T",1,'C');

      $startY += 10;
      $this::setXY(150, $startY);
			$this::SetFont('cambria','',10);
			$this::Cell(30,5,"",0,0,'C');
      $startY += 3;
			$this::setXY(150, $startY);
			$this::Cell(30,5,"Date","T",1,'C');

      $startY += 5;
			$this::setXY(17, $startY);
			$this::Cell(30,5,"The Registrar",0,0,'L');
      $startY += 5;
			$this::setXY(17, $startY);
			$this::Cell(30,5,"SOUTHERN LEYTE STATE UNIVERSITY",0,0,'L');
      $startY += 5;
			$this::setXY(17, $startY);
			$this::Cell(30,5,$clsprefs->GetDefaultValue($prefs, "CampusString").', '.$clsprefs->GetDefaultValue($prefs, "SchoolAddress"),0,0,'L');

      $startY += 10;
			$this::setXY(17, $startY);
			$this::Cell(30,5,"Sir/Madam:",0,0,'L');

      $startY += 10;
      $html = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;On the strength of the Transfer Credential issued by your office to <strong>".mb_strtoupper(utf8_decode($this->data->LastName)).', '.mb_strtoupper(utf8_decode($this->data->FirstName)).(empty($this->data->MiddleName)?'':' '.mb_strtoupper(utf8_decode($this->data->MiddleName)))."</strong> on <strong>".date('F j, Y').'</strong>, we are respectfully requesting '.($this->data->Sex=="M"?"his":"her").' Official Transcript of Record.';
      $this::writeHTMLCell(180, 5, 17, $startY, $html, 0, 0, 0, true, 'J', true);

      $startY += 20;
			$this::setXY(130, $startY);
			$this::Cell(50,5,"Very truly yours,",0,0,'L');

      $startY += 20;
			$this::setXY(130, $startY);
			$this::Cell(50,5,"Registrar","T",1,'C');

      $startY += 10;
			$this::setXY(17, $startY);
			$this::Cell(50,5,"Course & Yr.:".$reg->accro.' '.$reg->StudentYear.$reg->Section,0,0,'L');
      // $startY += 3;
			// $this::setXY(17, $startY);
			// $this::Cell(50,5,"Payment Details:".$this->getOrno()."/".$this->getId(),0,0,'L');
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
     * Get the value of schoolname
     */
    public function getSchoolname()
    {
        return $this->schoolname;
    }

    /**
     * Set the value of schoolname
     *
     * @return  self
     */
    public function setSchoolname($schoolname)
    {
        $this->schoolname = $schoolname;

        return $this;
    }

    /**
     * Get the value of address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set the value of address
     *
     * @return  self
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get the value of orno
     */
    public function getOrno()
    {
        return $this->orno;
    }

    /**
     * Set the value of orno
     *
     * @return  self
     */
    public function setOrno($orno)
    {
        $this->orno = $orno;

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
