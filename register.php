// register.php
<?php
require 'config.php';
$error = $success = '';

if ($_POST) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        try {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'customer')");
            $stmt->execute([$username, $email, $hashed]);
            $success = 'Registration successful! Please login.';
        } catch (PDOException $e) {
            $error = 'Username or email already exists';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Registration | Billing System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #ffb300 0%, #ff8a00 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        body::before {
            content: "";
            position: absolute;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 20%, rgba(255,255,255,0.15), transparent 60%),
                        radial-gradient(circle at 80% 80%, rgba(255,255,255,0.1), transparent 60%);
            animation: moveBackground 15s infinite alternate ease-in-out;
        }

        @keyframes moveBackground {
            0% { transform: translateY(0px); }
            100% { transform: translateY(-30px); }
        }

        .register-card {
            position: relative;
            z-index: 2;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            transition: all 0.4s;
        }

        .register-card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background: linear-gradient(135deg, #ffb300, #ff8a00);
            color: white;
            text-align: center;
            padding: 2rem;
            border-bottom: none;
        }

        .card-header h3 {
            font-weight: 700;
            margin-bottom: 0;
        }

        .card-body {
            padding: 2.5rem;
        }

        .register-card input {
            border-radius: 10px;
            padding: 12px;
        }

        .btn-register {
            background: linear-gradient(135deg, #ffb300, #ff8a00);
            border: none;
            color: white;
            font-weight: 600;
            border-radius: 12px;
            padding: 14px;
            transition: all 0.3s;
        }

        .btn-register:hover {
            background: linear-gradient(135deg, #ff8a00, #ffb300);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255,140,0,0.4);
        }

        .error-shake {
            animation: shake 0.4s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-8px); }
            75% { transform: translateX(8px); }
        }

        .footer-link {
            font-size: 0.9rem;
            color: #555;
            text-decoration: none;
        }

        .footer-link:hover {
            color: #ff8a00;
        }

        .alert {
            border-radius: 15px;
            border: none;
        }

        @media (max-width: 576px) {
            .register-card {
                margin: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-card col-md-5 col-lg-4">
        <div class="card-header">
            <h3><i class="bi bi-person-plus-fill me-2"></i>Customer Registration</h3>
            <p class="small mb-0">Create Your Account</p>
        </div>
        <div class="card-body <?php if($error) echo 'error-shake'; ?>">
            <?php if ($error): ?>
                <div class="alert alert-danger text-center py-2"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success text-center py-2">
                    <?php echo htmlspecialchars($success); ?>
                    <div class="mt-2">
                        <a href="customer_login.php" class="btn btn-sm btn-success">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Go to Login
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Choose a username" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="your@email.com" required>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" required>
                    <small class="text-muted">Must be at least 6 characters</small>
                </div>
                <button type="submit" class="btn btn-register w-100">
                    <i class="bi bi-person-check me-2"></i>Create Account
                </button>
            </form>
            <p class="text-center mt-4 mb-0">
                <a href="customer_login.php" class="footer-link">
                    <i class="bi bi-arrow-left me-1"></i>Already have an account? Login
                </a>
            </p>
        </div>
    </div>

    <script>
        const card = document.querySelector('.register-card');
        if (document.querySelector('.alert-danger')) {
            card.classList.add('error-shake');
            setTimeout(() => card.classList.remove('error-shake'), 400);
        }
    </script>
</body>
</html>