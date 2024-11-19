<?php
require './api/db/db_connection.php';
require 'vendor/autoload.php'; // Include PHPExcel or PHPSpreadsheet library

use PhpOffice\PhpSpreadsheet\IOFactory;

// Check if a file was uploaded and the form was submitted
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['file']['tmp_name'];
    $fileName = $_FILES['file']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Ensure the file is an Excel file
    if (in_array($fileExtension, ['xlsx', 'xls'])) {
        // Load the Excel file
        $spreadsheet = IOFactory::load($fileTmpPath);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        foreach ($sheetData as $rowIndex => $row) {
            // Skip the header row
            if ($rowIndex == 1) {
                continue;
            }

            // Check if column A is empty and break the loop if it is
            if (empty($row['A'])) {
                break; // Stop processing if column A is empty
            }

            // Extract data from columns (adjust column names based on your Excel structure)
            $enrollmentNo = $row['A'];
            $grNo = $row['B'];
            $firstName = $row['C'];
            $lastName = $row['D'];
            $gender = $row['E'];
            $studentEmail = $row['F'];
            $studentPhone = $row['G'];
            $sem = $row['H'];
            $eduType = strtolower($row['I']);
            $class = strtoupper($row['J']);
            $batch = strtolower($row['K']);
            $batchStartYear = $row['L'];
            $batchEndYear = $row['M'];
            $parentName = $row['N'];
            $parentPhone = $row['O'];

            // 1. Insert Parent Data into user_login
            $parentUsername = $grNo;
            $parentPassword = password_hash("$firstName@$grNo", PASSWORD_DEFAULT);
            $role = 'parent';

            $stmt = $conn->prepare("SELECT username FROM user_login WHERE username = ?");
            $stmt->bind_param("s", $parentUsername);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 0) {
                $stmt = $conn->prepare("INSERT INTO user_login (username, password, role,email,isactive, phone_no) VALUES (?, ?, ?, null, 1, ?)");
                $stmt->bind_param("ssss", $parentUsername, $parentPassword, $role, $parentPhone);
                $stmt->execute();
            }

            // 2. Insert into parents_info and retrieve parent_info_id
            $stmt = $conn->prepare("INSERT INTO parents_info (user_login_id, parent_name) VALUES (?, ?)");
            $stmt->bind_param("ss", $parentUsername, $parentName);
            $stmt->execute();
            $parentInfoId = $conn->insert_id; // Get the inserted parent's ID

            // 3. Insert Student Data into user_login
            $studentUsername = $enrollmentNo;
            $studentPassword = password_hash("$firstName@$grNo", PASSWORD_DEFAULT);
            $studentRole = 'student';

            // Check if the student user already exists
            $stmt = $conn->prepare("SELECT username FROM user_login WHERE username = ?");
            $stmt->bind_param("s", $studentUsername);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 0) {
                // Insert student into user_login
                $stmt = $conn->prepare("INSERT INTO user_login (username, password, role, isactive, email, phone_no) VALUES (?, ?, ?, 1, ?, ?)");
                $stmt->bind_param("sssss", $studentUsername, $studentPassword, $studentRole, $studentEmail, $studentPhone);
                $stmt->execute();
            }

            // 4. Retrieve class_info_id based on classname and sem_info_id
            $stmt = $conn->prepare("SELECT id FROM class_info WHERE classname = ? AND batch = ? AND sem_info_id = (SELECT id FROM sem_info WHERE sem = ? AND edu_type = ? )");
            $stmt->bind_param("ssis", $class, $batch, $sem, $eduType);
            $stmt->execute();
            $result = $stmt->get_result();  
            $classInfoId = $result->fetch_assoc()['id'];

            // 5. Retrieve sem_info_id based on sem and edu_type
            $stmt = $conn->prepare("SELECT id FROM sem_info WHERE sem = ? AND edu_type = ?");
            $stmt->bind_param("is", $sem, $eduType);
            $stmt->execute();
            $result = $stmt->get_result();
            $semInfoId = $result->fetch_assoc()['id'];

            // 6. Insert data into student_info
            $stmt = $conn->prepare("INSERT INTO student_info (user_login_id, enrollment_no, gr_no, class_info_id, first_name,last_name ,parent_info_id, sem_info_id, batch_start_year, batch_end_year) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssiss", $studentUsername, $enrollmentNo, $grNo, $classInfoId, $firstName,$lastName, $parentInfoId, $semInfoId, $batchStartYear, $batchEndYear);
            $stmt->execute();
        }

        echo "Data insertion completed successfully!";
    } else {
        echo "Error: Only Excel files (.xlsx, .xls) are allowed.";
    }
} else {
    echo "Error: No file uploaded or an error occurred during upload.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Excel File</title>
</head>
<body>
    <form action="AddNewStudents.php" method="post" enctype="multipart/form-data">
        <label for="file">Choose Excel file:</label>
        <input type="file" name="file" id="file" accept=".xlsx, .xls" required>
        <button type="submit">Upload and Process</button>
    </form>
</body>
</html>
