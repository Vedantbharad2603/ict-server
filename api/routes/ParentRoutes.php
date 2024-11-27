<?php

require_once __DIR__ . '/../controllers/ParentController.php';

function ParentRoutes($method, $subpath) {
    switch ($subpath) {
        case 'login': // Handle "Parent/Login"
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                ParentLoginController($input);
            } else {
                http_response_code(405); // Method Not Allowed
                echo json_encode(['message' => 'Method not allowed']);
            }
            break;
        case 'getFacultyContact': // Handle "Parent/Login"
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                GetFacultyContactController($input);
            } else {
                http_response_code(405); // Method Not Allowed
                echo json_encode(['message' => 'Method not allowed']);
            }
            break;
        default:
            http_response_code(404); // Not Found
            echo json_encode(['message' => 'Invalid Parent API endpoint']);
            break;
    }
}
