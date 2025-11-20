<?php
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config.php';

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "All fields are required";
    } else {
        $sql = "SELECT id, password FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid email or password";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Personal Finance Tracker</title>
    <link rel="icon" type="image/x-icon" href="./favicon_io/android-chrome-512x512.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
    body { background: linear-gradient(120deg, #f0f9ff 0%, #e0e7ff 100%); }
    .auth-section { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
    .auth-flex {
        display: flex;
        flex-direction: row;
        align-items: stretch;
        justify-content: center;
        min-height: 70vh;
        background: #fff;
        border-radius: 1.5rem;
        box-shadow: 0 4px 24px rgba(37,99,235,0.10);
        overflow: hidden;
    }
    .auth-info {
        flex: 1 1 50%;
        min-width: 320px;
        max-width: 600px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #f0f9ff 60%, #e0e7ff 100%);
        padding: 3rem 2rem;
        text-align: left;
    }
    .auth-info h2 { color: #2563eb; font-size: 2rem; margin-bottom: 1rem; }
    .auth-info p { color: #64748b; font-size: 1.1rem; margin-bottom: 1.5rem; }
    .auth-info img {
        max-width: 340px;
        width: 100%;
        border-radius: 1.2rem;
        box-shadow: 0 4px 16px rgba(37,99,235,0.12);
        border: 3px solid #e0e7ff;
        margin-top: 1rem;
        margin-bottom: 1rem;
        background: #fff;
        display: block;
    }
    .auth-container {
        flex: 1 1 50%;
        min-width: 320px;
        max-width: 400px;
        background: linear-gradient(135deg, #fff, #e0e7ff 80%);
        border-radius: 0 1.5rem 1.5rem 0;
        box-shadow: none;
        padding: 3rem 2rem 2rem 2rem;
        margin: 0;
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .auth-container h1 { color: #2563eb; margin-bottom: 1.5rem; }
    .form-group label { color: #2563eb; font-weight: 600; }
    .form-group input {
        width: 100%; padding: 0.8rem; border-radius: 0.7rem;
        border: 1px solid #c7d2fe; margin-bottom: 1rem;
        font-size: 1rem; background: #f0f9ff;
    }
    .btn-primary {
        background: linear-gradient(90deg, #2563eb, #3b82f6);
        color: #fff; border: none; border-radius: 0.7rem;
        padding: 0.8rem 1.5rem; font-size: 1rem; font-weight: 700;
        cursor: pointer; transition: background 0.3s;
        width: 100%;
    }
    .btn-primary:hover { background: #1e40af; }
    .error-message {
        background: #fee2e2;
        color: #b91c1c;
        border-radius: 0.7rem;
        padding: 0.75rem 1rem;
        margin-bottom: 1rem;
        font-weight: 600;
    }
    .auth-link { margin-top: 1.5rem; color: #64748b; }
    .auth-link a { color: #2563eb; text-decoration: underline; }
    </style>
</head>
<body>
    <main class="auth-section">
        <div class="container">
            <div class="auth-flex">
                <div class="auth-info">
                    <h2>Welcome Back!</h2>
                    <p>Log in to your Personal Finance Tracker account to manage your budgets, track expenses, and achieve your financial goals. Your secure financial dashboard awaits.</p>
                    <img src="./assets/makeme.png" alt="Finance Illustration">
                </div>
                <div class="auth-container">
                    <h1>Login</h1>
                    <?php if ($error): ?>
                        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <form action="login.php" method="POST">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Login</button>
                    </form>
                    <p class="auth-link">
                        Don't have an account? <a href="register.php">Register</a>
                    </p>
                </div>
            </div>
        </div>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html> 