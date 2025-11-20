<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config.php';

// Get user's goals
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM goals WHERE user_id = ? ORDER BY deadline ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$goals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get goal statistics
$sql = "SELECT 
        COUNT(*) as total_goals,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_goals,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_goals
        FROM goals 
        WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

$total_goals = intval($stats['total_goals'] ?? 0);
$completed_goals = intval($stats['completed_goals'] ?? 0);
$active_goals = intval($stats['active_goals'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goals - Personal Finance Tracker</title>
    <link rel="icon" type="image/x-icon" href="./favicon_io/android-chrome-512x512.png" />
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
    .form-group {
        margin-bottom: 1.2rem;
    }
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #1e293b;
    }
    .form-group input, .form-group select {
        width: 100%;
        padding: 0.8rem;
        border-radius: 0.7rem;
        border: 1px solid #c7d2fe;
        font-size: 1rem;
        background: #f0f9ff;
    }
    .btn {
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 0.7rem;
        padding: 0.8rem 1.5rem;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.3s;
    }
    .btn-primary {
        background: linear-gradient(90deg, #2563eb, #3b82f6);
    }
    .btn:hover, .btn-primary:hover {
        background: #1e40af;
    }
    .form-container {
        background: linear-gradient(135deg, #fff, #e0e7ff 80%);
        border-radius: 1.2rem;
        box-shadow: 0 4px 16px rgba(37,99,235,0.08);
        padding: 2rem 1.5rem;
        max-width: 450px;
        margin: 2rem auto;
    }
    .goals-list {
        margin-top: 2.5rem;
    }
    .goals-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        margin-top: 2rem;
    }
    .goal-card {
        background: linear-gradient(135deg, #f0f9ff 60%, #e0e7ff 100%);
        border-radius: 1.2rem;
        box-shadow: 0 2px 8px rgba(37,99,235,0.06);
        padding: 1.2rem 1rem;
        min-width: 260px;
        flex: 1 1 260px;
        position: relative;
        transition: transform 0.3s, box-shadow 0.3s;
    }
    .goal-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 20px rgba(37,99,235,0.12);
    }
    .goal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .goal-header h3 {
        color: #2563eb;
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
        flex: 1;
    }
    .goal-actions {
        display: flex;
        gap: 0.5rem;
    }
    .btn-delete, .btn-edit {
        background: none;
        border: none;
        color: #ef4444;
        cursor: pointer;
        font-size: 1.1rem;
        padding: 0.2rem 0.5rem;
        transition: color 0.2s;
    }
    .btn-edit {
        color: #2563eb;
    }
    .btn-delete:hover {
        color: #b91c1c;
    }
    .btn-edit:hover {
        color: #1e40af;
    }
    .goal-details {
        color: #64748b;
        font-size: 0.98rem;
        margin-top: 0.5rem;
        line-height: 1.7;
    }
    @media (max-width: 900px) {
        .goals-grid { flex-direction: column; gap: 1rem; }
        .form-container { max-width: 100%; }
    }
    /* Modal for editing goal */
    #editGoalModal {
        display: none;
        position: fixed;
        top: 0; left: 0; width: 100vw; height: 100vh;
        background: rgba(0,0,0,0.3);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        display: none; /* Ensure hidden by default */
    }
    #editGoalModal .modal-content {
        background: #fff;
        border-radius: 1rem;
        padding: 2rem;
        max-width: 400px;
        width: 90vw;
        max-height: 90vh;
        overflow-y: auto;
        margin: auto;
        position: relative;
        box-sizing: border-box;
    }
    @media (max-width: 500px) {
        #editGoalModal .modal-content {
            padding: 1rem;
            max-width: 98vw;
        }
    }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="goals-section">
        <div class="container">
            <h1>Financial Goals</h1>

            <!-- Goal Form -->
            <div class="form-container">
                <h2>Add Goal</h2>
                <form id="goalForm" action="goal_actions.php" method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label for="name">Goal Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="target_amount">Target Amount</label>
                        <input type="number" id="target_amount" name="target_amount" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="current_amount">Current Amount</label>
                        <input type="number" id="current_amount" name="current_amount" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="target_date">Target Date</label>
                        <input type="date" id="target_date" name="target_date" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Goal</button>
                </form>
            </div>

            <!-- Goals List -->
            <div class="goals-list">
                <h2>Your Goals</h2>
                <div class="goals-grid">
                    <?php foreach ($goals as $goal): 
                        $progress = ($goal['current_amount'] / $goal['target_amount']) * 100;
                        $days_left = ceil((strtotime($goal['deadline']) - time()) / (60 * 60 * 24));
                    ?>
                        <div class="goal-card">
                            <div class="goal-header">
                                <h3><?php echo htmlspecialchars($goal['title']); ?></h3>
                                <button class="btn-edit-goal" data-goal='<?php echo json_encode($goal); ?>' style="background:none;border:none;cursor:pointer;">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="goal_actions.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="goal_id" value="<?php echo $goal['id']; ?>">
                                    <button type="submit" class="btn-delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                
                            </div>
                            <div class="goal-progress">
                                <div class="progress-bar">
                                    <div class="progress" style="width: <?php echo $progress; ?>%"></div>
                                </div>
                                <div class="goal-amounts">
                                    <span>₹<?php echo number_format($goal['current_amount'], 2); ?> / ₹<?php echo number_format($goal['target_amount'], 2); ?></span>
                                    <span><?php echo number_format($progress, 1); ?>%</span>
                                </div>
                            </div>
                            <div class="goal-details">
                                <span class="goal-date">Target: <?php echo date('M d, Y', strtotime($goal['deadline'])); ?></span>
                                <span class="goal-days"><?php echo $days_left; ?> days left</span>
                            </div>
                            <div class="goal-status <?php echo $goal['status']; ?>">
                                <?php echo ucfirst($goal['status']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <!-- Edit Goal Modal -->
    <div id="editGoalModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); z-index:9999; align-items:center; justify-content:center;">
        <form id="editGoalForm" style="background:#fff; padding:30px; border-radius:10px; min-width:300px; max-width:90vw; margin:auto;">
            <h3>Edit Goal</h3>
            <input type="hidden" name="goal_id" id="editGoalId">
            <div class="form-group">
                <label for="editGoalName">Goal Name</label>
                <input type="text" name="name" id="editGoalName" required>
            </div>
            <div class="form-group">
                <label for="editTargetAmount">Target Amount</label>
                <input type="number" name="target_amount" id="editTargetAmount" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="editCurrentAmount">Current Amount</label>
                <input type="number" name="current_amount" id="editCurrentAmount" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="editTargetDate">Target Date</label>
                <input type="date" name="target_date" id="editTargetDate" required>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <button type="button" onclick="closeEditGoalModal()" class="btn btn-secondary" style="margin-left:10px;">Cancel</button>
        </form>
    </div>

    <script>
    function closeEditGoalModal() {
        document.getElementById('editGoalModal').style.display = 'none';
    }

    document.querySelectorAll('.btn-edit-goal').forEach(btn => {
        btn.addEventListener('click', function() {
            const goal = JSON.parse(this.getAttribute('data-goal'));
            document.getElementById('editGoalId').value = goal.id;
            document.getElementById('editGoalName').value = goal.name;
            document.getElementById('editTargetAmount').value = goal.target_amount;
            document.getElementById('editCurrentAmount').value = goal.current_amount;
            document.getElementById('editTargetDate').value = goal.target_date;
            document.getElementById('editGoalModal').style.display = 'flex';
        });
    });

    document.getElementById('editGoalForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'update');
        fetch('goal_actions.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to update goal');
            }
        });
    });
    </script>
</body>
</html> 