<?php
include 'config.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email=?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == 'manager') {
                header("Location: dashboard.php");
            } else {
                header("Location: log_waste.php");
            }
            exit;
        } else {
            $error = "Wrong password.";
        }
    } else {
        $error = "No account with that email.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <h1><img src="logo.png" alt="Logo" style="width:56px; height:56px; vertical-align:middle; margin-right:10px;">Smart Food Waste Analytics</h1>
                <p>Log in to continue</p>
            </div>
            <?php if ($error) echo "<p class='auth-error'>" . htmlspecialchars($error) . "</p>"; ?>
            <form method="POST" class="auth-form" onsubmit="return handleLoginSubmit()">
                <input type="email" name="email" autofocus placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" id="loginBtn">Login</button>
            </form>
            <p class="auth-footer">No account? <a href="register.php">Register here</a></p>
        </div>
    </div>
    <script>
        function handleLoginSubmit() {
            const btn = document.getElementById('loginBtn');
            btn.disabled = true;
            btn.textContent = 'Logging in...';
            return true;
        }
    </script>
</body>
</html>