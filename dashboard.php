<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config.php';

$user_id = $_SESSION['user_id'];
$current_month = date('n');
$current_year = date('Y');

// Get transaction statistics for current month
$sql = "SELECT 
        SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
        SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expenses
        FROM transactions 
        WHERE user_id = ? 
        AND MONTH(date) = ? 
        AND YEAR(date) = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("iii", $user_id, $current_month, $current_year);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

$total_income = floatval($stats['total_income'] ?? 0);
$total_expenses = floatval($stats['total_expenses'] ?? 0);
$balance = $total_income - $total_expenses;
$monthly_savings = $balance;

// Get recent transactions
$sql = "SELECT * FROM transactions WHERE user_id = ? ORDER BY date DESC LIMIT 5";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get active goals
$sql = "SELECT * FROM goals WHERE user_id = ? AND status = 'active' ORDER BY deadline ASC LIMIT 3";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$active_goals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get budget status for current month
$sql = "SELECT category, amount, 
        (SELECT COALESCE(SUM(amount), 0) 
         FROM transactions 
         WHERE user_id = b.user_id 
         AND category = b.category 
         AND type = 'expense'
         AND MONTH(date) = b.month 
         AND YEAR(date) = b.year) as spent
        FROM budgets b 
        WHERE user_id = ? 
        AND month = ? 
        AND year = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("iii", $user_id, $current_month, $current_year);
$stmt->execute();
$budgets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate overall budget progress
$total_budget = 0;
$total_spent = 0;
foreach ($budgets as $budget) {
    $total_budget += $budget['amount'];
    $total_spent += $budget['spent'];
}
$budget_progress = $total_budget > 0 ? ($total_spent / $total_budget) * 100 : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Personal Finance Tracker</title>
    <link rel="icon" type="image/x-icon" href="./favicon_io/android-chrome-512x512.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f9fafb;
        margin: 0;
        padding: 0;
    }
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem;
    }
    .card {
        background: #fff;
        border-radius: 0.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .card-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 1rem;
    }
    .chart-container {
        height: 300px;
        margin-bottom: 1.5rem;
    }
    .dashboard-section { padding: 3rem 0; }
    .dashboard-stats {
        display: flex;
        gap: 2rem;
        margin-bottom: 2.5rem;
        flex-wrap: wrap;
        justify-content: center;
    }
    .stat-card {
        background: #fff;
        border-radius: 1.2rem;
        box-shadow: 0 4px 16px rgba(37,99,235,0.08);
        padding: 2rem 1.5rem;
        flex: 1 1 220px;
        min-width: 220px;
        display: flex;
        align-items: center;
        gap: 1.2rem;
        transition: transform 0.2s;
    }
    .stat-card:hover { transform: translateY(-4px) scale(1.03); }
    .stat-icon {
        font-size: 2.2rem;
        color: #3b82f6;
        background: #e0e7ff;
        border-radius: 50%;
        width: 56px; height: 56px;
        display: flex; align-items: center; justify-content: center;
    }
    .stat-icon.income { background: #d1fae5; color: #10b981; }
    .stat-icon.expense { background: #fee2e2; color: #ef4444; }
    .stat-icon.savings { background: #fef3c7; color: #f59e0b; }
    .stat-info h3 { margin: 0; font-size: 1.3rem; color: #2563eb; }
    .stat-info p { font-size: 1.1rem; color: #64748b; margin: 0.2rem 0 0 0; }
    .dashboard-charts {
        display: flex;
        gap: 2rem;
        flex-wrap: wrap;
        margin-bottom: 2rem;
        justify-content: center;
    }
    .chart-container {
        background: #fff;
        border-radius: 1.2rem;
        box-shadow: 0 4px 16px rgba(37,99,235,0.08);
        padding: 1.5rem;
        flex: 1 1 350px;
        min-width: 320px;
        max-width: 500px;
    }
    .chart-container h3 { color: #2563eb; margin-bottom: 1rem; }
    @media (max-width: 900px) {
        .dashboard-stats, .dashboard-charts { flex-direction: column; gap: 1.5rem; }
    }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="dashboard-section">
        <div class="container">
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-wallet"></i></div>
                    <div class="stat-info">
                        <h3 id="total-balance">$<?php echo number_format($balance, 2); ?></h3>
                        <p>Total Balance</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon income"><i class="fas fa-arrow-up"></i></div>
                    <div class="stat-info">
                        <h3 id="total-income">$<?php echo number_format($total_income, 2); ?></h3>
                        <p>Total Income</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon expense"><i class="fas fa-arrow-down"></i></div>
                    <div class="stat-info">
                        <h3 id="total-expenses">$<?php echo number_format($total_expenses, 2); ?></h3>
                        <p>Total Expenses</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon savings"><i class="fas fa-piggy-bank"></i></div>
                    <div class="stat-info">
                        <h3 id="monthly-savings">$<?php echo number_format($monthly_savings, 2); ?></h3>
                        <p>Monthly Savings</p>
                    </div>
                </div>
            </div>
            <div class="dashboard-charts">
                <div class="chart-container">
                    <h3>Income vs Expenses</h3>
                    <canvas id="incomeExpenseChart"></canvas>
                </div>
                <div class="chart-container">
                    <h3>Budget Overview</h3>
                    <canvas id="budgetChart"></canvas>
                </div>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>
    <script>
    // Income vs Expenses Chart
    new Chart(document.getElementById('incomeExpenseChart'), {
        type: 'bar',
        data: {
            labels: ['Income', 'Expenses'],
            datasets: [{
                data: [<?php echo $total_income; ?>, <?php echo $total_expenses; ?>],
                backgroundColor: ['#10b981', '#ef4444']
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } }
        }
    });
    // Budget Chart
    new Chart(document.getElementById('budgetChart'), {
        type: 'doughnut',
        data: {
            labels: [<?php echo implode(',', array_map(function($b){return "'".addslashes($b['category'])."'";}, $budgets)); ?>],
            datasets: [{
                data: [<?php echo implode(',', array_map(function($b){return $b['spent'];}, $budgets)); ?>],
                backgroundColor: ['#3b82f6','#10b981','#f59e0b','#ef4444','#6366f1','#f472b6']
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'right' } }
        }
    });
    </script>
</body>
</html> 