<?php
include 'connection.php';

// Handle signup form submission
if(isset($_POST['signup'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = $_POST['confirm_password'];
    
    $errors = array();
    
    if($password !== $confirm_password) {
        $errors[] = "Passwords do not match!";
    }
    
    if(strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long!";
    }
    
    $email_check = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    if(mysqli_num_rows($email_check) > 0) {
        $errors[] = "Email already registered!";
    }
    
    $username_check = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    if(mysqli_num_rows($username_check) > 0) {
        $errors[] = "Username already taken!";
    }
    
    if(empty($errors)) {
        $stmt = mysqli_prepare($conn, "INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sss", $username, $email, $password);
        
        if(mysqli_stmt_execute($stmt)) {
            $signup_success = "Account created successfully! You can now login.";
        } else {
            $signup_error = "Error: " . mysqli_error($conn);
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $signup_error = implode("<br>", $errors);
    }
}

// Handle login form submission
if(isset($_POST['login'])) {
    $login_email = mysqli_real_escape_string($conn, $_POST['login_email']);
    $login_password = mysqli_real_escape_string($conn, $_POST['login_password']);
    
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $login_email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if(mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        if($login_password === $user['password']) {
            $login_success = "Login successful! Welcome " . htmlspecialchars($user['username']) . "!";
        } else {
            $login_error = "Invalid email or password!";
        }
    } else {
        $login_error = "Invalid email or password!";
    }
    
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up & Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 0;
        }
        .form-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 40px;
            height: 100%;
        }
        .form-card h2 {
            color: #667eea;
            font-weight: bold;
            margin-bottom: 30px;
            text-align: center;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 1px;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: #667eea;
            font-weight: bold;
            margin: 30px 0;
        }
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 2px solid #667eea;
        }
        .divider span {
            padding: 0 15px;
            font-size: 18px;
        }
        @media (max-width: 767px) {
            .divider::before,
            .divider::after {
                border-bottom: 1px solid #667eea;
            }
        }
    </style>
</head>
<body>  
    <div class="container">
        <div class="row justify-content-center align-items-stretch g-4">
            <!-- Signup Form -->
            <div class="col-lg-5 col-md-6">
               
                <div class="form-card">
                    <h2>Create Account</h2>
                    
                    <?php if(isset($signup_success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $signup_success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(isset($signup_error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $signup_error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="signupForm">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username:</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   placeholder="Choose a username" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email:</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="Enter your email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password:</label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Create a password" required minlength="8">
                            <small class="text-muted">Must be at least 8 characters</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password:</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   placeholder="Confirm your password" required>
                            <small id="passwordMatch" class="text-danger" style="display: none;">Passwords do not match</small>
                        </div>
                        
                        <button type="submit" name="signup" class="btn btn-primary w-100">Sign Up</button>
                    </form>
                </div>
            </div>

            <!-- Divider for mobile -->
            <div class="col-12 d-md-none">
                <div class="divider">
                    <span>OR</span>
                </div>
            </div>

            <!-- Login Form -->
            <div class="col-lg-5 col-md-6">
                <div class="form-card">
                    <h2>Login</h2>
                    
                    <?php if(isset($login_success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $login_success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(isset($login_error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $login_error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="loginForm">
                        <div class="mb-3">
                            <label for="login_email" class="form-label">Email:</label>
                            <input type="email" class="form-control" id="login_email" name="login_email" 
                                   placeholder="Enter your email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="login_password" class="form-label">Password:</label>
                            <input type="password" class="form-control" id="login_password" name="login_password" 
                                   placeholder="Enter your password" required>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="rememberMe">
                            <label class="form-check-label" for="rememberMe">Remember me</label>
                        </div>
                        
                        <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                        
                        <div class="text-center mt-3">
                            <a href="#" class="text-decoration-none">Forgot Password?</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Client-side password match validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            var password = document.getElementById('password').value;
            var confirmPassword = this.value;
            var matchMessage = document.getElementById('passwordMatch');
            
            if(password !== confirmPassword) {
                matchMessage.style.display = 'block';
            } else {
                matchMessage.style.display = 'none';
            }
        });
    </script>
</body>
</html>
