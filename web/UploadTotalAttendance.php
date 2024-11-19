<?php

require './api/db/db_connection.php';
// Include PHPExcel library to handle Excel file
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

// Function to get all students (store in associative array for quick lookup)
function getAllStudents($conn) {
    $students = [];
    $sql = "SELECT id, gr_no FROM student_info";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $students[$row['gr_no']] = $row['id'];
    }
    return $students;
}

// Function to get all subjects (store in associative array for quick lookup)
function getAllSubjects($conn) {
    $subjects = [];
    $sql = "SELECT id, short_name, lec_type FROM subject_info";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $subjects[$row['short_name']] = ['id' => $row['id'], 'lec_type' => $row['lec_type']];
    }
    return $subjects;
}

// Function to insert or update attendance data (execute all at once in batches)
function upsertAttendance($conn, $attendanceData) {
    $sql = "INSERT INTO total_attendance_info (student_info_id, subject_info_id, total, attend, lec_type) 
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE total = VALUES(total), attend = VALUES(attend)";
    $stmt = $conn->prepare($sql);

    foreach ($attendanceData as $data) {
        $stmt->bind_param('iiiss', $data['studentId'], $data['subjectId'], $data['total'], $data['attend'], $data['lec_type']);
        $stmt->execute();
    }
}



// Optimized function to increment column letters
function incrementColumn($col, $step = 1) {
    // Convert column letter to index, increment, and convert back to letter
    $colIndex = Coordinate::columnIndexFromString($col);
    return Coordinate::stringFromColumnIndex($colIndex + $step);
}

if (isset($_FILES['attendance_file'])) {
    $file = $_FILES['attendance_file']['tmp_name'];

    // Load the Excel file
    $spreadsheet = IOFactory::load($file);
    $worksheet = $spreadsheet->getActiveSheet();

    // Fetch all student and subject data once
    $students = getAllStudents($conn);
    $subjects = getAllSubjects($conn);

    $attendanceData = [];
    $row = 6;
    $subRow = 3;
    $TLheadingRow = $subRow + 1;
    $col = 'D';

    while ($worksheet->getCell('A' . $row)->getValue() != '') {
        $gr_no = $worksheet->getCell('A' . $row)->getValue();
        $studentId = $students[$gr_no] ?? null;

        if ($studentId) {
            while ($worksheet->getCell($col . $TLheadingRow)->getValue() != '') {
                $short_name = $worksheet->getCell($col . $subRow)->getValue();
                $subject = $subjects[$short_name] ?? null;

                if ($subject) {
                    $subjectId = $subject['id'];
                    $subjectLecType = $subject['lec_type'];

                    if ($subjectLecType == "TL") {
                        // Lecture
                        $total_L = $worksheet->getCell($col . $row)->getValue();
                        $attend_L = $worksheet->getCell(incrementColumn($col) . $row)->getValue();
                        if (isset($total_L) && isset($attend_L)) {
                            $attendanceData[] = [
                                'studentId' => $studentId,
                                'subjectId' => $subjectId,
                                'total' => $total_L,
                                'attend' => $attend_L,
                                'lec_type' => 'L'
                            ];
                        }
                        $col = incrementColumn($col, 2);

                        // Tutorial
                        $total_T = $worksheet->getCell($col . $row)->getValue();
                        $attend_T = $worksheet->getCell(incrementColumn($col) . $row)->getValue();
                        if (isset($total_T) && isset($attend_T)) {
                            $attendanceData[] = [
                                'studentId' => $studentId,
                                'subjectId' => $subjectId,
                                'total' => $total_T,
                                'attend' => $attend_T,
                                'lec_type' => 'T'
                            ];
                        }
                        $col = incrementColumn($col, 2);
                    } elseif ($subjectLecType == "T") {
                        // Tutorial only
                        $total_T = $worksheet->getCell($col . $row)->getValue();
                        $attend_T = $worksheet->getCell(incrementColumn($col) . $row)->getValue();
                        if (isset($total_T) && isset($attend_T)) {
                            $attendanceData[] = [
                                'studentId' => $studentId,
                                'subjectId' => $subjectId,
                                'total' => $total_T,
                                'attend' => $attend_T,
                                'lec_type' => 'T'
                            ];
                        }
                        $col = incrementColumn($col, 2);
                    } elseif ($subjectLecType == "L") {
                        // Lecture only
                        $total_L = $worksheet->getCell($col . $row)->getValue();
                        $attend_L = $worksheet->getCell(incrementColumn($col) . $row)->getValue();
                        if (isset($total_L) && isset($attend_L)) {
                            $attendanceData[] = [
                                'studentId' => $studentId,
                                'subjectId' => $subjectId,
                                'total' => $total_L,
                                'attend' => $attend_L,
                                'lec_type' => 'L'
                            ];
                        }
                        $col = incrementColumn($col, 2);
                    }
                }
            }
        }
        $col = 'D';  // Reset column to start from 'D' for the next row
        $row++;
    }

    if (!empty($attendanceData)) {
        upsertAttendance($conn, $attendanceData);
        echo "Attendance data uploaded successfully!";
    } else {
        echo "No attendance data to upload.";
    }

} else {
    echo "No file uploaded.";
}

$conn->close();
?>
