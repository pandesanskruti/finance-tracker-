<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
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
        $category = $_POST['category'] ?? '';
        $description = $_POST['description'] ?? '';
        $date = $_POST['date'] ?? date('Y-m-d');
        $month = date('n', strtotime($date));
        $year = date('Y', strtotime($date));

        if (empty($type) || empty($amount) || empty($category) || empty($description)) {
            echo json_encode(["success" => false, "message" => "All fields are required"]);
            exit();
        }

        $sql = "INSERT INTO transactions (user_id, type, amount, category, description, date) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isdsss", $user_id, $type, $amount, $category, $description, $date);

        if ($stmt->execute()) {
            // Only update budget for expenses
            if ($type === 'expense') {
                // Find the budget for this user/category/month/year
                $sql = "SELECT id FROM budgets WHERE user_id = ? AND category = ? AND month = ? AND year = ?";
                $budget_stmt = $conn->prepare($sql);
                $budget_stmt->bind_param("issi", $user_id, $category, $month, $year);
                $budget_stmt->execute();
                $budget = $budget_stmt->get_result()->fetch_assoc();

                if ($budget) {
                    // Update spent amount for expenses
                    $sql = "UPDATE budgets SET spent = spent + ? WHERE id = ?";
                    $update_stmt = $conn->prepare($sql);
                    $update_stmt->bind_param("di", $amount, $budget['id']);
                    $update_stmt->execute();
                }
            }
            echo json_encode(["success" => true, "message" => "Transaction added successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to add transaction: " . $conn->error]);
        }
        exit();

    case 'delete':
        $transaction_id = intval($_POST['transaction_id'] ?? 0);

        if ($transaction_id <= 0) {
            echo json_encode(["success" => false, "message" => "Invalid transaction ID"]);
            exit();
        }

        // Get transaction details before deleting
        $sql = "SELECT type, amount, category, date FROM transactions WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $transaction_id, $user_id);
        $stmt->execute();
        $transaction = $stmt->get_result()->fetch_assoc();

        if ($transaction) {
            // Only update budget for expenses
            if ($transaction['type'] === 'expense') {
                $amount = $transaction['amount'];
                $category = $transaction['category'];
                $date = $transaction['date'];
                $month = date('n', strtotime($date));
                $year = date('Y', strtotime($date));

                // Subtract the expense from the budget
                $sql = "UPDATE budgets SET spent = spent - ? WHERE user_id = ? AND category = ? AND month = ? AND year = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("dissi", $amount, $user_id, $category, $month, $year);
                $stmt->execute();
            }

            // Delete the transaction
            $sql = "DELETE FROM transactions WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $transaction_id, $user_id);

            if ($stmt->execute()) {
                header("Location: transactions.php?success=delete");
                exit();
            } else {
                header("Location: transactions.php?error=delete");
                exit();
            }
        } else {
            header("Location: transactions.php?error=notfound");
            exit();
        }

    case 'update':
        $transaction_id = intval($_POST['transaction_id'] ?? 0);
        $type = $_POST['type'] ?? '';
        $amount = floatval($_POST['amount'] ?? 0);
        $category = $_POST['category'] ?? '';
        $description = $_POST['description'] ?? '';
        $date = $_POST['date'] ?? date('Y-m-d');
        $month = date('n', strtotime($date));
        $year = date('Y', strtotime($date));

        if ($transaction_id <= 0 || empty($type) || empty($amount) || empty($category) || empty($description)) {
            echo json_encode(["success" => false, "message" => "All fields are required"]);
            exit();
        }

        // Get the old transaction for budget adjustment
        $sql = "SELECT * FROM transactions WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $transaction_id, $user_id);
        $stmt->execute();
        $old = $stmt->get_result()->fetch_assoc();

        if (!$old) {
            echo json_encode(["success" => false, "message" => "Transaction not found"]);
            exit();
        }

        // Handle budget updates for expenses only
        if ($old['type'] === 'expense') {
            // Reverse the old expense's effect on the budget
            $old_month = date('n', strtotime($old['date']));
            $old_year = date('Y', strtotime($old['date']));
            $sql = "UPDATE budgets SET spent = spent - ? WHERE user_id = ? AND category = ? AND month = ? AND year = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("dissi", $old['amount'], $user_id, $old['category'], $old_month, $old_year);
            $stmt->execute();
        }

        // Apply the new transaction's effect on the budget (only for expenses)
        if ($type === 'expense') {
            $sql = "UPDATE budgets SET spent = spent + ? WHERE user_id = ? AND category = ? AND month = ? AND year = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("dissi", $amount, $user_id, $category, $month, $year);
            $stmt->execute();
        }

        // Update the transaction
        $sql = "UPDATE transactions SET type = ?, amount = ?, category = ?, description = ?, date = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdsssii", $type, $amount, $category, $description, $date, $transaction_id, $user_id);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Transaction updated successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update transaction: " . $conn->error]);
        }
        exit();

    default:
        echo json_encode(["success" => false, "message" => "Invalid action"]);
        exit();
}
?> 