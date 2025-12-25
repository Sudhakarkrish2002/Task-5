


<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// 1. Handle Preflight for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

// --- DATABASE CONNECTION ---
$host = "localhost";
$db_name = "u247633921_iot"; // CHANGE THIS
$db_user = "u247633921_iot"; // CHANGE THIS
$db_pass = "Arun@811001"; // CHANGE THIS

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

// Helper: Get JSON Body & Action
$data = json_decode(file_get_contents("php://input"), true);
$action = $_GET['action'] ?? '';

// ==========================================================
// ROUTES
// ==========================================================

if ($action === 'list_dashboards' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare("SELECT id, name, updated_at FROM dashboards");
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

elseif ($action === 'save_dashboard' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $data['id'];
    $name = $data['name'];
    $json = json_encode($data['data']);

    // Check if dashboard exists
    $stmt = $pdo->prepare("SELECT id FROM dashboards WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("UPDATE dashboards SET name=?, data=? WHERE id=?");
        $stmt->execute([$name, $json, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO dashboards (id, name, data) VALUES (?, ?, ?)");
        $stmt->execute([$id, $name, $json]);
    }
    echo json_encode(["message" => "Saved"]);
}

elseif ($action === 'get_dashboard' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM dashboards WHERE id = ?");
    $stmt->execute([$id]);
    $dash = $stmt->fetch(PDO::FETCH_ASSOC);
    if($dash) echo json_encode($dash);
    else { http_response_code(404); echo json_encode(["error" => "Not found"]); }
}

elseif ($action === 'delete_dashboard' && $_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM dashboards WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(["message" => "Deleted"]);
}
?>