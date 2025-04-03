<?php

require_once __DIR__ . '/../db/db_connection.php';

function RecentlyPlacedService($batchId) {
    global $conn;
    try {
        $stmt = $conn->prepare("CALL recentlyPlacedStudents(?)");
        $stmt->bind_param("s",$batchId);
        $stmt->execute();
        $result = $stmt->get_result();
        $studentsList = [];
        while ($row = $result->fetch_assoc()) {
            $studentsList[] = $row;
        }

        return ['status' => true, 'data' => $studentsList];
    } catch (Exception $e) {
        return ['status' => false, 'message' => $e->getMessage()];
    }
}

function GetCompanyListService() {
    global $conn; // Assuming $conn is your database connection object

    try {
        $stmt = $conn->prepare("SELECT * FROM company_info order by company_name");
        $stmt->execute();
        $result = $stmt->get_result();
        $companyList = [];
        while ($row = $result->fetch_assoc()) {
            $companyList[] = $row;
        }
        return ['status' => true, 'data' => $companyList];
    } catch (Exception $e) {
        return ['status' => false, 'message' => $e->getMessage()];
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
}

function GetCampusDriveStudentListService($studentId,$batchId) {
    global $conn;

    try {
        $stmt = $conn->prepare("CALL GetCampusDriveByStudent(?,?)");
        $stmt->bind_param("ii",$studentId,$batchId);
        $stmt->execute();
        $result = $stmt->get_result();
        $campusDriveList = [];
        while ($row = $result->fetch_assoc()) {
            $campusDriveList[] = $row;
        }
        return ['status' => true, 'data' => $campusDriveList];
    } catch (Exception $e) {
        return ['status' => false, 'message' => $e->getMessage()];
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
}

?>