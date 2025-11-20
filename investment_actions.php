<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit();
}
require_once 'config.php';
$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        $type = $_POST['type'] ?? '';
        $amount = floatval($_POST['amount'] ?? 0);
        $return_rate = floatval($_POST['return_rate'] ?? 0);
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? null;
        $status = $_POST['status'] ?? 'active';
        $created_at = date('Y-m-d H:i:s');

        if (empty($type) || empty($amount) || empty($return_rate) || empty($start_date) || empty($status)) {
            echo json_encode(["success" => false, "message" => "All fields except end date are required"]);
            exit();
        }

        $sql = "INSERT INTO investments (user_id, type, amount, return_rate, start_date, end_date, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isddssss", $user_id, $type, $amount, $return_rate, $start_date, $end_date, $status, $created_at);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Investment added successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to add investment"]);
        }
        break;

    case 'delete':
        $investment_id = intval($_POST['investment_id'] ?? 0);
        if ($investment_id <= 0) {
            echo json_encode(["success" => false, "message" => "Invalid investment ID"]);
            exit();
        }
        $sql = "DELETE FROM investments WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $investment_id, $user_id);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Investment deleted successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to delete investment"]);
        }
        break;

    default:
        echo json_encode(["success" => false, "message" => "Invalid action"]);
}
?> 