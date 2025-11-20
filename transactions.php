<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config.php';

// Get user's transactions
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM transactions WHERE user_id = ? ORDER BY date DESC";
    $stmt = $conn->prepare($sql);
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
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

$total_income = floatval($stats['total_income'] ?? 0);
$total_expenses = floatval($stats['total_expenses'] ?? 0);
$balance = $total_income - $total_expenses;
$monthly_savings = $balance;

// Get success/error messages
$success_message = '';
$error_message = '';

if (isset($_GET['success']) && $_GET['success'] === 'delete') {
    $success_message = 'Transaction deleted successfully!';
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'delete':
            $error_message = 'Failed to delete transaction. Please try again.';
            break;
        case 'notfound':
            $error_message = 'Transaction not found.';
            break;
        default:
            $error_message = 'An error occurred. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - Personal Finance Tracker</title>
    <link rel="icon" type="image/x-icon" href="./favicon_io/android-chrome-512x512.png" />
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
        .trans-flex {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        .trans-form {
            background: #fff;
            border-radius: 1.2rem;
            box-shadow: 0 2px 12px rgba(37,99,235,0.08);
            padding: 2rem 1.5rem;
            min-width: 320px;
            max-width: 350px;
            flex: 1 1 350px;
            border: 1px solid #f1f5f9;
            height: fit-content;
        }
        .trans-form h2 {
            color: #1e293b;
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
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
        }
        .btn:hover, .btn-primary:hover {
            background: #1e40af;
        }
        .trans-table {
            background: #fff;
            border-radius: 1.2rem;
            box-shadow: 0 2px 12px rgba(37,99,235,0.08);
            padding: 2rem 1.5rem;
            min-width: 320px;
            max-width: 700px;
            flex: 2 1 500px;
            border: 1px solid #f1f5f9;
        }
        .trans-table h2 {
            color: #1e293b;
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0.5rem;
        }
        th, td {
            padding: 0.75rem 0.5rem;
            text-align: left;
        }
        th {
            color: #1e293b;
            font-size: 1rem;
            font-weight: 700;
            border-bottom: 2px solid #f1f5f9;
        }
        td {
            color: #334155;
            font-size: 1rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .badge-income {
            background: #d1fae5;
            color: #10b981;
            border-radius: 0.7rem;
            padding: 0.2rem 0.8rem;
            font-size: 0.95rem;
            font-weight: 600;
            text-transform: lowercase;
        }
        .badge-expense {
            background: #fee2e2;
            color: #ef4444;
            border-radius: 0.7rem;
            padding: 0.2rem 0.8rem;
            font-size: 0.95rem;
            font-weight: 600;
            text-transform: lowercase;
        }
        .delete-btn {
            background: none;
            border: none;
            color: #ef4444;
            font-size: 1.2rem;
            cursor: pointer;
            transition: color 0.2s;
        }
        .delete-btn:hover {
            color: #b91c1c;
        }
        @media (max-width: 900px) {
            .trans-flex { flex-direction: column; gap: 1.5rem; }
            .trans-form, .trans-table { max-width: 100%; }
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
    .edit-btn {
        background: none;
        border: none;
        color: #2563eb;
        font-size: 1.2rem;
        cursor: pointer;
        transition: color 0.2s;
    }
    .edit-btn:hover {
        color: #1e40af;
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
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <?php if ($success_message): ?>
        <div class="notification success" style="background:#d1fae5;color:#065f46;padding:1rem 1.5rem;border-radius:0.7rem;margin-bottom:1.5rem;text-align:center;font-weight:600;">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="notification error" style="background:#fee2e2;color:#b91c1c;padding:1rem 1.5rem;border-radius:0.7rem;margin-bottom:1.5rem;text-align:center;font-weight:600;">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <main class="transactions-section">
        
        <div class="container">
        <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-wallet"></i></div>
                    <div class="stat-info">
                        <h3 id="total-balance">₹<?php echo number_format($balance, 2); ?></h3>
                        <p>Total Balance</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon income"><i class="fas fa-arrow-up"></i></div>
                    <div class="stat-info">
                        <h3 id="total-income">₹<?php echo number_format($total_income, 2); ?></h3>
                        <p>Total Income</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon expense"><i class="fas fa-arrow-down"></i></div>
                    <div class="stat-info">
                        <h3 id="total-expenses">₹<?php echo number_format($total_expenses, 2); ?></h3>
                        <p>Total Expenses</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon savings"><i class="fas fa-piggy-bank"></i></div>
                    <div class="stat-info">
                        <h3 id="monthly-savings">₹<?php echo number_format($monthly_savings, 2); ?></h3>
                        <p>Monthly Savings</p>
                    </div>
                </div>
            </div>
            <div style="text-align: right; margin-bottom: 1rem;">
                <button onclick="printTransactionHistory()" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-print"></i> Print Transaction History
                </button>
            </div>
            <div class="trans-flex">
                <div class="trans-form">
                    <h2>Add Transaction</h2>
                    <form id="transactionForm" action="transaction_actions.php" method="POST">
                        <input type="hidden" name="action" value="add">
                        <div class="form-group">
                            <label for="type">Type</label>
                            <select id="type" name="type" class="form-control" required>
                                <option value="income">Income</option>
                                <option value="expense">Expense</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category" class="form-control" required>
                                <option value="Salary">Salary</option>
                                <option value="Food">Food</option>
                                <option value="Transportation">Transportation</option>
                                <option value="Entertainment">Entertainment</option>
                                <option value="Utilities">Utilities</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="amount">Amount</label>
                            <input type="number" id="amount" name="amount" class="form-control" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <input type="text" id="description" name="description" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="date">Date</label>
                            <input type="date" id="date" name="date" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Transaction</button>
                    </form>
                </div>
                <div class="trans-table">
                    <h2>Recent Transactions</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($transaction['date']); ?></td>
                                <td>
                                    <span class="badge-<?php echo $transaction['type']; ?>">
                                        <?php echo htmlspecialchars($transaction['type']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($transaction['category']); ?></td>
                                <td>₹<?php echo number_format($transaction['amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                                <td>
                                    <form action="transaction_actions.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="transaction_id" value="<?php echo $transaction['id']; ?>">
                                        <button type="submit" class="delete-btn" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                    <button class="edit-btn" title="Edit" data-id="<?php echo $transaction['id']; ?>" style="background:none;border:none;color:#2563eb;font-size:1.2rem;cursor:pointer;margin-left:0.5rem;"><i class="fas fa-pen"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('transactionForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    fetch('transaction_actions.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('Transaction added successfully!', 'success');
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            showNotification(data.message || 'Error adding transaction', 'error');
                        }
                    });
                });
            }

            function showNotification(message, type = 'success') {
                const notification = document.createElement('div');
                notification.className = `notification ${type}`;
                notification.style.background = type === 'success' ? '#d1fae5' : '#fee2e2';
                notification.style.color = type === 'success' ? '#065f46' : '#b91c1c';
                notification.style.padding = '1rem 1.5rem';
                notification.style.borderRadius = '0.7rem';
                notification.style.marginBottom = '1.5rem';
                notification.style.textAlign = 'center';
                notification.style.fontWeight = '600';
                notification.textContent = message;
                document.body.prepend(notification);
                setTimeout(() => {
                    notification.remove();
                }, 2000);
            }
        });

        function printTransactionHistory() {
            const printWindow = window.open('generate_transaction_pdf.php', '_blank');
            printWindow.onload = function() {
                printWindow.print();
            };
        }
    </script>

    <div id="editTransactionModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeEditTransactionModal">&times;</span>
            <h2>Edit Transaction</h2>
            <form id="editTransactionForm">
                <input type="hidden" id="editTransactionId" name="transaction_id">
                <div class="form-group">
                    <label for="editType">Type</label>
                    <select id="editType" name="type" class="form-control" required>
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editCategory">Category</label>
                    <select id="editCategory" name="category" class="form-control" required>
                        <option value="Salary">Salary</option>
                        <option value="Food">Food</option>
                        <option value="Transportation">Transportation</option>
                        <option value="Entertainment">Entertainment</option>
                        <option value="Utilities">Utilities</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editAmount">Amount</label>
                    <input type="number" id="editAmount" name="amount" class="form-control" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="editDescription">Description</label>
                    <input type="text" id="editDescription" name="description" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="editDate">Date</label>
                    <input type="date" id="editDate" name="date" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Transaction</button>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Edit button click handler for transactions
        document.querySelectorAll('.edit-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const row = this.closest('tr');
                document.getElementById('editTransactionId').value = this.getAttribute('data-id');
                document.getElementById('editType').value = row.querySelector('span[class^="badge-"]').textContent.trim();
                document.getElementById('editCategory').value = row.children[2].textContent.trim();
                document.getElementById('editAmount').value = row.children[3].textContent.replace(/[^\d.]/g, '');
                document.getElementById('editDescription').value = row.children[4].textContent.trim();
                document.getElementById('editDate').value = row.children[0].textContent.trim();
                document.getElementById('editTransactionModal').style.display = 'flex';
            });
        });
        // Close modal
        document.getElementById('closeEditTransactionModal').onclick = function() {
            document.getElementById('editTransactionModal').style.display = 'none';
        };
        window.onclick = function(event) {
            if (event.target === document.getElementById('editTransactionModal')) {
                document.getElementById('editTransactionModal').style.display = 'none';
            }
        };
        // Handle edit form submit
        document.getElementById('editTransactionForm').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'update');
            fetch('transaction_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to update transaction');
                }
            });
        };
    });
    </script>
</body>
</html> 