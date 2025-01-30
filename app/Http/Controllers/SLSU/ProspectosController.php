<?php

namespace App\Http\Controllers\SLSU;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SLSU\LogController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Encryption\DecryptException;

use App\Models\Prospectos;

use Crypt;
use GENERAL;

class ProspectosController extends Controller
{
    protected $course;
    protected $major;
    protected $SchoolYear;
    protected $Semester;
    protected $CurNum;

    public function getList(){
        $lists = Prospectos::query();
        $lists = $lists->where("course", $this->getCourse())
            ->where("cur_num", $this->getCurNum())
            ->where("hide", "<>", 1);

        if (!empty($this->getMajor())){
            //later nani
        }

        $lists = $lists->orderby("stud_year")
        ->orderby("semester")
        ->orderby("sort_order")
        ->get();

        return $lists;
    }

    /**
     * Get the value of course
     */
    public function getCourse()
    {
        return $this->course;
    }

    /**
     * Set the value of course
     *
     * @return  self
     */
    public function setCourse($course)
    {
        $this->course = $course;

        return $this;
    }

    /**
     * Get the value of SchoolYear
     */
    public function getSchoolYear()
    {
        return $this->SchoolYear;
    }

    /**
     * Set the value of SchoolYear
     *
     * @return  self
     */
    public function setSchoolYear($SchoolYear)
    {
        $this->SchoolYear = $SchoolYear;

        return $this;
    }

    /**
     * Get the value of Semester
     */
    public function getSemester()
    {
        return $this->Semester;
    }

    /**
     * Set the value of Semester
     *
     * @return  self
     */
    public function setSemester($Semester)
    {
        $this->Semester = $Semester;

        return $this;
    }

    /**
     * Get the value of CurNum
     */
    public function getCurNum()
    {
        return $this->CurNum;
    }

    /**
     * Set the value of CurNum
     *
     * @return  self
     */
    public function setCurNum($CurNum)
    {
        $this->CurNum = $CurNum;

        return $this;
    }

    /**
     * Get the value of major
     */
    public function getMajor()
    {
        return $this->major;
    }

    /**
     * Set the value of major
     *
     * @return  self
     */
    public function setMajor($major)
    {
        $this->major = $major;

        return $this;
    }
}
