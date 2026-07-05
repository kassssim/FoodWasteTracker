<?php
include 'access_control.php';

$current_page = basename($_SERVER['PHP_SELF']);

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ingredient_id = trim($_POST['ingredient_id']);
    $quantity = trim($_POST['quantity']);
    $reason = trim($_POST['reason']);
    $user_id = $_SESSION['user_id'];

    if ($ingredient_id == "" || $quantity == "" || $reason == "") {
        $error = "All fields are required.";
    } elseif (!is_numeric($quantity) || $quantity <= 0) {
        $error = "Quantity must be a positive number.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT cost_per_unit FROM ingredients WHERE ingredient_id=?");
        mysqli_stmt_bind_param($stmt, "i", $ingredient_id);
        mysqli_stmt_execute($stmt);
        $ing_result = mysqli_stmt_get_result($stmt);
        $ing = mysqli_fetch_assoc($ing_result);

        if (!$ing) {
            $error = "Invalid ingredient selected.";
        } else {
            $cost_per_unit = $ing['cost_per_unit'];
            $financial_loss = $quantity * $cost_per_unit;

            $insert = mysqli_prepare($conn, "INSERT INTO waste_logs (user_id, ingredient_id, quantity, reason, financial_loss) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($insert, "iidsd", $user_id, $ingredient_id, $quantity, $reason, $financial_loss);

            if (mysqli_stmt_execute($insert)) {
                $success = "Waste logged. Loss: RM " . number_format($financial_loss, 2);
            } else {
                $error = "Failed to log waste.";
            }
        }
    }
}

$ingredients = mysqli_query($conn, "SELECT * FROM ingredients ORDER BY name ASC");

$recent = mysqli_query($conn, "
    SELECT waste_logs.*, 
           COALESCE(ingredients.name, 'Deleted Ingredient') AS ingredient_name, 
           COALESCE(ingredients.unit, '') AS unit 
    FROM waste_logs 
    LEFT JOIN ingredients ON waste_logs.ingredient_id = ingredients.ingredient_id 
    ORDER BY logged_at DESC LIMIT 10
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Log Food Waste</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-layout">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <h2>Log Food Waste</h2>

        <?php if ($error) echo "<p class='alert alert-error'>" . htmlspecialchars($error) . "</p>"; ?>
        <?php if ($success) echo "<p class='alert alert-success'>" . htmlspecialchars($success) . "</p>"; ?>

        <form method="POST" id="wasteForm" onsubmit="return handleSubmit()">
            <select name="ingredient_id" id="ingredientSelect" autofocus required>
                <option value="">-- Select Ingredient --</option>
                <?php while ($row = mysqli_fetch_assoc($ingredients)) { ?>
                    <option value="<?php echo htmlspecialchars($row['ingredient_id']); ?>">
                        <?php echo htmlspecialchars($row['name'] . " (" . $row['unit'] . ") - RM " . number_format($row['cost_per_unit'], 2) . "/unit"); ?>
                    </option>
                <?php } ?>
            </select>

            <input type="number" step="0.01" min="0.01" name="quantity" placeholder="Quantity wasted" required>

            <select name="reason" required>
                <option value="">-- Select Reason --</option>
                <option value="Expired">Expired</option>
                <option value="Overproduction">Overproduction</option>
                <option value="Spoilage">Spoilage</option>
                <option value="Prep Mistake">Prep Mistake</option>
                <option value="Other">Other</option>
            </select>

            <button type="submit" id="submitBtn">Log Waste</button>
        </form>

        <h3>Recent Waste Logs</h3>
        <table>
            <tr>
                <th>Ingredient</th>
                <th>Quantity</th>
                <th>Reason</th>
                <th>Loss (RM)</th>
                <th>Date</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($recent)) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['ingredient_name']); ?></td>
                <td><?php echo htmlspecialchars($row['quantity'] . " " . $row['unit']); ?></td>
                <td><?php echo htmlspecialchars($row['reason']); ?></td>
                <td><?php echo number_format($row['financial_loss'], 2); ?></td>
                <td><?php echo date('d/m/Y, h:i A', strtotime($row['logged_at'])); ?></td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>
<script>
    function handleSubmit() {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.textContent = 'Logging...';
        return true;
    }
</script>
</body>
</html>