<?php

require_once __DIR__ . '/../services/ParentService.php';

function ParentLoginController($input) {
    if (!isset($input['username']) || !isset($input['password'])) {
        http_response_code(400); // Bad Request
        echo json_encode(['message' => 'Username and password required']);
        return;
    }

    // Extract username and password
    $username = $input['username'];
    $password = $input['password'];

    // Call the service
    $response = ParentService($username, $password);

    if ($response['status']) {
        echo json_encode($response['data']);
    } else {
        echo json_encode(['message' => $response['message']]);
    }
}
