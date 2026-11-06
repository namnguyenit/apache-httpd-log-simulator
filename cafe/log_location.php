<?php
// log_location.php
// Nhận vị trí thực tế từ client và lưu vào session để enrich log truy cập
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['latitude']) && isset($data['longitude'])) {
        $_SESSION['real_lat'] = $data['latitude'];
        $_SESSION['real_lon'] = $data['longitude'];
        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
