<?php
session_start();
require_once 'config.php';

// Error handling
try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in');
    }

    // Get user's transactions
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT * FROM transactions WHERE user_id = ? ORDER BY date DESC";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Get transaction statistics
    $sql = "SELECT 
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expenses
            FROM transactions 
            WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stats = $stmt->get_result()->fetch_assoc();

    $total_income = floatval($stats['total_income'] ?? 0);
    $total_expenses = floatval($stats['total_expenses'] ?? 0);
    $balance = $total_income - $total_expenses;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .summary {
            margin-bottom: 30px;
        }
        .summary-item {
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .amount {
            text-align: right;
        }
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Transaction History Report</h1>
    </div>

    <div class="summary">
        <h2>Summary</h2>
        <div class="summary-item">
            <strong>Total Income:</strong> ₹<?php echo number_format($total_income, 2); ?>
        </div>
        <div class="summary-item">
            <strong>Total Expenses:</strong> ₹<?php echo number_format($total_expenses, 2); ?>
        </div>
        <div class="summary-item">
            <strong>Net Balance:</strong> ₹<?php echo number_format($balance, 2); ?>
        </div>
    </div>

    <h2>Transaction History</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Category</th>
                <th>Amount</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $transaction): ?>
            <tr>
                <td><?php echo htmlspecialchars($transaction['date']); ?></td>
                <td><?php echo ucfirst(htmlspecialchars($transaction['type'])); ?></td>
                <td><?php echo htmlspecialchars($transaction['category']); ?></td>
                <td class="amount">₹<?php echo number_format($transaction['amount'], 2); ?></td>
                <td><?php echo htmlspecialchars($transaction['description']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()">Print Report</button>
    </div>

    <script>
        // Automatically trigger print dialog when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
<?php
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
    exit();
}
?> 