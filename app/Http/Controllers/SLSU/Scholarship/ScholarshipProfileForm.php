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
    protected $id, $letter, $studentName, $studentName2, $semester, $schoolYear;

    // Student Info
    protected $studentNo, $email, $contactNo, $age, $birthDate, $birthPlace, $sex, $civilStatus, $citizenship, $motherName, $fatherName, $motherOccu, $fatherOccu, $picture;

    // Academic Info
    protected $course, $major, $section, $scholarship, $schEnrollmentId, $gwa, $unitsEnrolled, $dateEnrolled;

    // Address
    protected $p_address, $zipCode;

    public function __construct()
    {
        // Initialize the LetterHead instance
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
                    'students.Picture',
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

            $runningTotal = 0;
            $runningUnit = 0;
            $unitsEnrolled = 0;

            $registrations = DB::connection($campus)
                ->table("registration as r")
                ->select("r.RegistrationID", "r.SchoolYear", "r.Semester", "r.DateEnrolled")
                ->where("r.finalize", 1)
                ->where("r.StudentNo", $scholar->student_no)
                ->get();

            foreach ($registrations as $registration) {
                $gradesTable = "grades" . $registration->SchoolYear . $registration->Semester;

                $grades = DB::connection($campus)
                    ->table($gradesTable . " as g")
                    ->select("t.units", "g.final", "g.inc")
                    ->leftJoin("transcript as t", "g.sched", "=", "t.id")
                    ->where("g.gradesid", $registration->RegistrationID)
                    ->where("t.exempt", "<>", 1)
                    ->get();

                foreach ($grades as $grade) {
                    $out = GENERAL::ComputeForGWA($grade->final, $grade->inc, $grade->units);
                    $runningTotal += $out['RunningTimes'];
                    $runningUnit += $out['RunningUnit'];
                    $unitsEnrolled += $grade->units;
                }
            }

            $gwa = $runningUnit > 0 ? $runningTotal / $runningUnit : 0;

            $this->scholarship = $scholar->scholarship_name ?? 'N/A';
            $this->studentNo = $scholar->student_no;
            $this->studentName = $scholar->LastName . ', ' . $scholar->FirstName . ' ' . ($scholar->MiddleName ? substr($scholar->MiddleName, 0, 1) . '.' : '');
            $this->studentName2 = $scholar->FirstName . ' ' . ($scholar->MiddleName ? substr($scholar->MiddleName, 0, 1) . '. ' : '') . $scholar->LastName;
            $this->sex = $scholar->Sex;
            $this->course = $scholar->Course;
            $this->section = $scholar->StudentYear . ' - ' . $scholar->Section;
            $this->birthDate = $scholar->BirthDate ? date('F j, Y', strtotime($scholar->BirthDate)) : 'N/A';
            $this->birthPlace = $scholar->Birthplace;
            $this->major = $scholar->major ?? 'N/A';
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
            $this->picture = $scholar->Picture;

            $this->gwa = number_format($gwa, 3);
            $this->unitsEnrolled = $unitsEnrolled;
            $this->dateEnrolled = $registration->DateEnrolled ?? 'N/A';
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

        $leftMargin = 25.4; // 1 inch
        $rightMargin = 25.4;
        $pageWidth = $this::GetPageWidth();
        $usableWidth = $pageWidth - $leftMargin - $rightMargin;

        $this::SetFont('cambria', 'B', 12);
        $html1 = '<p>OFFICE OF STUDENTS AND AUXILIARY SERVICES</p>';
        $this::writeHTMLCell($usableWidth, 0, $leftMargin, $startY, $html1, 0, 1, false, true, 'C');

        $startY += 10;
        $html2 = '<p>STUDENTS\'S SCHOLARSHIP/GRANT CERTIFICATION</p>';
        $this::writeHTMLCell($usableWidth, 0, $leftMargin, $startY, $html2, 0, 1, false, true, 'C');
    }

    public function content()
    {
        $this->generate();
        $startY = 65;

        $leftMargin = 25.4; // 1 inch
        $rightMargin = 25.4;
        $pageWidth = $this::GetPageWidth();
        $usableWidth = $pageWidth - $leftMargin - $rightMargin;

        $html1 = '<p style="font-family: cambria; font-size: 11px;"><strong>Direction: Please fill-up the scholarship profile form completely and provide the required information.
                    <br>Write "NA" if not applicable. Do not leave blank.</strong>
                  </p>
                  <p style="font-family: cambria; font-size: 10px;">Type of Scholarship/Grant: <strong>' . $this->scholarship . '</strong></p>
                  <table cellpadding="1" cellspacing="0" style="font-family: cambria; font-size: 10px; width: 100%;">
                    <tr>
                        <td style="width: 46%;">Program: <strong>' . $this->course . '</strong></td>
                        <td style="width: 54%;">Semester: &nbsp;&nbsp;&nbsp;&nbsp; 1st &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 2nd &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Summer</td>
                    </tr>
                    <tr>
                        <td style="width: 46%;"></td>
                        <td style="width: 54%;">Academic Year: <strong>' . $this->schoolYear . '</strong></td>
                    </tr>
                    <br>
                    <tr>
                        <td style="width: 46%;">Major: <strong>' . $this->major . '</strong></td>
                        <td style="width: 54%;">Gen. Ave.: <strong>' . $this->gwa . '</strong></td>
                    </tr>
                    <tr>
                        <td style="width: 46%;">Year & Sec.: <strong>' . $this->section . '</strong></td>
                        <td style="width: 54%;">Units Enrolled: <strong>' . $this->unitsEnrolled . '</strong></td>
                    </tr>
                    <tr>
                        <td style="width: 46%;">Student No.: <strong>' . $this->studentNo . '</strong></td>
                        <td style="width: 54%;">Date Enrolled: <strong>' . $this->dateEnrolled . '</strong></td>
                    </tr>
                    <tr>
                        <td style="width: 46%;">Contact No.: <strong>' . $this->contactNo . '</strong></td>
                        <td style="width: 54%;">Date Complied: _______________</td>
                    </tr>
                    <tr>
                        <td style="width: 46%;">E-mail Add.: <strong>' . $this->email . '</strong></td>
                        <td style="width: 54%;"></td>
                    </tr>
                  </table>
                  <p style="font-family: cambria; font-size: 10px;">
                    <br><br><strong>PERSONAL INFORMATION:</strong>
                    <br>
                    <br><table cellpadding="1" cellspacing="0" style="font-family: cambria; font-size: 10px; width: 100%;">
                    <tr>
                        <td style="width: 50%;">Name: <strong>' . $this->studentName . '</strong></td>
                        <td style="width: 25%;">Age: <strong>' . $this->age . '</strong></td>
                        <td style="width: 25%;">Sex: <strong>' . $this->sex . '</strong></td>
                    </tr>
                    <tr>
                        <td style="width: 75%;">Home Address: <strong>' . $this->p_address . ' </strong></td>
                        <td style="width: 25%;">Zip Code: <strong>' . $this->zipCode . '</strong></td>
                    </tr>
                    <tr>
                        <td style="width: 30%; font-size: 8px;">Date of Birth: <span style="font-size: 9px;"><strong>' . $this->birthDate . '</strong></span></td>
                        <td style="width: 45%; font-size: 9px;">Place of Birth: <strong>' . $this->birthPlace . '</strong></td>
                        <td style="width: 25%; font-size: 9px;">Citizenship: <strong>' . $this->citizenship . '</strong></td>
                    </tr>
                    <tr>
                        <td style="width: 40%;">Civil Status: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Single &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Married</td>
                        <td style="width: 50%;">Others, pls specify: ________________________</td>
                    </tr>
                    <tr>
                        <td style="width: 50%;">If married, name of spouse: <strong>(name sa spouse)</strong></td>
                        <td style="width: 50%;">Spouse\' Occupation: <strong>(spouse occupation)</strong></td>
                    </tr>
                    <tr>
                        <td style="width: 58%;">Mother\'s Complete Name: <strong>' . $this->motherName . '</strong></td>
                        <td style="width: 42%;">Occupation: <strong>' . $this->motherOccu . '</strong></td>
                    </tr>
                    <tr>
                        <td style="width: 58%;">Father\'s Complete Name: <strong>' . $this->fatherName . '</strong></td>
                        <td style="width: 42%;">Occupation: <strong>' . $this->fatherOccu . '</strong></td>
                    </tr>
                    <tr>
                        <td style="width: 50%;">Total Family Members: ________</td>
                        <td style="width: 50%;">Household Per Capita Income: __________</td>
                    </tr>
                    <tr>
                        <td style="width: 30%;">4Ps Member: Yes &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; No</td>
                        <td style="width: 70%;">If yes, please specify DSWD Household No.: ___________________</td>
                    </tr>
                    </table>
                    <br><br><br><br>&nbsp;&nbsp;&nbsp;<strong>' . strtoupper($this->studentName2) . '</strong>
                  </p>';
        $this::writeHTMLCell($usableWidth, 0, $leftMargin, $startY, $html1, 0, 1, false, true, 'L');

        $html2 = '<p style="font-style: italic; font-family: cambria; font-size: 10px;"><span style="display: inline-block; width: 100%;">__________________________________</span>
        <br><span style="font-style: italic;">Printed Name and Signature</span>
        <br><br><br><br><br>Disclaimer: By completing this form, you voluntarily and freely give consent to Southern Leyte State University to collect and process the above personal information 
        (protected by R.A. 10173, Data Privacy Act of 2012) for records purposes use only.
        </p>';
        $this::writeHTMLCell($usableWidth, 0, $leftMargin, 207, $html2, 0, 1, false, true, 'L');


        $picture = public_path('storage/photo/SG/' . $this->picture);

        if (!empty($this->picture) && file_exists($picture)) {
            $this::Image($picture, 155, 75, 50.8, 50.8);
        } else {
            $pictureBoxX = 155;
            $pictureBoxY = 75;
            $pictureBoxSize = 50.8;
            $this::Rect($pictureBoxX, $pictureBoxY, $pictureBoxSize, $pictureBoxSize, 'D');

            $this::setXY($pictureBoxX, $pictureBoxY + ($pictureBoxSize / 2) - 6);
            $this::SetFont('cambria', 'B', 12);
            $this::Cell($pictureBoxSize, 12, "No Image", 0, 0, 'C');
        }

        $pictureBoxX = 155;
        $pictureBoxY = 75;
        $pictureBoxSize = 50.8;
        $this::Rect($pictureBoxX, $pictureBoxY, $pictureBoxSize, $pictureBoxSize, 'D');

        $startY += 16.5;

        // first semester checkbox
        $checkboxX1 = 115;
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

        $this::SetFont('zapfdingbats', '', 10); // font for checkmark
        if ($this->semester === '1st') {
            $this::Text($checkboxX1 + -1, $checkboxY1 + -1, '4');
        } elseif ($this->semester === '2nd') {
            $this::Text($checkboxX2 + -1, $checkboxY2 + -1, '4');
        } elseif ($this->semester === 'Sum') {
            $this::Text($checkboxX3 + -1, $checkboxY3 + -1, '4');
        } elseif ($this->semester === 'Sum2') {
            $this::Text($checkboxX3 + -1, $checkboxY3 + -1, '4');
        }

        $startY += 17;


        $startY += 64;

        // Single checkbox
        $checkboxX4 = 47;
        $checkboxY4 = $startY;
        $this::Rect($checkboxX4, $checkboxY4, $checkboxSize, $checkboxSize);

        // Married checkbox
        $checkboxX5 = 63;
        $checkboxY5 = $checkboxY4;
        $this::Rect($checkboxX5, $checkboxY5, $checkboxSize, $checkboxSize);

        // others checkbox
        $checkboxX6 = 85;
        $checkboxY6 = $checkboxY5;
        $this::Rect($checkboxX6, $checkboxY6, $checkboxSize, $checkboxSize);

        $this::SetFont('zapfdingbats', '', 10); // Use ZapfDingbats font for the checkmark
        if ($this->civilStatus === 'Single') {
            $this::Text($checkboxX4 + -1, $checkboxY4 + -1, '4');
        } elseif ($this->civilStatus === 'Married') {
            $this::Text($checkboxX5 + -1, $checkboxY5 + -1, '4');
        }

        $startY += 30;

        // 4Ps checkbox
        // Yes checkbox
        $checkboxX7 = 54;
        $checkboxY7 = $checkboxY6 + 26;
        $this::Rect($checkboxX7, $checkboxY7, $checkboxSize, $checkboxSize);

        // No checkbox
        $checkboxX8 = 65;
        $checkboxY8 = $checkboxY7;
        $this::Rect($checkboxX8, $checkboxY8, $checkboxSize, $checkboxSize);
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
