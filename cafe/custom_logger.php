<?php
// custom_logger.php (Phiên bản 3 - Làm phẳng dữ liệu tại nguồn)

// Định nghĩa đường dẫn đến tệp nhật ký mà chúng ta sẽ ghi vào
define('ENRICHED_LOG_FILE', '/var/log/www/access/enriched_access.log');

/**
 * Gọi một API bên ngoài để lấy thông tin vị trí từ địa chỉ IP.
 * @param string $ipAddress Địa chỉ IP của người dùng.
 * @return array Mảng chứa thông tin vị trí.
 */
function get_geo_info_from_ip($ipAddress) {
    if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        $url = "http://ip-api.com/json/{$ipAddress}?fields=status,countryCode,regionName,city,lat,lon";
        $response = @file_get_contents($url); 
        if ($response) {
            $data = json_decode($response, true);
            if ($data && $data['status'] == 'success') {
                return [
                    "country" => $data['countryCode'] ?? '-',
                    "region"  => $data['regionName'] ?? '-',
                    "city"    => $data['city'] ?? '-',
                    "lat"     => (string)($data['lat'] ?? '-'),
                    "lon"     => (string)($data['lon'] ?? '-')
                ];
            }
        }
    }
    return ["country" => "-", "region" => "-", "city" => "-", "lat" => "-", "lon" => "-"];
}

/**
 * Ghi một bản ghi nhật ký truy cập thông thường (không có đơn hàng).
 */
function write_enriched_log() {
    // Thu thập các thông tin chung
    $remoteIP  = $_SERVER['REMOTE_ADDR'] ?? '-';
    $requestURI = $_SERVER['REQUEST_URI'] ?? '-';
    $requestParts = explode('?', $requestURI);
    $requestPath = $requestParts[0];
    $queryString = $_SERVER['QUERY_STRING'] ?? '';
    $method    = $_SERVER['REQUEST_METHOD'] ?? '-';
    $status    = http_response_code();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '-';
    $referer   = $_SERVER['HTTP_REFERER'] ?? '-';
    $processId = (string)getmypid();
    $fileName  = $_SERVER['SCRIPT_FILENAME'] ?? '-';
    $host      = $_SERVER['HTTP_HOST'] ?? '-';
    $geoInfo = get_geo_info_from_ip($remoteIP);

    // Xây dựng đối tượng nhật ký
    $logEntry = [
        "time"      => gmdate("Y-m-d H:i:s"),
        "process"   => $processId,
        "filename"  => $fileName,
        "remoteIP"  => $remoteIP,
        "host"      => $host,
        "request"   => $requestPath,
        "query"     => $queryString,
        "method"    => $method,
        "status"    => (string)$status,
        "userAgent" => $userAgent,
        "referer"   => $referer,
        "country"   => $geoInfo['country'],
        "region"    => $geoInfo['region'],
        "city"      => $geoInfo['city'],
        "lat"       => $geoInfo['lat'],
        "lon"       => $geoInfo['lon']
    ];

    // Ghi vào tệp nhật ký
    @file_put_contents(ENRICHED_LOG_FILE, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
}

/**
 * Ghi nhật ký chi tiết cho MỖI SẢN PHẨM trong một đơn hàng.
 */
function write_enriched_log_with_order($orderNumber, $totalAmount, $orderProducts) {
    // Thu thập các thông tin chung, giống như hàm trên
    $remoteIP  = $_SERVER['REMOTE_ADDR'] ?? '-';
    $requestPath = explode('?', $_SERVER['REQUEST_URI'] ?? '-')[0];
    $method    = $_SERVER['REQUEST_METHOD'] ?? '-';
    $status    = http_response_code();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '-';
    $referer   = $_SERVER['HTTP_REFERER'] ?? '-';
    $processId = (string)getmypid();
    $fileName  = $_SERVER['SCRIPT_FILENAME'] ?? '-';
    $host      = $_SERVER['HTTP_HOST'] ?? '-';
    $geoInfo = get_geo_info_from_ip($remoteIP);

    // LẶP QUA TỪNG SẢN PHẨM và ghi một dòng log riêng
    if (!empty($orderProducts)) {
        foreach ($orderProducts as $product) {
            $logEntry = [
                "time"          => gmdate("Y-m-d H:i:s"),
                "process"       => $processId,
                "filename"      => $fileName,
                "remoteIP"      => $remoteIP,
                "host"          => $host,
                "request"       => $requestPath,
                "method"        => $method,
                "status"        => (string)$status,
                "userAgent"     => $userAgent,
                "referer"       => $referer,
                "country"       => $geoInfo['country'],
                "region"        => $geoInfo['region'],
                "city"          => $geoInfo['city'],
                "lat"           => $geoInfo['lat'],
                "lon"           => $geoInfo['lon'],
                // Thông tin đơn hàng và sản phẩm
                "order_id"      => (string)$orderNumber,
                "order_total"   => (float)$totalAmount,
                "product_id"    => isset($product['product_id']) ? (int)$product['product_id'] : 0,
                "product_name"  => $product['product_name'] ?? '-',
                "quantity"      => isset($product['quantity']) ? (int)$product['quantity'] : 0,
                "price"         => isset($product['price']) ? (float)$product['price'] : 0,
                "amount"        => isset($product['amount']) ? (float)$product['amount'] : 0,
                "type"          => $product['type'] ?? '-'
            ];
            // Ghi vào tệp
            @file_put_contents(ENRICHED_LOG_FILE, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
        }
    }
}
?>
