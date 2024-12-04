<?php
header("Content-Type: application/json");
require 'utils/ApiKeyValidator.php';
require 'routes/AppVersionRoutes.php';
require 'routes/ParentRoutes.php';
require 'routes/FacultyRoutes.php';
require 'routes/AttendanceRoutes.php';
require 'routes/PasswordRoutes.php';
require 'routes/ZoomRoutes.php';
require 'routes/HolidayRoutes.php';
require 'routes/ExamRoutes.php';

// Validate API Key
validateApiKey(); // Check the API key before processing the request

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Extract the endpoint path after "index.php/"
$path = explode('/', parse_url($uri, PHP_URL_PATH));
$endpoint = isset($path[4]) ? $path[4] : null; // First part after "index.php/"
$subpath = isset($path[5]) ? $path[5] : null; // Second part after "index.php/<endpoint>/"
switch ($endpoint) {
    case 'AppVersion':
        AppVersionRoutes($method, $subpath);
        break;
    case 'Parent':
        ParentRoutes($method, $subpath);
        break;
    case 'Faculty':
        FacultyRoutes($method, $subpath);
        break;
    case 'Attendance':
        AttendanceRoutes($method, $subpath);
        break;
    case 'Password':
        PasswordRoutes($method, $subpath);
        break;
    case 'ZoomLink':
        ZooomRoutes($method, $subpath);
        break;
    case 'Holiday':
        HolidayRoutes($method, $subpath);
        break;
    case 'Exam':
        ExamRoutes($method, $subpath);
        break;

    default:
        http_response_code(404);
        echo json_encode(['message' => 'Invalid endpoint11']);
        break;
}
?>
