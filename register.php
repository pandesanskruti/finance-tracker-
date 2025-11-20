<?php
session_start();
require_once 'config.php';

// Modify the users table to remove unique constraint on username
try {
    $conn->query("ALTER TABLE users DROP INDEX username");
} catch (Exception $e) {
    // Ignore error if index doesn't exist
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Server-side validation
    if (empty($username)) {
        $errors[] = "Username is required";
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // Check if email already exists
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            if (!$stmt) {
                throw new Exception("Database prepare error: " . $conn->error);
            }
            
            $stmt->bind_param("s", $email);
            if (!$stmt->execute()) {
                throw new Exception("Database execute error: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $errors[] = "Email address already exists";
            }
            $stmt->close();
        } catch (Exception $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            
            if (!$stmt) {
                throw new Exception("Database prepare error: " . $conn->error);
            }
            
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if ($stmt->execute()) {
                $success = true;
                // Redirect to login page after successful registration
                header("Location: login.php?registered=1");
                exit();
            } else {
                throw new Exception("Database execute error: " . $stmt->error);
            }
        } catch (Exception $e) {
            $errors[] = "Registration failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Personal Finance Tracker</title>
    <link rel="icon" type="image/x-icon" href="./favicon_io/android-chrome-512x512.png" />
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1e40af;
            --success-color: #10b981;
            --error-color: #ef4444;
            --text-color: #1e293b;
            --light-text: #64748b;
            --border-color: #e2e8f0;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            margin: 0;
        }

        .auth-container {
            width: 100%;
            max-width: 480px;
            margin: 0 auto;
        }

        .auth-card {
            background: var(--card-bg);
            border-radius: 1.2rem;
            box-shadow: var(--shadow);
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .auth-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(to right, var(--primary-color), var(--success-color));
        }

        .auth-card h2 {
            color: var(--text-color);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
        }

        .auth-card h2::after {
            content: '';
            display: block;
            width: 60px;
            height: 4px;
            background: var(--primary-color);
            margin: 0.5rem auto 0;
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-color);
            font-size: 0.95rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            border: 2px solid var(--border-color);
            border-radius: 0.5rem;
            background: var(--bg-color);
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-text {
            display: block;
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: var(--light-text);
        }

        .error-message {
            background: #fee2e2;
            color: #b91c1c;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            border: 1px solid #fecaca;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .error-message i {
            color: #dc2626;
            font-size: 1.25rem;
        }

        .error-message div {
            margin-bottom: 0.5rem;
        }

        .error-message div:last-child {
            margin-bottom: 0;
        }

        .success-message {
            background: #dcfce7;
            color: #166534;
            padding: 1.25rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            border: 1px solid #a7f3d0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideIn 0.5s ease-out;
        }

        .success-message i {
            color: #16a34a;
            font-size: 1.5rem;
        }

        .success-message div {
            flex: 1;
        }

        .success-message .title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .success-message .subtitle {
            font-size: 0.875rem;
            opacity: 0.9;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .success-icon {
            animation: fadeIn 0.5s ease-out;
        }

        .password-requirements {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-top: 0.5rem;
            border: 1px solid var(--border-color);
        }

        .password-requirements ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .password-requirements li {
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            color: var(--light-text);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .password-requirements li::before {
            content: '•';
            color: var(--light-text);
        }

        .password-requirements li.valid {
            color: var(--success-color);
        }

        .password-requirements li.valid::before {
            content: '✓';
            color: var(--success-color);
        }

        .password-requirements li.invalid {
            color: var(--error-color);
        }

        .btn {
            width: 100%;
            padding: 0.875rem;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            background: var(--primary-color);
            color: white;
            margin-top: 1rem;
        }

        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn:active {
            transform: translateY(0);
        }

        .auth-link {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--light-text);
            font-size: 0.95rem;
        }

        .auth-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .auth-link a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .auth-card {
                padding: 2rem 1.5rem;
            }

            .auth-card h2 {
                font-size: 1.75rem;
            }

            .form-group input {
                padding: 0.625rem 0.875rem;
            }
        }

        /* Animation for form elements */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .auth-card {
            animation: fadeIn 0.5s ease-out;
        }

        .form-group {
            animation: fadeIn 0.5s ease-out;
            animation-fill-mode: both;
        }

        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .form-group:nth-child(3) { animation-delay: 0.3s; }
        .form-group:nth-child(4) { animation-delay: 0.4s; }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h2>Create Account</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <?php foreach ($errors as $error): ?>
                            <div><?php echo htmlspecialchars($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle success-icon"></i>
                    <div>
                        <div class="title">Registration Successful!</div>
                        <div class="subtitle">You will be redirected to the login page in a moment...</div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['registered'])): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle success-icon"></i>
                    <div>
                        <div class="title">Registration Successful!</div>
                        <div class="subtitle">You can now login with your credentials.</div>
                    </div>
                </div>
            <?php endif; ?>

            <form id="registerForm" method="POST" action="register.php" novalidate>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required minlength="8">
                    <div class="password-requirements">
                        <ul>
                            <li id="length" class="invalid">At least 8 characters long</li>
                            <li id="uppercase" class="invalid">Contains uppercase letter</li>
                            <li id="lowercase" class="invalid">Contains lowercase letter</li>
                            <li id="number" class="invalid">Contains number</li>
                        </ul>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <small class="form-text" id="password-match-message"></small>
                </div>

                <button type="submit" class="btn btn-primary">Register</button>
            </form>

            <p class="auth-link">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('registerForm');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const passwordMatchMessage = document.getElementById('password-match-message');
        const emailInput = document.getElementById('email');

        // Password validation
        const requirements = {
            length: /.{8,}/,
            uppercase: /[A-Z]/,
            lowercase: /[a-z]/,
            number: /[0-9]/
        };

        function validatePassword() {
            const value = password.value;
            
            // Check each requirement
            for (const [requirement, regex] of Object.entries(requirements)) {
                const element = document.getElementById(requirement);
                if (regex.test(value)) {
                    element.classList.remove('invalid');
                    element.classList.add('valid');
                } else {
                    element.classList.remove('valid');
                    element.classList.add('invalid');
                }
            }
        }

        function validatePasswordMatch() {
            if (confirmPassword.value === '') {
                passwordMatchMessage.textContent = '';
                passwordMatchMessage.style.color = '';
            } else if (password.value === confirmPassword.value) {
                passwordMatchMessage.textContent = 'Passwords match';
                passwordMatchMessage.style.color = '#059669';
            } else {
                passwordMatchMessage.textContent = 'Passwords do not match';
                passwordMatchMessage.style.color = '#dc2626';
            }
        }

        // Add event listeners
        password.addEventListener('input', validatePassword);
        confirmPassword.addEventListener('input', validatePasswordMatch);

        // Form submission validation
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const errors = [];

            // Username validation
            const username = document.getElementById('username');
            if (username.value.trim() === '') {
                errors.push('Username is required');
                isValid = false;
            }

            // Email validation
            if (!emailInput.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                errors.push('Please enter a valid email address');
                isValid = false;
            }

            // Password validation
            if (!requirements.length.test(password.value)) {
                errors.push('Password must be at least 8 characters long');
                isValid = false;
            }
            if (!requirements.uppercase.test(password.value)) {
                errors.push('Password must contain at least one uppercase letter');
                isValid = false;
            }
            if (!requirements.lowercase.test(password.value)) {
                errors.push('Password must contain at least one lowercase letter');
                isValid = false;
            }
            if (!requirements.number.test(password.value)) {
                errors.push('Password must contain at least one number');
                isValid = false;
            }

            // Password match validation
            if (password.value !== confirmPassword.value) {
                errors.push('Passwords do not match');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.innerHTML = errors.map(error => `<div>${error}</div>`).join('');
                
                // Remove any existing error messages
                const existingError = form.querySelector('.error-message');
                if (existingError) {
                    existingError.remove();
                }
                
                form.insertBefore(errorDiv, form.firstChild);
            }
        });

        // Add success message handling
        const successMessage = document.querySelector('.success-message');
        if (successMessage) {
            // Redirect to login page after 3 seconds if registration was successful
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 3000);
        }
    });
    </script>
</body>
</html>