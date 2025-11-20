<footer class="footer">
    <div class="container">
        <style>
        .footer {
            background: #f8fafc;
            margin-top: 0.1rem;
        }
        .footer-content {
            display: flex;
            flex-wrap: wrap;
            gap: 10rem;
            justify-content: space-between;
            padding: 1rem 0;
        }
        .footer-section {
            flex: 1 1 220px;
            min-width: 180px;
        }
        .footer-section h3, .footer-section h4 {
            color: #1e293b;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        .footer-section p {
            color: #64748b;
            margin-bottom: 1rem;
        }
        .footer-section ul {
            list-style: none;
            padding: 0;
        }
        .footer-section ul li {
            margin-bottom: 0.5rem;
        }
        .footer-section ul a {
            color: #64748b;
            text-decoration: none;
            transition: color 0.2s;
        }
        .footer-section ul a:hover {
            color: #2563eb;
            text-decoration: underline;
        }
        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid #e5e7eb;
            color: #64748b;
            font-size: 0.95rem;
        }
        @media (max-width: 900px) {
            .footer-content {
                flex-direction: column;
                gap: 2rem;
                align-items: flex-start;
            }
        }
        </style>
        <div class="footer-content">
            <div class="footer-section">
                <h3>Personal Finance Tracker</h3>
                <p>Take control of your financial future with our comprehensive tracking and management tools.</p>
            </div>
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="transactions.php">Transactions</a></li>
                    <li><a href="budget.php">Budget</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Resources</h4>
                <ul>
                    <li><a href="investment.php">Investments</a></li>
                    <li><a href="loans.php">Loans</a></li>
                    <li><a href="howitworks.php">How it Works</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; <?php echo date('Y'); ?> Personal Finance Tracker. Developed by Aditya Kanuje (136) &amp; Shubham Tidke (133)
        </div>
    </div>
</footer> 