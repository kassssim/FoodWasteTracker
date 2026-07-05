<?php
include 'access_control.php';

if ($_SESSION['role'] != 'manager') {
    die("Access denied. Managers only.");
}

$current_page = basename($_SERVER['PHP_SELF']);

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    $del = mysqli_prepare($conn, "DELETE FROM ingredients WHERE ingredient_id=?");
    mysqli_stmt_bind_param($del, "i", $delete_id);
    if (mysqli_stmt_execute($del)) {
        $success = "Ingredient deleted. Past waste logs using it are kept for record.";
    } else {
        $error = "Failed to delete ingredient.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['name'])) {
    $name = trim($_POST['name']);
    $unit = trim($_POST['unit']);
    $cost = trim($_POST['cost_per_unit']);

    if ($name == "" || $unit == "" || $cost == "") {
        $error = "All fields are required.";
    } elseif (preg_match('/\d/', $unit)) {
        $error = "Unit cannot contain numbers (e.g. use \"kg\" not \"132131\").";
    } elseif (!is_numeric($cost) || $cost < 0) {
        $error = "Cost per unit must be a positive number.";
    } else {
        $dup_check = mysqli_prepare($conn, "SELECT COUNT(*) AS cnt FROM ingredients WHERE LOWER(name)=LOWER(?)");
        mysqli_stmt_bind_param($dup_check, "s", $name);
        mysqli_stmt_execute($dup_check);
        $dup_result = mysqli_stmt_get_result($dup_check);
        $dup_row = mysqli_fetch_assoc($dup_result);

        if ($dup_row['cnt'] > 0) {
            $error = "An ingredient with this name already exists.";
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO ingredients (name, unit, cost_per_unit) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssd", $name, $unit, $cost);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Ingredient added.";
            } else {
                $error = "Failed to add ingredient.";
            }
        }
    }
}

$result = mysqli_query($conn, "SELECT * FROM ingredients ORDER BY name ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Settings - Ingredients</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-layout">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <h2>Settings: Manage Ingredients</h2>

        <?php if ($error) echo "<p class='alert alert-error'>" . htmlspecialchars($error) . "</p>"; ?>
        <?php if ($success) echo "<p class='alert alert-success'>" . htmlspecialchars($success) . "</p>"; ?>

        <h3>Add New Ingredient</h3>
        <form method="POST" id="ingredientForm" onsubmit="return handleAddSubmit()">
            <input type="text" name="name" autofocus placeholder="Ingredient name (e.g. Chicken)" required>
            <input type="text" name="unit" placeholder="Unit (e.g. kg, liter, pcs)" pattern="[^0-9]*" title="Unit cannot contain numbers" required>
            <input type="number" step="0.01" min="0.01" name="cost_per_unit" placeholder="Cost per unit (RM)" required>
            <button type="submit" id="addBtn">Add Ingredient</button>
        </form>

        <h3>Current Ingredients</h3>
        <table style="max-width: 700px;">
            <tr>
                <th style="width: 35%;">Name</th>
                <th style="width: 20%;">Unit</th>
                <th style="width: 25%;">Cost per Unit (RM)</th>
                <th style="width: 20%;">Action</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['unit']); ?></td>
                <td><?php echo number_format($row['cost_per_unit'], 2); ?></td>
                <td>
                    <form method="POST" class="delete-form" onsubmit="return confirm('Delete this ingredient? Past waste logs will still show it.')">
                        <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($row['ingredient_id']); ?>">
                        <button type="submit" class="delete-btn">Delete</button>
                    </form>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>
<script>
    function handleAddSubmit() {
        const btn = document.getElementById('addBtn');
        btn.disabled = true;
        btn.textContent = 'Adding...';
        return true;
    }
</script>
</body>
</html>