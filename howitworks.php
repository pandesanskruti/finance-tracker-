<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>How it works - Personal Finance Tracker</title>
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
        .how-section {
            padding: 2rem 0;
            background: var(--accent-color);
        }
        .how-title {
            text-align: center;
            font-size: 2.4rem;
            font-weight: bold;
            margin-bottom: 30px;
            color: var(--text-color);
            position: relative;
        }
        .how-title::after {
            content: "";
            display: block;
            margin: 12px auto 0 auto;
            width: 60px;
            height: 4px;
            background: #ffe600;
            border-radius: 2px;
        }
        .how-flex {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 40px;
            background: #fff;
            border-radius: 12px;
            padding: 40px 30px;
            max-width: 1000px;
            margin: 0 auto;
            box-shadow: var(--shadow);
        }
        .how-content {
            flex: 1 1 350px;
            min-width: 300px;
        }
        .how-content h2 {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 18px;
            color: var(--text-color);
        }
        .how-content span {
            color: #ff3c00;
        }
        .how-content ul {
            margin-top: 18px;
            margin-bottom: 0;
            padding-left: 0;
            list-style: none;
        }
        .how-content ul li {
            font-size: 1.1rem;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            color: var(--text-color);
        }
        .how-content ul li i {
            color: #00c853;
            margin-right: 10px;
            font-size: 1.2rem;
        }
        .how-illustration {
            flex: 1 1 350px;
            min-width: 280px;
            display: flex;
            justify-content: center;
        }
        .how-illustration img {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
            box-shadow: var(--shadow);
            background: #f5f5f5;
        }
        .how-footer {
            text-align: center;
            margin-top: 30px;
            color: var(--light-text);
            font-size: 1rem;
        }
        @media (max-width: 900px) {
            .how-flex {
                flex-direction: column;
                text-align: center;
            }
            .how-content, .how-illustration {
                min-width: 0;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <section class="how-section">
        <div class="container">
            <div class="how-title">
                How it works
            </div>
            <div class="how-flex">
                <div class="how-content">
                    <h2>Experience the power of <span>Personal Finance Tracker</span></h2>
                    <ul>
                        <li><i class="fas fa-check"></i>Track effortlessly</li>
                        <li><i class="fas fa-check"></i>Control transactions</li>
                        <li><i class="fas fa-check"></i>Customize currencies</li>
                        <li><i class="fas fa-check"></i>Edit with ease</li>
                        <li><i class="fas fa-check"></i>Real-time balance</li>
                        <li><i class="fas fa-check"></i>Secure data protection</li>
                    </ul>
                </div>
                <div class="how-illustration">
                    <img src="./assets/how-it-works.png" alt="How it works illustration">
                </div>
            </div>

        </div>
    </section>

    <?php include 'footer.php'; ?>
</body>
</html>