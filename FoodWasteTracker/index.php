<?php
include 'config.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'manager') {
        header("Location: dashboard.php");
    } else {
        header("Location: log_waste.php");
    }
} else {
    header("Location: login.php");
}
exit;
?>