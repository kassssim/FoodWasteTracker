<?php
include 'config.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if ($name == "" || $email == "") {
        $error = "All fields are required.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $check = mysqli_prepare($conn, "SELECT * FROM users WHERE email=?");
        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);
        $check_result = mysqli_stmt_get_result($check);

        if (mysqli_num_rows($check_result) > 0) {
            $error = "Email already registered.";
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $hashed_password, $role);
            if (mysqli_stmt_execute($stmt)) {
                header("Location: login.php");
                exit;
            } else {
                $error = "Something went wrong. Try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <h1><img src="logo.png" alt="Logo" style="width:56px; height:56px; vertical-align:middle; margin-right:10px;">Smart Food Waste Analytics</h1>
                <p>Create your account</p>
            </div>
            <?php if ($error) echo "<p class='auth-error'>" . htmlspecialchars($error) . "</p>"; ?>
            <form method="POST" class="auth-form" onsubmit="return handleRegisterSubmit()">
                <input type="text" name="name" autofocus placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password (min 6 characters)" required>
                <select name="role" required>
                    <option value="staff">Staff</option>
                    <option value="manager">Manager</option>
                </select>
                <button type="submit" id="registerBtn">Register</button>
            </form>
            <p class="auth-footer">Already have an account? <a href="login.php">Login</a></p>
        </div>
    </div>
    <script>
        function handleRegisterSubmit() {
            const btn = document.getElementById('registerBtn');
            btn.disabled = true;
            btn.textContent = 'Creating account...';
            return true;
        }
    </script>
</body>
</html>