<?php
// api/config.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

// ── Identifiants Railway MySQL ─────────────────────────────────
define('DB_HOST', 'viaduct.proxy.rlwy.net');
define('DB_PORT', '10362');
define('DB_USER', 'root');
define('DB_PASS', 'WrdxwEpAeYtOpzwAmnPBluTUjdbcOLGk');
define('DB_NAME', 'railway');

function getDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, (int)DB_PORT);
    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(['error' => 'Connexion BDD échouée: ' . $conn->connect_error]);
        exit();
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

function respond($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}
?>
