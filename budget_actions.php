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
    case 'add_budget':
        $category = $_POST['category'] ?? '';
        $amount = floatval($_POST['amount'] ?? 0);
        $month = date('n');
        $year = date('Y');

        if (empty($category) || empty($amount)) {
            echo json_encode(["success" => false, "message" => "All fields are required"]);
            exit();
        }

        // Check if budget already exists for this user, category, month, and year
        $sql = "SELECT id FROM budgets WHERE user_id = ? AND category = ? AND month = ? AND year = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo json_encode(["success" => false, "message" => "Prepare failed: " . $conn->error]);
            exit();
        }
        $stmt->bind_param("isii", $user_id, $category, $month, $year);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();

        if ($existing) {
            echo json_encode(["success" => false, "message" => "Budget already exists for this category this month."]);
            exit();
        }

        $sql = "INSERT INTO budgets (user_id, category, amount, month, year, spent) VALUES (?, ?, ?, ?, ?, 0)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo json_encode(["success" => false, "message" => "Prepare failed: " . $conn->error]);
            exit();
        }
        $stmt->bind_param("isdii", $user_id, $category, $amount, $month, $year);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Budget added successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to add budget: " . $conn->error]);
        }
        break;

    case 'update':
        $budget_id = intval($_POST['budget_id'] ?? 0);
        $amount = floatval($_POST['amount'] ?? 0);

        if ($budget_id <= 0) {
            echo json_encode(["success" => false, "message" => "Invalid budget ID"]);
            exit();
        }

        $sql = "UPDATE budgets SET amount = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("dii", $amount, $budget_id, $user_id);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Budget updated successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update budget"]);
        }
        break;

    case 'delete':
        $budget_id = intval($_POST['budget_id'] ?? 0);

        if ($budget_id <= 0) {
            echo json_encode(["success" => false, "message" => "Invalid budget ID"]);
            exit();
        }

        $sql = "DELETE FROM budgets WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $budget_id, $user_id);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Budget deleted successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to delete budget"]);
        }
        break;

    default:
        echo json_encode(["success" => false, "message" => "Invalid action"]);
}
?> 