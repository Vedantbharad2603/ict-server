<?php

require_once __DIR__ . '/../db/db_connection.php';

function ParentService($username, $password) {
    global $conn; // Use global DB connection

    // Sanitize input
    $username = $conn->real_escape_string($username);

    // Prepare the statement to get the hashed password
    $stmt = $conn->prepare("SELECT password FROM user_login WHERE username = ?");
    if (!$stmt) {
        return ['status' => false, 'message' => 'Failed to prepare the statement'];
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($hashedPassword);
    $stmt->fetch();
    $stmt->close();

    // Check if a hashed password was found
    if (!$hashedPassword || !password_verify($password, $hashedPassword)) {
        http_response_code(401); // Unauthorized
        return ['status' => false, 'message' => 'Invalid username or password'];
    }

    // Proceed to call the stored procedure
    $stmt = $conn->prepare("CALL LoginParent(?)");
    if (!$stmt) {
        return ['status' => false, 'message' => 'Failed to prepare the stored procedure'];
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    $conn->close();

    if ($result && $result->num_rows > 0) {
        $user_data = $result->fetch_assoc();

        // Decode the JSON fields
        $parent_details = json_decode($user_data['parent_details'], true);
        $student_details = json_decode($user_data['student_details'], true);
        $class_details = json_decode($user_data['class_details'], true);

        $full_details = [
            'parent_details' => $parent_details,
            'student_details' => $student_details,
            'class_details' => $class_details,
        ];

        $stmt->close();
        return ['status' => true, 'data' => $full_details];
    }

    $stmt->close();
    http_response_code(401); // Unauthorized
    return ['status' => false, 'message' => 'Invalid username or password'];
}
