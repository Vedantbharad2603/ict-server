<?php

require_once __DIR__ . '/../controllers/FacultyController.php';

function FacultyRoutes($method, $subpath) {
    switch ($subpath) {
        case 'login': // Handle "Faculty/login"
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                FacultyLoginController($input);
            } else {
                http_response_code(405); // Method Not Allowed
                echo json_encode(['message' => 'Method not allowed']);
            }
            break;

        default:
            http_response_code(404); // Not Found
            echo json_encode(['message' => 'Invalid Faculty API endpoint']);
            break;
    }
}
