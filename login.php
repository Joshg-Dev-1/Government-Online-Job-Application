<?php
session_start();
$conn = new mysqli("localhost", "root", "", "egov_portal");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
$message = '';
$message_type = 'error';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, username, email, password, role, is_verified FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            if ($user['is_verified'] == 1) {
                // Store session data
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                if ($user['role'] == "admin") {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: user/user_dashboard.php");
                }
                exit;
            } else {
                $message = 'Please verify your email first.';
            }
        } else {
            $message = 'Invalid password.';
        }
    } else {
        $message = 'No user found with this email.';
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GOJA - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #1e40af 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(-45deg, #0f172a, #1e3a8a, #1e40af, #3b82f6);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
            z-index: -1;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .floating {
            animation: floating 3s ease-in-out infinite;
        }

        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            animation: slideUp 0.6s ease-out;
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 500px;
            max-width: 900px;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .left-section {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            border-radius: 20px 0 0 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px 30px;
            position: relative;
            overflow: hidden;
        }

        .left-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: moveBackground 20s linear infinite;
        }

        @keyframes moveBackground {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }

        .left-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
        }

        .logo-icon {
            font-size: 64px;
            margin-bottom: 20px;
            display: inline-block;
            background: rgba(255, 255, 255, 0.1);
            width: 100px;
            height: 100px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left:150px ;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }

        .left-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .left-subtitle {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .benefits-list {
            text-align: left;
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 30px;
        }

        .benefit-item {
            display: flex;
            gap: 12px;
            align-items: center;
            font-size: 14px;
        }

        .benefit-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .right-section {
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }

        .form-title {
            color: #0f172a;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .form-subtitle {
            color: #64748b;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 24px;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
        }

        .input-icon {
            position: absolute;
            left: 12px;
            top: 38px;
            color: #94a3b8;
            font-size: 18px;
            transition: color 0.3s ease;
            z-index: 2;
            pointer-events: none;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 38px;
            color: #94a3b8;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 3;
            background: none;
            border: none;
            padding: 5px;
        }

        .password-toggle:hover {
            color: #1e3a8a;
        }

        .input-field {
            width: 100%;
            padding: 12px 45px 12px 42px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s ease;
            background-color: #f8fafc;
        }

        .input-field:focus {
            outline: none;
            border-color: #1e3a8a;
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
        }

        .input-field:focus + .input-icon {
            color: #1e3a8a;
        }

        .label-text {
            display: block;
            color: #334155;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .message {
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.4s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.error {
            background-color: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #dc2626;
        }

        .message.success {
            background-color: #dcfce7;
            color: #15803d;
            border-left: 4px solid #22c55e;
        }

        .message i {
            font-size: 18px;
        }

        .submit-btn {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
            position: relative;
            overflow: hidden;
        }

        .submit-btn:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .submit-btn:hover:before {
            left: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(30, 58, 138, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .footer-links {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 20px;
        }

        .footer-link {
            text-align: center;
            font-size: 14px;
            color: #64748b;
        }

        .footer-link a {
            color: #1e3a8a;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
        }

        .footer-link a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #1e3a8a, #1e40af);
            transition: width 0.3s ease;
        }

        .footer-link a:hover::after {
            width: 100%;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #1e3a8a;
            border-radius: 4px;
        }

        .checkbox-group label {
            font-size: 14px;
            color: #475569;
            cursor: pointer;
            user-select: none;
        }

        .info-banner {
            background: linear-gradient(135deg, #dbeafe 0%, #e0e7ff 100%);
            border-left: 4px solid #3b82f6;
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #0c4a6e;
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }

        .info-banner i {
            margin-top: 2px;
            flex-shrink: 0;
        }

        .remember-forgot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 20px;
        }

        .forgot-link {
            text-align: right;
            flex: 1;
        }

        .forgot-link a {
            color: #1e3a8a;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .forgot-link a:hover {
            color: #1e40af;
        }

        @media (max-width: 1024px) {
            .login-container {
                grid-template-columns: 1fr;
                max-width: 500px;
                min-height: auto;
            }

            .left-section {
                border-radius: 20px 20px 0 0;
                padding: 30px 20px;
                min-height: 250px;
            }

            .right-section {
                border-radius: 0 0 20px 20px;
                padding: 40px 30px;
            }

            .logo-icon {
                font-size: 48px;
                width: 80px;
                height: 80px;
            }

            .left-title {
                font-size: 24px;
            }

            .benefits-list {
                display: none;
            }
        }

        @media (max-width: 640px) {
            .form-title {
                font-size: 24px;
            }

            .login-container {
                margin: 15px;
                border-radius: 15px;
            }

            .right-section {
                padding: 30px 20px;
            }

            .logo-icon {
                font-size: 40px;
                width: 70px;
                height: 70px;
            }
        }
    </style>
</head>
<body>
    <div class="animated-bg"></div>

    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="login-container">
            <!-- Left Section -->
            <div class="left-section">
                <div class="left-content">
                    <div class="logo-icon floating">
                        <i class="fas fa-landmark"></i>
                    </div>
                    <h1 class="left-title">Government Online Job Application</h1>
                    <p class="left-subtitle">Citizen Portal</p>

                    <div class="benefits-list">
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <span>Quick & Easy Access</span>
                        </div>
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <span>Secure & Encrypted</span>
                        </div>
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <span>Available 24/7</span>
                        </div>
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="fas fa-headset"></i>
                            </div>
                            <span>Dedicated Support</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Section -->
            <div class="right-section">
                <!-- Title -->
                <div class="mb-6">
                    <h2 class="form-title">Welcome Back</h2>
                    <p class="form-subtitle">Apply job for government services</p>
                </div>

                <!-- Info Banner -->
                <div class="info-banner">
                    <i class="fas fa-circle-info"></i>
                    <span>Login with your registered email and password</span>
                </div>

                <!-- Error/Success Message -->
                <?php if (!empty($message)): ?>
                    <div class="message error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo $message; ?></span>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form method="post" action="" class="mt-4">
                    <!-- Email Input -->
                    <div class="input-group">
                        <label class="label-text">Email Address</label>
                        <input type="email" id="email" name="email" required 
                               class="input-field" placeholder="your.email@example.com">
                        <i class="fas fa-envelope input-icon"></i>
                    </div>

                    <!-- Password Input -->
                    <div class="input-group">
                        <label class="label-text">Password</label>
                        <input type="password" id="password" name="password" required 
                               class="input-field" placeholder="Enter your password">
                        <i class="fas fa-lock input-icon"></i>
                        <button type="button" class="password-toggle" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="remember-forgot">
                        <div class="checkbox-group">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember me</label>
                        </div>
                        <div class="forgot-link">
                            <a href="#">Forgot password?</a>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-sign-in-alt" style="margin-right: 8px;"></i>Login
                    </button>
                </form>

                <!-- Footer Links -->
                <div class="footer-links">
                    <div class="footer-link">
                        Don't have an account? 
                        <a href="register.php">Create one now</a>
                    </div>
                    <div class="footer-link">
                        <a href="#">Need help? Contact support</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password visibility toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', function(e) {
            e.preventDefault();
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle icon
            if (type === 'password') {
                this.innerHTML = '<i class="fas fa-eye"></i>';
            } else {
                this.innerHTML = '<i class="fas fa-eye-slash"></i>';
            }
        });

        // Input animation on focus
        document.querySelectorAll('.input-field').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        // Form validation feedback
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            if (!email || !password) {
                e.preventDefault();
                alert('Please fill in all fields');
            }
        });
    </script>
</body>
</html>
