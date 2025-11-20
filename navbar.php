<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar">
    <style>
    .navbar {
        background: linear-gradient(90deg, #e0f2fe 0%,rgb(236, 242, 245) 100%);
        box-shadow: 0 2px 8px rgba(37,99,235,0.07);
        width: 100%;
        margin-bottom: 2rem;
        padding: 0;
    }
    .navbar-flex {
        display: flex;
        align-items: center;
        justify-content: space-between;
        max-width: 1200px;
        margin: 0 auto;
        padding: 1.2rem 2rem 1.2rem 2rem;
    }
    .navbar-logo {
        font-weight: 700;
        font-size: 1.3rem;
        color: #2563eb;
        line-height: 1.1;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        letter-spacing: 0.5px;
    }
    .logo-line1 {
        font-size: 1.1rem;
        color: #2563eb;
        font-weight: 700;
    }
    .logo-line2 {
        font-size: 1.1rem;
        color: #2563eb;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    .nav-links {
        display: flex;
        gap: 1.5rem;
        align-items: center;
        margin-left: 2rem;
    }
    .nav-links a {
        color: #1e293b;
        text-decoration: none;
        font-weight: 500;
        font-size: 1rem;
        padding: 0.5rem 1.1rem;
        border-radius: 0.7rem;
        transition: background 0.2s, color 0.2s;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .nav-links a.active, .nav-links a:hover {
        background: #e0e7ff;
        color: #2563eb;
    }
    .nav-btn {
        background: linear-gradient(90deg, #2563eb, #3b82f6);
        color: #fff !important;
        font-weight: 600;
        border-radius: 0.7rem;
        padding: 0.5rem 1.3rem;
        box-shadow: 0 2px 8px rgba(37,99,235,0.07);
        margin-right: 0.7rem;
        transition: background 0.2s, color 0.2s;
    }
    .nav-btn:hover {
        background: #1e40af;
        color: #fff !important;
    }
    .nav-actions {
        margin-left: 2rem;
    }
    .btn-logout {
        background: #ef4444;
        color: #fff;
        border-radius: 0.7rem;
        padding: 0.5rem 1.1rem;
        font-weight: 600;
        text-decoration: none;
        transition: background 0.2s;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .btn-logout:hover {
        background: #b91c1c;
        color: #fff;
    }
    @media (max-width: 900px) {
        .navbar-flex {
            flex-direction: column;
            align-items: flex-start;
            padding: 1.2rem 1rem;
        }
        .nav-links {
            flex-wrap: wrap;
            gap: 0.7rem;
            margin-left: 0;
        }
        .nav-actions {
            margin-left: 0;
            margin-top: 1rem;
        }
    }
    </style>
    <div class="container navbar-flex">
        <div class="navbar-logo">
            <span class="logo-line1">Personal</span><br>
            <span class="logo-line2"><i class="fas fa-chart-line"></i> Finance Tracker</span>
        </div>
        <div class="nav-links">
            <a href="index.php" class="nav-btn <?php echo $current_page === 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Home
            </a>
            <a href="dashboard.php" class="<?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-table-columns"></i> Dashboard
            </a>
            <a href="transactions.php" class="<?php echo $current_page === 'transactions.php' ? 'active' : ''; ?>">
                <i class="fas fa-exchange-alt"></i> Transactions
            </a>
            <a href="budget.php" class="<?php echo $current_page === 'budget.php' ? 'active' : ''; ?>">
                <i class="fas fa-wallet"></i> Budget
            </a>
            <a href="howitworks.php" class="<?php echo $current_page === 'howitworks.php' ? 'active' : ''; ?>">
                <i class="fas fa-bullseye"></i> How it works
            </a>
            <a href="investment.php" class="<?php echo $current_page === 'investment.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i> Investment
            </a>
            <a href="loans.php" class="<?php echo $current_page === 'loans.php' ? 'active' : ''; ?>">
                <i class="fas fa-hand-holding-usd"></i> Loans
            </a>
        </div>
        <div class="nav-actions">
            <a href="logout.php" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</nav>

<script>
document.querySelector('.mobile-menu').addEventListener('click', function() {
    document.querySelector('.nav-links').classList.toggle('active');
});
</script> 