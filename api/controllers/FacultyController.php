<?php

require_once __DIR__ . '/../services/FacultyService.php';

function FacultyLoginController($input) {
    if (!isset($input['username']) || !isset($input['password'])) {
        echo json_encode(['message' => 'Username and password required']);
        return;
    }

    $username = $input['username'];
    $password = $input['password'];

    // Call the service
    $response = FacultyLoginService($username, $password);

    if ($response['status']) {
        echo json_encode($response['data']);
    } else {
        http_response_code(401);
        echo json_encode(['message' => $response['message']]);
    }
}