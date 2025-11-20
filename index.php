<?php
session_start();
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Finance Tracker</title>
    <link rel="icon" type="image/x-icon" href="./favicon_io/android-chrome-512x512.png" />
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .hero-section {
            background: #eaf6ff;
            padding: 60px 0 40px 0;
        }
        .hero-flex {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 40px;
        }
        .hero-content {
            flex: 1 1 350px;
            min-width: 300px;
        }
        .hero-content h1 {
            font-size: 2.8rem;
            font-weight: bold;
            margin-bottom: 18px;
        }
        .hero-content p {
            font-size: 1.2rem;
            color: #444;
            margin-bottom: 32px;
        }
        .cta-buttons {
            display: flex;
            gap: 16px;
        }
        .hero-illustration {
            flex: 1 1 350px;
            min-width: 280px;
            display: flex;
            justify-content: center;
        }
        .hero-illustration img {
            max-width: 100%;
            height: auto;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.07);
        }
        @media (max-width: 900px) {
            .hero-flex {
                flex-direction: column;
                text-align: center;
            }
            .hero-content, .hero-illustration {
                min-width: 0;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="hero-section">
        <div class="container hero-flex">
            <div class="hero-content">
                <h1>Take Control of Your Finances</h1>
                <p>Track your income and expenses effortlessly. Manage your budget, set financial goals, and make smarter decisions for a more secure future. Get insights into your spending patterns and achieve financial freedom.</p>
                <div class="cta-buttons">
                    <a href="register.php" class="btn btn-primary">Get Started</a>
                    <a href="#features" class="btn btn-secondary">Learn More</a>
                </div>
            </div>
            <div class="hero-illustration">
                <img src="./assets/makeme.png" alt="Finance Illustration">
            </div>
        </div>
    </main>

    <section class="features-section" id="features">
        <div class="container">
            <h2>Key Features</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-wallet"></i>
                    <h3>Budget Tracking</h3>
                    <p>Set and monitor your budgets across different categories.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-exchange-alt"></i>
                    <h3>Transaction Management</h3>
                    <p>Track your income and expenses with detailed categorization.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-bullseye"></i>
                    <h3>Financial Goals</h3>
                    <p>Set and track your progress towards financial goals.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-chart-line"></i>
                    <h3>Investment Tracking</h3>
                    <p>Monitor your investments and portfolio performance.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="container">
            <h2>Ready to Start Your Financial Journey?</h2>
            <p>Join thousands of users who are already managing their finances effectively.</p>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="register.php" class="btn btn-primary">Sign Up Now</a>
            <?php else: ?>
                <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'footer.php'; ?>
</body>
</html> 