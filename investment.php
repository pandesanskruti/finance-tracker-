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
    <title>Investments - Personal Finance Tracker</title>
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

        .investment-hero {
            background: var(--accent-color, #f5f8fa);
            padding: 60px 0 40px 0;
            text-align: center;
        }
        .investment-hero h1 {
            font-size: 2.8rem;
            font-weight: bold;
            margin-bottom: 18px;
            color: var(--text-color, #222);
        }
        .investment-hero h2 {
            font-size: 2rem;
            margin-bottom: 40px;
            color: var(--secondary-color, #1976D2);
            position: relative;
        }
        .investment-hero h2::after {
            content: "";
            display: block;
            margin: 12px auto 0 auto;
            width: 60px;
            height: 4px;
            background: #1976D2;
            border-radius: 2px;
        }
        .investment-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            justify-content: center;
            margin-top: 30px;
        }
        .investment-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.07);
            padding: 30px 24px;
            width: 320px;
            text-align: left;
            transition: transform 0.2s;
        }
        .investment-card:hover {
            transform: translateY(-6px) scale(1.03);
        }
        .investment-card h3 {
            color: #1976D2;
            margin-bottom: 12px;
            font-size: 1.3rem;
        }
        .investment-card p {
            color: #444;
            margin-bottom: 18px;
        }
        .investment-card .btn {
            background: #2196F3;
            color: #fff;
            border: none;
            padding: 10px 22px;
            border-radius: 6px;
            font-weight: 500;
            text-decoration: none;
            transition: background 0.2s;
        }
        .investment-card .btn:hover {
            background: #1976D2;
        }
        @media (max-width: 900px) {
            .investment-grid {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <section class="investment-hero">
        <h1>Investment Opportunities</h1>
        <div class="investment-grid">
            <div class="investment-card">
                <h3>Gold Investment</h3>
                <p>Investing in gold offers a stable and tangible asset that can hedge against inflation and currency fluctuations.</p>
                <a href="https://www.motilaloswal.com/invest-in-gold" target="_blank" class="btn">Learn More</a>
            </div>
            <div class="investment-card">
                <h3>Bitcoin</h3>
                <p>Bitcoin is a decentralized digital currency, offering high potential returns but with increased volatility and risk.</p>
                <a href="https://www.coinbase.com/price/bitcoin" target="_blank" class="btn">Learn More</a>
            </div>
            <div class="investment-card">
                <h3>Mutual Funds</h3>
                <p>Mutual funds pool money from multiple investors to invest in diversified portfolios managed by professionals.</p>
                <a href="https://www.mutualfundssahihai.com/en" target="_blank" class="btn">Learn More</a>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>
</body>
</html>