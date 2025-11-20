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
        $title = $_POST['title'] ?? '';
        $target_amount = floatval($_POST['target_amount'] ?? 0);
        $current_amount = floatval($_POST['current_amount'] ?? 0);
        $deadline = $_POST['deadline'] ?? '';
        $status = $_POST['status'] ?? 'active';
        $created_at = date('Y-m-d H:i:s');

        if (empty($title) || empty($target_amount) || empty($deadline) || empty($status)) {
            echo json_encode(["success" => false, "message" => "All fields except current amount are required"]);
            exit();
        }

        $sql = "INSERT INTO goals (user_id, title, target_amount, current_amount, deadline, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isddsss", $user_id, $title, $target_amount, $current_amount, $deadline, $status, $created_at);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Goal added successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to add goal"]);
        }
        break;

    case 'delete':
        $goal_id = intval($_POST['goal_id'] ?? 0);
        if ($goal_id <= 0) {
            echo json_encode(["success" => false, "message" => "Invalid goal ID"]);
            exit();
        }
        $sql = "DELETE FROM goals WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $goal_id, $user_id);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Goal deleted successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to delete goal"]);
        }
        break;

    default:
        echo json_encode(["success" => false, "message" => "Invalid action"]);
}
?> 