<?php
require_once __DIR__ . '/../db/db_connection.php';
require __DIR__ . '/../vendor/autoload.php';

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

function sendFCMNotification($username, $title, $body) {
    global $conn;

    // Fetch device_token from user_login table
    $stmt = $conn->prepare("SELECT device_token FROM user_login WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($deviceToken);
    $stmt->fetch();
    $stmt->close();

    // If no device_token found, return success without sending notification
    if (!$deviceToken) {
        return json_encode(['success' => true, 'message' => 'No device token found, notification skipped']);
    }

    $serviceAccountPath = __DIR__ . '/firebase_credentials.json';

    $factory = (new Factory)->withServiceAccount($serviceAccountPath);
    $messaging = $factory->createMessaging();

    $notification = Notification::create($title, $body);
    $message = CloudMessage::withTarget('token', $deviceToken)
        ->withNotification($notification)
        ->withData(['extra_data' => 'Some Extra Data']);

    try {
        $response = $messaging->send($message);
        return json_encode(['success' => true, 'response' => $response]);
    } catch (\Exception $e) {
        return json_encode(['success' => false, 'message' => 'Notification not sent', 'error' => $e->getMessage()]);
    }
}
?>
