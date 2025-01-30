<?php

namespace App\Http\Controllers\SLSU\Report;

use Elibyy\TCPDF\Facades\TCPDF;
use App\Http\Controllers\SLSU\Report\LetterHead;
use GENERAL;
use App\Models\CourseOffering;

class WorkloadController extends TCPDF
{
    protected $letter;
    protected $id;
    protected $sy;
    protected $sem;
    protected $data;
    protected $w;
    public function __construct(){
        $this->letter = new LetterHead();
        $this->w = array(25, 30, 12, 60, 12, 46);
    }

    private function getData(){
        $id = $this::getId();
        $lists = CourseOffering::where('teacher', $id)
        ->withCount("enrolled")
        ->get();

        return $lists;
    }

    public function Header(){

      $this->data = $this::getData();
      // dd($this->data[0]->subject);
      $this->letter->ReportHeader();

      $startY = 40;

      if (strtolower(session('campus')) == "sg"){
        $registrar = strtoupper("Office of the University Registrar");
      }else{
        $registrar =  strtoupper("Office of the Registrar");
      }
      $this::setXY(17, $startY);

			$this::SetFont('cambriab','',11);
			$this::Cell(180,5,$registrar,0,0,'C');

			$startY += 10;
			$this::setXY(17, $startY);
			$this::SetFont('cambriab','',10);
			$this::Cell(180,5,"TEACHING LOAD SHEET/S",0,0,'C');

			$startY += 10;
			$this::setXY(17, $startY);
			$this::SetFont('cambriab','',8);

      $term = GENERAL::Semesters()[$this::getSem()]['Long'] . " / " . GENERAL::setSchoolYearLabel($this::getSy(),$this::getSem());

			$this::Cell(180,4,$term,0,0,'C');
			$startY += 5;
			$this::Line(82, $startY, 210-80, $startY);

			$startY += 1;
			$this::setXY(17, $startY);
			$this::SetFont('cambria','',8);
			$this::Cell(180,4,"Term/Academic Year",0,0,'C');

      $startY += 7;
			$this::setXY(20, $startY);
			$this::SetFont('cambriab','',12);
			$this::Cell(180,4,strtoupper(utf8_decode($this->data[0]->employee->LastName.", ".$this->data[0]->employee->FirstName." ".$this->data[0]->employee->MiddleName)),0,0,'L');

      $this::setXY(130, $startY);
			$this::SetFont('cambriab','',12);
			$this::Cell(180,4,"Department/College: ".$this->data[0]->employee->department->DepartmentName,0,0,'L');

      $startY += 6;
			$this::setXY(20, $startY);
			$this::SetFont('cambria','',10);
			$this::Cell(180,4,$this->data[0]->employee->EmploymentStatus,0,0,'L');

      $interval = 7;

      $startX = 15;
			$startY += 7;
			$this::setXy($startX, $startY);
			$this::Line($startX, $startY, 200, $startY);

      $this::setXY(15, $startY);
      $this::Cell($this->w[0],$interval," COURSE CODE",'L');
      $this::Cell($this->w[1],$interval," COURSE NO",'L',0,'L');
      $this::Cell($this->w[2],$interval,"UNITS",'L',0,'C');
      $this::Cell($this->w[3],$interval," DESCRIPTIVE TITLE",'L',0,'L');
      $this::Cell($this->w[4],$interval," SIZE",'L',0,'C');
      $this::Cell($this->w[5],$interval," SCHEDULE",'LR',0,'L');

      $startX = 15;
			$startY += 7;
			$this::setXy($startX, $startY);
			$this::Line($startX, $startY, 200, $startY);

    }

    public function Footer(){
      $this->letter->ReportFooter(['QC' => config('QC.GradeSheet')]);
    }

    function List()
		{

        $this::setX(15);

		    foreach($this->data as $key)
		    {

          $this::SetFont('cambria','',10);
          $interval = 5;
          $this::Cell($this->w[0],$interval,$key->coursecode,'LB',0,'L');
          $this::Cell($this->w[1],$interval,$key->subject->courseno,'LB',0,'L');
          $this::Cell($this->w[2],$interval,($key->subject->exempt==1?"(".$key->subject->units.")":$key->subject->units),'LB',0,'C');
          $this::SetFont('cambria','',9);
          $this::Cell($this->w[3],$interval,$key->subject->coursetitle,'LB',0,'L');
          $this::SetFont('cambria','',10);
          $this::Cell($this->w[4],$interval,$key->enrolled_count,'LB',0,'C');
          $this::SetFont('cambria','',9);
          $this::Cell($this->w[5],$interval,$key->schedule->tym,'LBR',0,'L');
          $this::Ln();
          $this::setX(15);

		    }
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
