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

// Get all budgets for the current month
$sql = "SELECT * FROM budgets WHERE user_id = ? AND month = ? AND year = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user_id, $current_month, $current_year);
$stmt->execute();
$budgets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get monthly spending data for the last 6 months
$sql = "SELECT 
            DATE_FORMAT(date, '%Y-%m') as month_year,
            SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expenses
        FROM transactions 
        WHERE user_id = ? 
        AND date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(date, '%Y-%m')
        ORDER BY month_year ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$monthly_spending = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get category-wise spending for the current month
$sql = "SELECT 
            category,
            SUM(amount) as total_spent
        FROM transactions 
        WHERE user_id = ? 
        AND type = 'expense'
        AND MONTH(date) = ? 
        AND YEAR(date) = ?
        GROUP BY category";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user_id, $current_month, $current_year);
    $stmt->execute();
$category_spending = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate total budget and spent
$total_budget = 0;
$total_spent = 0;
foreach ($budgets as $budget) {
    $total_budget += $budget['amount'];
    $total_spent += $budget['spent'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget - Personal Finance Tracker</title>
    <link rel="icon" type="image/x-icon" href="./favicon_io/android-chrome-512x512.png" />
    <link rel="stylesheet" href="style.css">
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
    .budget-section {
        padding: 2rem 0;
    }
    .budget-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-bottom: 2.5rem;
    }
    .budget-form-wrapper {
        display: flex;
        justify-content: center;
        margin-bottom: 2.5rem;
    }
    .budget-form-card {
        background: #fff;
        border-radius: 1.2rem;
        box-shadow: 0 2px 12px rgba(37,99,235,0.08);
        padding: 2rem 2.5rem;
        min-width: 320px;
        max-width: 400px;
        border: 1px solid #f1f5f9;
    }
    .budget-form-card h2 {
        color: #1e293b;
        font-size: 1.4rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        text-align: left;
    }
    .form-group {
        margin-bottom: 1.2rem;
    }
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #1e293b;
    }
    .form-control {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        font-size: 1rem;
        background: #f9fafb;
    }
    .btn, .btn-primary {
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 0.375rem;
        padding: 0.5rem 1rem;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.2s;
        font-weight: 600;
        width: 100%;
    }
    .btn:hover, .btn-primary:hover {
        background: #1e40af;
    }
    .budget-list-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1.5rem;
        margin-left: 0.5rem;
    }
    .budget-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 1.5rem;
    }
    .budget-card {
        background: #fff;
        border-radius: 1.2rem;
        box-shadow: 0 2px 12px rgba(37,99,235,0.08);
        padding: 1.2rem 1.2rem 1.5rem 1.2rem;
        border: 1px solid #f1f5f9;
        position: relative;
        margin-top: 0.5rem;
    }
    .budget-card form {
        margin: 0;
        padding: 0;
    }
    .budget-card .budget-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #2563eb;
        margin-bottom: 0.7rem;
    }
    .budget-progress-bar {
        width: 100%;
        height: 8px;
        background: #e0e7ff;
        border-radius: 6px;
        margin: 0.7rem 0 0.5rem 0;
        overflow: hidden;
        position: relative;
    }
    .budget-progress {
        height: 100%;
        background: #2563eb;
        border-radius: 6px;
        transition: width 0.4s;
    }
    .budget-progress.warning {
        background: #f59e0b;
    }
    .budget-progress.danger {
        background: #ef4444;
    }
    .budget-amounts {
        display: flex;
        justify-content: space-between;
        font-size: 0.98rem;
        color: #64748b;
        margin-bottom: 0.2rem;
    }
    .budget-percent {
        font-size: 0.98rem;
        color: #2563eb;
        font-weight: 600;
        text-align: right;
    }
    .budget-percent.warning {
        color: #f59e0b;
    }
    .budget-percent.danger {
        color: #ef4444;
    }
    .budget-remaining {
        font-size: 0.9rem;
        color: #64748b;
        margin-top: 0.3rem;
    }
    @media (max-width: 900px) {
        .budget-layout {
            grid-template-columns: 1fr;
        }
        .budget-form-card { max-width: 100%; }
    }
    .budget-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        align-items: center;
        margin-bottom: 1rem;
        position: absolute;
        top: 1rem;
        right: 1rem;
    }
    .edit-btn, .delete-btn {
        background: none;
        border: none;
        font-size: 1.2rem;
        cursor: pointer;
        padding: 0.3rem;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 6px;
    }
    .edit-btn {
        color: #2563eb;
        background: rgba(37, 99, 235, 0.1);
    }
    .edit-btn:hover {
        color: #1e40af;
        background: rgba(37, 99, 235, 0.2);
    }
    .delete-btn {
        color: #ef4444;
        background: rgba(239, 68, 68, 0.1);
    }
    .delete-btn:hover {
        color: #b91c1c;
        background: rgba(239, 68, 68, 0.2);
    }
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0; top: 0; width: 100vw; height: 100vh;
        background: rgba(30,41,59,0.18);
        align-items: center;
        justify-content: center;
    }
    .modal-content {
        background: #fff;
        border-radius: 1.2rem;
        box-shadow: 0 4px 24px rgba(37,99,235,0.12);
        padding: 2rem 2rem 1.5rem 2rem;
        min-width: 320px;
        max-width: 400px;
        position: relative;
        margin: auto;
    }
    .modal-content h2 {
        color: #2563eb;
        margin-bottom: 1.2rem;
        font-size: 1.3rem;
        font-weight: 700;
    }
    .modal .close {
        position: absolute;
        top: 1.2rem;
        right: 1.2rem;
        font-size: 1.3rem;
        color: #64748b;
        cursor: pointer;
        font-weight: 700;
    }
    .modal .close:hover {
        color: #ef4444;
    }
    .chart-container {
        background: #fff;
        border-radius: 1.2rem;
        box-shadow: 0 4px 20px rgba(37,99,235,0.08);
        padding: 2rem;
        margin-bottom: 2rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .chart-container:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(37,99,235,0.12);
    }
    .chart-container h2 {
        color: #1e293b;
        font-size: 1.4rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        padding-bottom: 0.8rem;
        border-bottom: 2px solid #e0e7ff;
    }
    .chart-row {
        display: flex;
        gap: 2rem;
        margin-bottom: 2rem;
    }
    .chart-col {
        flex: 1;
        min-width: 0;
    }
    @media (max-width: 768px) {
        .chart-row {
            flex-direction: column;
        }
        .chart-container {
            margin-bottom: 1.5rem;
        }
    }
    /* Chart-specific styles */
    #budgetOverviewChart {
        max-height: 400px;
    }
    #monthlySpendingChart, #categorySpendingChart {
        max-height: 350px;
    }
    /* Notification Styles */
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 600;
        z-index: 1000;
        animation: slideIn 0.3s ease-out;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .notification.success {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #86efac;
    }
    .notification.error {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    .budget-summary-card {
        background: #fff;
        border-radius: 1.2rem;
        box-shadow: 0 2px 12px rgba(37,99,235,0.08);
        padding: 2rem;
        border: 1px solid #f1f5f9;
        height: fit-content;
    }
    .budget-summary-title {
        font-size: 1.4rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1.5rem;
        text-align: left;
    }
    .budget-summary-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid #e2e8f0;
    }
    .budget-summary-item:last-child {
        border-bottom: none;
    }
    .budget-summary-label {
        font-size: 1.1rem;
        color: #64748b;
        font-weight: 500;
    }
    .budget-summary-value {
        font-size: 1.2rem;
        font-weight: 600;
    }
    .budget-summary-value.total {
        color: #2563eb;
    }
    .budget-summary-value.remaining {
        color: #10b981;
    }
    .budget-summary-value.spent {
        color: #ef4444;
    }
    .budget-progress-container {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e2e8f0;
    }
    .budget-progress-label {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
        color: #64748b;
    }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="budget-section">
        <div class="container">
            <div class="budget-layout">
                <!-- Add Budget Form -->
                <div class="budget-form-card">
                    <h2>Add Budget</h2>
                    <form id="addBudgetForm" method="POST" action="budget_actions.php">
                        <input type="hidden" name="action" value="add">
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category" class="form-control" required>
                                <option value="Food">Food</option>
                                <option value="Transportation">Transportation</option>
                                <option value="Entertainment">Entertainment</option>
                                <option value="Utilities">Utilities</option>
                                <option value="Shopping">Shopping</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="amount">Budget Amount</label>
                            <input type="number" id="amount" name="amount" class="form-control" step="0.01" min="0" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Budget</button>
                    </form>
                </div>

                <!-- Budget Summary -->
                <div class="budget-summary-card">
                    <h2 class="budget-summary-title">Budget Summary</h2>
                    <div class="budget-summary-item">
                        <span class="budget-summary-label">Total Budget</span>
                        <span class="budget-summary-value total">₹<?php echo number_format($total_budget, 2); ?></span>
                    </div>
                    <div class="budget-summary-item">
                        <span class="budget-summary-label">Total Spent</span>
                        <span class="budget-summary-value spent">₹<?php echo number_format($total_spent, 2); ?></span>
                    </div>
                    <div class="budget-summary-item">
                        <span class="budget-summary-label">Remaining</span>
                        <span class="budget-summary-value remaining">₹<?php echo number_format($total_budget - $total_spent, 2); ?></span>
                    </div>
                    
                    <div class="budget-progress-container">
                        <div class="budget-progress-label">
                            <span>Budget Usage</span>
                            <span><?php echo number_format(($total_spent / $total_budget) * 100, 1); ?>%</span>
                        </div>
                        <div class="budget-progress-bar">
                            <div class="budget-progress" style="width: <?php echo min(100, ($total_spent / $total_budget) * 100); ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="budget-list-title">Your Budgets</div>
            <div class="budget-grid">
                <?php if (empty($budgets)): ?>
                    <div style="grid-column: 1/-1; text-align:center; color:#64748b; font-size:1.2rem; padding:2rem;">
                        No budgets found for this month. Add a budget above!
                    </div>
                <?php else: ?>
                    <?php foreach ($budgets as $budget): 
                        $spent = abs($budget['spent']);
                        $amount = abs($budget['amount']);
                        $percent = $amount > 0 ? ($spent / $amount) * 100 : 0;
                        $remaining = $amount - $spent;
                        
                        $progressClass = '';
                        if ($percent >= 90) {
                            $progressClass = 'danger';
                        } elseif ($percent >= 75) {
                            $progressClass = 'warning';
                        }
                    ?>
                    <div class="budget-card">
                        <div class="budget-actions">
                            <button type="button" class="edit-btn" title="Edit Budget" data-id="<?php echo $budget['id']; ?>">
                                <i class="fas fa-pen"></i>
                            </button>
                            <form action="budget_actions.php" method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="budget_id" value="<?php echo $budget['id']; ?>">
                                <button type="submit" class="delete-btn" title="Delete Budget">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                        <div class="budget-title"><?php echo htmlspecialchars($budget['category']); ?></div>
                        <div class="budget-amounts">
                            <span>Spent: ₹<?php echo number_format($spent, 2); ?></span>
                            <span>Budget: ₹<?php echo number_format($amount, 2); ?></span>
                        </div>
                        <div class="budget-progress-bar">
                            <div class="budget-progress <?php echo $progressClass; ?>" style="width: <?php echo min(100, $percent); ?>%"></div>
                        </div>
                        <div class="budget-amounts">
                            <span class="budget-remaining">Remaining: ₹<?php echo number_format($remaining, 2); ?></span>
                            <span class="budget-percent <?php echo $progressClass; ?>"><?php echo number_format($percent, 1); ?>% Used</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
        </div>
    </main>
    

    <!-- Edit Budget Modal -->
    <div id="editBudgetModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeEditModal">&times;</span>
            <h2>Edit Budget</h2>
            <form id="editBudgetForm">
                <input type="hidden" id="editBudgetId" name="budget_id">
                <div class="form-group">
                    <label for="editCategory">Category</label>
                    <input type="text" id="editCategory" name="category" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label for="editAmount">Budget Amount</label>
                    <input type="number" id="editAmount" name="amount" class="form-control" step="0.01" min="0" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Budget</button>
            </form>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <script src="app.js"></script>
    <script src="budget.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Function to show notification
        function showNotification(message, type = 'success') {
            // Remove any existing notifications
            document.querySelectorAll('.notification').forEach(n => n.remove());
            
            // Create new notification
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            
            // Add to document
            document.body.appendChild(notification);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease-out forwards';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Handle add budget form submission
        const addBudgetForm = document.getElementById('addBudgetForm');
        if (addBudgetForm) {
            addBudgetForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                
                fetch('budget_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Budget added successfully!');
                        // Clear form
                        this.reset();
                        // Reload page after a short delay
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showNotification(data.message || 'Error adding budget', 'error');
                    }
                })
                .catch(error => {
                    showNotification('An error occurred. Please try again.', 'error');
                });
            });
        }

        // Handle edit form submission
        const editBudgetForm = document.getElementById('editBudgetForm');
        if (editBudgetForm) {
            editBudgetForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'update');
                
                fetch('budget_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Budget updated successfully!');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showNotification(data.message || 'Failed to update budget', 'error');
                    }
                })
                .catch(error => {
                    showNotification('An error occurred. Please try again.', 'error');
                });
            });
        }

        // Handle delete form submission
        document.querySelectorAll('.budget-card form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                if (form.querySelector('.delete-btn')) {
                    e.preventDefault();
                    if (!confirm('Are you sure you want to delete this budget?')) return;
                    
                    const formData = new FormData(form);
                    fetch('budget_actions.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('Budget deleted successfully!');
                            form.closest('.budget-card').remove();
                        } else {
                            showNotification(data.message || 'Failed to delete budget', 'error');
                        }
                    })
                    .catch(error => {
                        showNotification('An error occurred. Please try again.', 'error');
                    });
                }
            });
        });

        // Edit button click handler
        document.querySelectorAll('.edit-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const budgetId = this.getAttribute('data-id');
                const card = this.closest('.budget-card');
                const category = card.querySelector('.budget-title').textContent.trim();
                const amount = card.querySelector('.budget-amounts span:last-child')
                    .textContent.replace(/[^\d.]/g, '');

                document.getElementById('editBudgetId').value = budgetId;
                document.getElementById('editCategory').value = category;
                document.getElementById('editAmount').value = amount;
                document.getElementById('editBudgetModal').style.display = 'flex';
            });
        });
        // Close modal
        document.getElementById('closeEditModal').onclick = function() {
            document.getElementById('editBudgetModal').style.display = 'none';
        };
        window.onclick = function(event) {
            if (event.target === document.getElementById('editBudgetModal')) {
                document.getElementById('editBudgetModal').style.display = 'none';
            }
        };
    });
    </script>
    <script>
        // Common chart options
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        font: {
                            size: 12,
                            weight: '600'
                        }
                    }
                }
            }
        };

        // Budget Overview Chart
        const budgetOverviewCtx = document.getElementById('budgetOverviewChart').getContext('2d');
        new Chart(budgetOverviewCtx, {
            type: 'doughnut',
            data: {
                labels: ['Spent', 'Remaining'],
                datasets: [{
                    data: [<?php echo $total_spent; ?>, <?php echo $total_budget - $total_spent; ?>],
                    backgroundColor: ['#ef4444', '#10b981'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                ...commonOptions,
                cutout: '70%',
                plugins: {
                    ...commonOptions.plugins,
                    title: {
                        display: true,
                        text: 'Total Budget: ₹<?php echo number_format($total_budget, 2); ?>',
                        font: {
                            size: 16,
                            weight: 'bold'
                        },
                        padding: {
                            bottom: 30
                        }
                    }
                }
            }
        });

        // Monthly Spending Trend Chart
        const monthlySpendingCtx = document.getElementById('monthlySpendingChart').getContext('2d');
        new Chart(monthlySpendingCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($monthly_spending, 'month_year')); ?>,
                datasets: [{
                    label: 'Monthly Expenses',
                    data: <?php echo json_encode(array_column($monthly_spending, 'total_expenses')); ?>,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                ...commonOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            },
                            font: {
                                size: 11
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });

        // Category-wise Spending Chart
        const categorySpendingCtx = document.getElementById('categorySpendingChart').getContext('2d');
        new Chart(categorySpendingCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($category_spending, 'category')); ?>,
                datasets: [{
                    label: 'Spent Amount',
                    data: <?php echo json_encode(array_column($category_spending, 'total_spent')); ?>,
                    backgroundColor: '#3b82f6',
                    borderRadius: 6,
                    borderSkipped: false,
                    barThickness: 20,
                    maxBarThickness: 30
                }]
            },
            options: {
                ...commonOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            },
                            font: {
                                size: 11
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });
    </script>
    
</body>
</html> 