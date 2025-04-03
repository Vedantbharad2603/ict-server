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
?>