<?php

namespace App\Http\Controllers\SLSU\Scholarship;

use Elibyy\TCPDF\Facades\TCPDF;
use App\Http\Controllers\SLSU\Report\LetterHead;
use Illuminate\Contracts\Encryption\DecryptException;

use Illuminate\Support\Facades\DB;


use GENERAL;

class ScholarshipProfileForm extends TCPDF
{
    protected $id;
    protected $letter;
    protected $studentName;
    protected $courseYear;
    protected $studentNo;
    protected $scholarship;
    protected $semester;
    protected $schoolYear;
    protected $date;
    protected $schEnrollmentId;

    public function __construct()
    {
        $this->letter = new LetterHead();
    }

    public function generate()
    {
        try {
            $campus = strtolower(session('campus'));

            if (!$campus) {
                throw new \Exception('Invalid campus database connection');
            }

            $scholar = DB::connection($campus)
                ->table('sch_scholar_details')
                ->join('students', 'sch_scholar_details.student_no', '=', 'students.StudentNo')
                ->join('sch_scholar_enrollments', 'sch_scholar_details.id', '=', 'sch_scholar_enrollments.scholar_id')
                ->join('sch_scholarships', 'sch_scholar_details.scholarship_id', '=', 'sch_scholarships.id')
                ->where('sch_scholar_details.id', $this->getId())
                ->where('sch_scholar_enrollments.id', $this->getSchEnrollmentId())
                ->where('sch_scholar_enrollments.school_year', $this->getSchoolYear())
                ->where('sch_scholar_enrollments.semester', $this->getSemester())
                ->select(
                    'sch_scholar_details.*',
                    'students.FirstName',
                    'students.MiddleName',
                    'students.LastName',
                    'students.Course',
                    'students.StudentYear',
                    'sch_scholarships.sch_name AS scholarship_name',
                    'sch_scholar_enrollments.id AS enrollment_id',
                    'sch_scholar_enrollments.semester',
                    'sch_scholar_enrollments.school_year'
                )
                ->first();

            if (!$scholar) {
                throw new \Exception('Scholar not found');
            }

            $this->studentName = $scholar->FirstName . ' ' . ($scholar->MiddleName ? substr($scholar->MiddleName, 0, 1) . '.' : '') . ' ' . $scholar->LastName;
            $this->courseYear = $scholar->Course . ' - ' . $scholar->StudentYear;
            $this->studentNo = $scholar->student_no;
            $this->scholarship = $scholar->scholarship_name ?? 'N/A';
            $this->semester = GENERAL::Semesters()[$scholar->semester]['Short'];
            $this->schoolYear = GENERAL::setSchoolYearLabel($scholar->school_year, $scholar->semester);
            $this->date = date('F j, Y');
        } catch (DecryptException $e) {
            throw new \Exception('Invalid or corrupted scholarship ID');
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function Header()
    {
        $this->letter->ReportHeader();

        $startY = 42;

        $pageWidth = $this::GetPageWidth();

        $this::SetFont('cambria', 'B', 12);
        $textWidth1 = $this::GetStringWidth("OFFICE OF STUDENTS AND AUXILIARY SERVICES");

        $centerX1 = ($pageWidth - $textWidth1) / 2;

        $this::setXY($centerX1, $startY);
        $this::Cell($textWidth1, 5, "OFFICE OF STUDENTS AND AUXILIARY SERVICES", 0, 1, 'C');

        $startY += 10;
        $this::SetFont('cambria', 'B', 12);
        $textWidth2 = $this::GetStringWidth("STUDENTS'S SCHOLARSHIP/GRANT CERTIFICATION");

        $centerX2 = ($pageWidth - $textWidth2) / 2;

        $this::setXY($centerX2, $startY);
        $this::Cell($textWidth2, 4, "STUDENTS'S SCHOLARSHIP/GRANT CERTIFICATION", 0, 1, 'C');
    }

    public function content()
    {
        $this->generate();

        $startY = 65;
        $this::setXY(25, $startY);
        $this::SetFont('cambria', 'B', 11);
        $this::Cell(0, 10, "Direction: Please fill up the scholarship profile form completely and provide the required", 0, 1, 'L');

        $startY += 5;
        $this::setXY(44, $startY);
        $this::SetFont('cambria', 'B', 11);
        $this::Cell(0, 10, "information.", 0, 1, 'L');

        $startY += 5;
        $this::setXY(25, $startY);
        $this::SetFont('cambria', 'B', 11);
        $this::Cell(0, 10, "Write “NA” if not applicable. Do not leave blank.", 0, 1, 'L');

        // Add a checkbox
        $startY += 13;
        $this::setXY(25, $startY);
        $this::SetFont('cambria', '', 11);
        $this::Cell(0, 10, "Type of Scholarship/Grant: ", 0, 1, 'L');

        // Draw the checkbox for "1st Semester"
        $checkboxX1 = 131; // X position of the 1st Semester checkbox
        $checkboxY1 = $startY + 11.5; // Y position of the checkbox
        $checkboxSize = 3; // Size of the checkbox

        $this::Rect($checkboxX1, $checkboxY1, $checkboxSize, $checkboxSize); // Draw the checkbox

        // Draw the checkbox for "2nd Semester"
        $checkboxX2 = $checkboxX1 + 11; // X position of the 2nd Semester checkbox (adjusted to be beside the 1st)
        $checkboxY2 = $checkboxY1; // Same Y position as the 1st Semester checkbox

        $this::Rect($checkboxX2, $checkboxY2, $checkboxSize, $checkboxSize); // Draw the 2nd Semester checkbox

        // Draw the checkbox for "2nd Semester"
        $checkboxX3 = $checkboxX2 + 12; // X position of the 2nd Semester checkbox (adjusted to be beside the 1st)
        $checkboxY3 = $checkboxY2; // Same Y position as the 1st Semester checkbox

        $this::Rect($checkboxX3, $checkboxY3, $checkboxSize, $checkboxSize); // Draw the 2nd Semester checkbox

        // Position (in mm) from top-left corner of the page
        $pictureBoxX = 133; // X position of the picture box
        $pictureBoxY = 75; // Y position from top

        // Size for 2x2 inch box (1 inch = 25.4 mm)
        $pictureBoxSize = 50.8; // 2 inches in mm

        // Optional: set border style (if needed)
        $style = array(
            'all' => array(
                'width' => 0.5, // border width
                'color' => array(0, 0, 0) // black
            )
        );

        // Draw the box
        $this::Rect($pictureBoxX, $pictureBoxY, $pictureBoxSize, $pictureBoxSize, 'D'); // 'D' = Draw only (no fill)


        $startY += 8;
        $this::setXY(25, $startY);
        $this::SetFont('cambria', '', 11);
        $this::Cell(0, 10, "Program ___________________________                                       Semester:     1st       2nd       Summer", 0, 1, 'L');
    }

    public function Footer()
    {
        $this->letter->ReportFooter(['QC' => config('QC.SS06')]);
    }


    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getSchEnrollmentId()
    {
        return $this->schEnrollmentId;
    }

    public function setSchEnrollmentId($schEnrollmentId)
    {
        $this->schEnrollmentId = $schEnrollmentId;
        return $this;
    }

    public function getStudentName()
    {
        return $this->studentName;
    }

    public function setStudentName($studentName)
    {
        $this->studentName = $studentName;
        return $this;
    }

    public function getCourseYear()
    {
        return $this->courseYear;
    }

    public function setCourseYear($courseYear)
    {
        $this->courseYear = $courseYear;
        return $this;
    }

    public function getStudentNo()
    {
        return $this->studentNo;
    }

    public function setStudentNo($studentNo)
    {
        $this->studentNo = $studentNo;
        return $this;
    }

    public function getScholarship()
    {
        return $this->scholarship;
    }

    public function setScholarship($scholarship)
    {
        $this->scholarship = $scholarship;
        return $this;
    }

    public function getSemester()
    {
        return $this->semester;
    }

    public function setSemester($semester)
    {
        $this->semester = $semester;
        return $this;
    }

    public function getSchoolYear()
    {
        return $this->schoolYear;
    }

    public function setSchoolYear($schoolYear)
    {
        $this->schoolYear = $schoolYear;
        return $this;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }
}
