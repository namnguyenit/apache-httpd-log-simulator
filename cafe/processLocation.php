<?php
// processLocation.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['latitude']) && isset($data['longitude'])) {
        $latitude = $data['latitude'];
        $longitude = $data['longitude'];
        // Ở đây bạn có thể lưu vào file, database, hoặc xử lý theo ý muốn
        // Ví dụ: ghi log vào file
        $log = date('Y-m-d H:i:s') . " - Lat: $latitude, Lng: $longitude\n";
        file_put_contents('user_locations.log', $log, FILE_APPEND);
        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
