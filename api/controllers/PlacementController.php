<?php

require_once __DIR__ . '/../services/PlacementService.php';

function RecentlyPlaced($input) {
    if (!isset($input['batch_id'])) {
        http_response_code(400); // Bad Request
        echo json_encode(['message' => 'Batch id is required']);
        return;
    }
    $batchId = $input['batch_id'];
    $response = RecentlyPlacedService($batchId);

    if ($response['status']) {
        echo json_encode($response['data']);
    } else {
        echo json_encode(['message' => $response['message']]);
    }
}

function CompanyList() {
    $response = GetCompanyListService();
    if ($response['status']) {
        echo json_encode($response['data']);
    } else {
        echo json_encode(['message' => $response['message']]);
    }
}

function CampusDriveStudentList($input) {
    if (!isset($input['batch_id'])||!isset($input['student_id'])) {
        http_response_code(400); // Bad Request
        echo json_encode(['message' => 'Batch id is required and Student id is required']);
        return;
    }
    $batchId = $input['batch_id'];
    $studentId = $input['student_id'];
    $response = GetCampusDriveStudentListService($studentId,$batchId);

    if ($response['status']) {
        echo json_encode($response['data']);
    } else {
        echo json_encode(['message' => $response['message']]);
    }
}

function StatusUpdateCampusDrive($input) {
    if (!isset($input['student_id']) || !isset($input['campus_drive_id']) || !isset($input['status'])) {
        http_response_code(400); // Bad Request
        echo json_encode(['message' => 'Student id, Campus drive id, and Status are required']);
        return;
    }

    $studentId = $input['student_id'];
    $driveId = $input['campus_drive_id'];
    $status = $input['status'];

    $response = StatusUpdateCampusDriveService($studentId, $driveId, $status);

    if ($response['status']) {
        http_response_code(200);
        echo json_encode(['message' => $response['message']]);
    } else {
        http_response_code(500);
        echo json_encode(['message' => $response['message']]);
    }
}


?>