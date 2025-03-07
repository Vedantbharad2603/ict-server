<?php

require_once __DIR__ . '/../services/ParentService.php';

function ParentLoginController($input) {
    if (!isset($input['username']) || !isset($input['password']) || !isset($input['device_token'])) {
        http_response_code(400); // Bad Request
        echo json_encode(['message' => 'Username and password required']);
        return;
    }

    // Extract username and password
    $username = $input['username'];
    $password = $input['password'];
    $device_token = $input['device_token'];

    // Call the service
    $response = ParentService($username, $password,$device_token);

    if ($response['status']) {
        echo json_encode($response['data']);
    } else {
        echo json_encode(['message' => $response['message']]);
    }
}

function ParentLogoutController($input) {
    if (!isset($input['username'])) {
        http_response_code(400); // Bad Request
        echo json_encode(['message' => 'Username and password required']);
        return;
    }

    // Extract username and password
    $username = $input['username'];

    // Call the service
    $response = ParentOutService($username);

    if ($response['status']) {
        echo json_encode($response['data']);
    } else {
        echo json_encode(['message' => $response['message']]);
    }
}

function GetFacultyContactController($input) {
    if (!isset($input['s_id'])) {
        http_response_code(400); // Bad Request
        echo json_encode(['message' => 'Student ID required']);
        return;
    }
    $studentId = $input['s_id'];

    $response = GetFacultyContactService($studentId);

    if ($response['status']) {
        echo json_encode($response['data']);
    } else {
        echo json_encode(['message' => $response['message']]);
    }
}

function GetStudentTimetableController($input) {
    if (!isset($input['s_id'])) {
        http_response_code(400); // Bad Request
        echo json_encode(['message' => 'Student ID required']);
        return;
    }
    $studentId = $input['s_id'];

    $response = GetStudentTimetableService($studentId);

    if ($response['status']) {
        echo json_encode($response['data']);
    } else {
        echo json_encode(['message' => $response['message']]);
    }
}
?>