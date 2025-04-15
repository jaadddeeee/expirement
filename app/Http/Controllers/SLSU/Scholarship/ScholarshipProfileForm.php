<?php

namespace App\Http\Controllers\SLSU\Scholarship;

use Elibyy\TCPDF\Facades\TCPDF;
use App\Http\Controllers\SLSU\Report\LetterHead;
use Illuminate\Contracts\Encryption\DecryptException;

use Illuminate\Support\Facades\DB;
use DateTime;



use GENERAL;

class ScholarshipProfileForm extends TCPDF
{
    protected $id, $letter, $studentName, $semester, $schoolYear;

    // Student Info
    protected $studentNo, $email, $contactNo, $age, $birthDate, $birthPlace, $sex, $civilStatus, $citizenship, $motherName, $fatherName, $motherOccu, $fatherOccu;

    // Academic Info
    protected $course, $major, $section, $scholarship, $schEnrollmentId;

    // Address
    protected $p_address, $zipCode;

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
                    'students.major',
                    'students.Section',
                    'students.ContactNo',
                    'students.email',
                    'students.BirthDate',
                    'students.Birthplace',
                    'students.Sex',
                    'students.civil_status',
                    'students.nationality',
                    'students.mother_name',
                    'students.mother_occu',
                    'students.father_name',
                    'students.father_occu',
                    'students.p_street',
                    'students.p_municipality',
                    'students.p_province',
                    'students.p_zip',
                    'sch_scholarships.sch_name AS scholarship_name',
                    'sch_scholar_enrollments.id AS enrollment_id',
                    'sch_scholar_enrollments.semester',
                    'sch_scholar_enrollments.school_year'
                )
                ->first();

            if (!$scholar) {
                throw new \Exception('Scholar not found');
            }

            // Calculate age
            if (empty($scholar->BirthDate)) {
                $age = 0;
            } else {
                $from = new DateTime($scholar->BirthDate);
                $to = new DateTime('today');
                $age = $from->diff($to)->y;
            }

            $this->scholarship = $scholar->scholarship_name ?? 'N/A';
            $this->studentNo = $scholar->student_no;
            $this->studentName = $scholar->LastName . ', ' . $scholar->FirstName . ' ' . ($scholar->MiddleName ? substr($scholar->MiddleName, 0, 1) . '.' : '');
            $this->sex = $scholar->Sex;
            $this->course = $scholar->Course;
            $this->section = $scholar->StudentYear . ' - ' . $scholar->Section;
            $this->birthDate = $scholar->BirthDate ? date('F j, Y', strtotime($scholar->BirthDate)) : 'N/A';
            $this->birthPlace = $scholar->Birthplace;
            $this->major = $scholar->major;
            $this->motherName = $scholar->mother_name;
            $this->motherOccu = $scholar->mother_occu;
            $this->fatherName = $scholar->father_name;
            $this->fatherOccu = $scholar->father_occu;

            $this->semester = GENERAL::Semesters()[$scholar->semester]['Short'];
            $this->schoolYear = GENERAL::setSchoolYearLabel($scholar->school_year, $scholar->semester);
            $this->contactNo = $scholar->ContactNo;
            $this->email = $scholar->email;
            $this->age = $age;
            $this->p_address = $scholar->p_street . ', ' . $scholar->p_municipality . ', ' . $scholar->p_province;
            $this->zipCode = $scholar->p_zip;
            $this->citizenship = $scholar->nationality;
            $this->civilStatus = $scholar->civil_status;
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

        $startY += 13;
        $this::setXY(25, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(0, 10, "Type of Scholarship/Grant:", 0, 1, 'L');

        $this::setXY(67, $startY);
        $this::SetFont('cambria', 'B', 10);
        $this::Cell(0, 10, "{$this->scholarship}", 0, 1, 'L');

        // first semester checkbox
        $checkboxX1 = 122;
        $checkboxY1 = $startY + 12.5;
        $checkboxSize = 3;
        $this::Rect($checkboxX1, $checkboxY1, $checkboxSize, $checkboxSize);

        // second semester checkbox
        $checkboxX2 = $checkboxX1 + 10;
        $checkboxY2 = $checkboxY1;
        $this::Rect($checkboxX2, $checkboxY2, $checkboxSize, $checkboxSize);

        // summer checkbox
        $checkboxX3 = $checkboxX2 + 11;
        $checkboxY3 = $checkboxY2;
        $this::Rect($checkboxX3, $checkboxY3, $checkboxSize, $checkboxSize);

        $this::SetFont('zapfdingbats', '', 10); // Use ZapfDingbats font for the checkmark
        if ($this->semester === '1st') {
            $this::Text($checkboxX1 + -1, $checkboxY1 + -1, '4');
        } elseif ($this->semester === '2nd') {
            $this::Text($checkboxX2 + -1, $checkboxY2 + -1, '4');
        } elseif ($this->semester === 'Sum') {
            $this::Text($checkboxX3 + -1, $checkboxY3 + -1, '4');
        } elseif ($this->semester === 'Sum2') {
            $this::Text($checkboxX3 + -1, $checkboxY3 + -1, '4');
        }

        $pictureBoxX = 161;
        $pictureBoxY = 73;
        $pictureBoxSize = 32;
        $this::Rect($pictureBoxX, $pictureBoxY, $pictureBoxSize, $pictureBoxSize, 'D');


        $startY += 9;
        $this::setXY(25, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(0, 10, "Program:                                                                                     Semester:      1st       2nd       Summer", 0, 1, 'L');

        $this::setXY(40, $startY);
        $this::SetFont('cambria', 'B', 10);
        $this::Cell(0, 10, "{$this->course}", 0, 1, 'L');

        $startY += 5;
        $this::setXY(105, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(0, 10, "Academic Year :", 0, 1, 'L');

        $this::setXY(130, $startY);
        $this::SetFont('cambria', 'B', 10);
        $this::Cell(0, 10, "{$this->schoolYear}", 0, 0, 'L');

        $startY += 10;
        $this::setXY(25, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(0, 10, "Major            :", 0, 1, 'L');

        $this::setXY(45, $startY);
        $this::SetFont('cambria', 'B', 10);
        $this::Cell(0, 10, "{$this->major}", 0, 1, 'L');

        $this::setXY(105, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(0, 10, "Gen. Ave.            : ___________________", 0, 1, 'L');

        $startY += 5;
        $this::setXY(25, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(0, 10, "Year & Sec.  :", 0, 1, 'L');

        $this::setXY(45, $startY);
        $this::SetFont('cambria', 'B', 10);
        $this::Cell(0, 10, "{$this->section}", 0, 1, 'L');

        $this::setXY(105, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(0, 10, "Units Enrolled  : ___________________", 0, 1, 'L');

        $startY += 5;
        $this::setXY(25, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(0, 10, "Student No. :", 0, 1, 'L');

        $this::setXY(45, $startY);
        $this::SetFont('cambria', 'B', 10);
        $this::Cell(0, 10, "{$this->studentNo}", 0, 1, 'L');


        $this::setXY(105, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(0, 10, "Date Enrolled   : ___________________", 0, 1, 'L');


        $startY += 5;
        $this::setXY(25, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(0, 10, "Contact No. :", 0, 1, 'L');

        $this::setXY(45, $startY);
        $this::SetFont('cambria', 'B', 10);
        $this::Cell(0, 10, "{$this->contactNo}", 0, 1, 'L');

        $this::setXY(105, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(0, 10, "Date Complied : ___________________", 0, 1, 'L');

        $startY += 5;
        $this::setXY(25, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(0, 10, "Email Add.  :", 0, 1, 'L');

        $this::setXY(45, $startY);
        $this::SetFont('cambria', 'B', 10);
        $this::Cell(0, 10, "{$this->email}", 0, 1, 'L');

        $startY += 13;
        $this::setXY(25, $startY);
        $this::SetFont('cambria', 'B', 10);
        $this::Cell(0, 10, "PERSONAL INFORMATION:", 0, 1, 'L');

        $startY += 8;
        $this::setXY(25, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(0, 10, "Name                  :   ", 0, 1, 'L');

        $this::setXY(50, $startY);
        $this::SetFont('cambria', 'B', 10);
        $this::Cell(0, 10, "{$this->studentName}", 0, 1, 'L');

        $this::setXY(120, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(0, 10, "Age          : ", 0, 1, 'L');

        $this::setXY(135, $startY);
        $this::SetFont('cambria', 'B', 10);
        $this::Cell(0, 10, "{$this->age}", 0, 1, 'L');

        $this::setXY(145, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(0, 10, "Sex:", 0, 1, 'L');

        $this::setXY(152, $startY);
        $this::SetFont('cambria', 'B', 10);
        $this::Cell(0, 10, "{$this->sex}", 0, 1, 'L');

        $startY += 5;
        $this::setXY(25, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(0, 10, "Home Address :", 0, 1, 'L');

        $this::setXY(50, $startY);
        $this::SetFont('cambria', 'B', 10);
        $this::Cell(0, 10, "{$this->p_address}", 0, 1, 'L');

        $this::setXY(120, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(0, 10, "Zip Code:", 0, 1, 'L');

        $this::setXY(135, $startY);
        $this::SetFont('cambria', 'B', 10);
        $this::Cell(0, 10, "{$this->zipCode}", 0, 1, 'L');

        $startY += 5;
        $this::setXY(25, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(0, 10, "Date of Birth     :", 0, 1, 'L');

        $this::setXY(50, $startY);
        $this::SetFont('cambria', 'B', 10);
        $this::Cell(0, 10, "{$this->birthDate}", 0, 1, 'L');

        $this::setXY(85, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(0, 10, "Place of Birth:", 0, 1, 'L');

        $this::setXY(107, $startY);
        $this::SetFont('cambria', 'B', 10);
        $this::Cell(0, 10, "{$this->birthPlace}", 0, 1, 'L');

        $this::setXY(145, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(0, 10, "Citizenship:", 0, 1, 'L');

        $this::setXY(164, $startY);
        $this::SetFont('cambria', 'B', 10);
        $this::Cell(0, 10, "{$this->citizenship}", 0, 1, 'L');

        $startY += 5;
        $this::setXY(25, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(0, 10, "Civil Status:         Single         Married         others, pls specify _________________________", 0, 1, 'L');

        // Single checkbox
        $checkboxX4 = 47;
        $checkboxY4 = $startY + 3.5;
        $this::Rect($checkboxX4, $checkboxY4, $checkboxSize, $checkboxSize);

        // Married checkbox
        $checkboxX5 = 63;
        $checkboxY5 = $checkboxY4;
        $this::Rect($checkboxX5, $checkboxY5, $checkboxSize, $checkboxSize);

        // others checkbox
        $checkboxX6 = 82;
        $checkboxY6 = $checkboxY5;
        $this::Rect($checkboxX6, $checkboxY6, $checkboxSize, $checkboxSize);

        $this::SetFont('zapfdingbats', '', 10); // Use ZapfDingbats font for the checkmark
        if ($this->civilStatus === 'Single') {
            $this::Text($checkboxX4 + -1, $checkboxY4 + -1, '4');
        } elseif ($this->civilStatus === 'Married') {
            $this::Text($checkboxX5 + -1, $checkboxY5 + -1, '4');
        }

        $startY += 5;
        $this::setXY(25, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(0, 10, "If married, name of spouse:______________________________________ Spouse' Occupation:__________________", 0, 1, 'L');

        $startY += 5;
        $this::setXY(25, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(0, 10, "Mother's Complete Name:", 0, 1, 'L');

        $this::setXY(65, $startY);
        $this::SetFont('cambria', 'B', 10);
        $this::Cell(0, 10, "{$this->motherName}", 0, 1, 'L');

        $this::setXY(116, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(0, 10, "Occupation:", 0, 1, 'L');

        $this::setXY(135, $startY);
        $this::SetFont('cambria', 'B', 10);
        $this::Cell(0, 10, "{$this->motherOccu}", 0, 1, 'L');

        $startY += 5;
        $this::setXY(25, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(0, 10, "Father's Complete Name :", 0, 1, 'L');

        $this::setXY(65, $startY);
        $this::SetFont('cambria', 'B', 10);
        $this::Cell(0, 10, "{$this->fatherName}", 0, 1, 'L');

        $this::setXY(135, $startY);
        $this::SetFont('cambria', 'B', 10);
        $this::Cell(0, 10, "{$this->fatherOccu}", 0, 1, 'L');


        $this::setXY(116, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(0, 10, "Occupation:", 0, 1, 'L');

        $startY += 5;
        $this::setXY(25, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(0, 10, "Total Family Members:___________         Household Per Capita Income:_________________________________", 0, 1, 'L');

        $startY += 5;
        $this::setXY(25, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(0, 10, "4Ps Member?  Yes         No            If yes, please specify DSWD Household No. _________________________", 0, 1, 'L');

        $checkboxX7 = 54;
        $checkboxY7 = $checkboxY6 + 25;
        $this::Rect($checkboxX7, $checkboxY7, $checkboxSize, $checkboxSize);

        $checkboxX8 = 65;
        $checkboxY8 = $checkboxY7;
        $this::Rect($checkboxX8, $checkboxY8, $checkboxSize, $checkboxSize);

        $startY += 15;
        $this::setXY(25, $startY);
        $this::SetFont('cambria', '', 10);
        $this::Cell(0, 10, "________________________________________", 0, 1, 'L');

        $startY += 5;
        $this::setXY(25, $startY);
        $this::SetFont('cambria', 'I', 10);
        $this::Cell(0, 10, "Printed Name & Signature", 0, 1, 'L');


        $startY += 20;
        $this::setXY(25, $startY);
        $this::SetFont('cambria', 'I', 10);
        $this::Cell(0, 10, "Disclaimer: By completing this form, you voluntarily and freely give consent to Southern Leyte State ", 0, 1, 'L');

        $startY += 5;
        $this::setXY(25, $startY);
        $this::SetFont('cambria', 'I', 10);
        $this::Cell(0, 10, "University to collect and process the above personal information (protected by R.A. 10173, Data Privacy Act ", 0, 1, 'L');


        $startY += 5;
        $this::setXY(25, $startY);
        $this::SetFont('cambria', 'I', 10);
        $this::Cell(0, 10, "of 2012) for records purposes use only.", 0, 1, 'L');


        $this::setXY(163, 83);
        $this::SetFont('cambria', 'B', 12);
        $this::Cell(0, 10, "2x2 PICTURE", 0, 1, 'L');
    }

    public function Footer()
    {
        $this->letter->ReportFooter(['QC' => config('QC.SS13')]);
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
}
