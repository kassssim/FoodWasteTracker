<?php
include 'access_control.php';

if ($_SESSION['role'] != 'manager') {
    die("Access denied. Managers only.");
}

$current_page = basename($_SERVER['PHP_SELF']);

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_log_id'])) {
    $delete_log_id = $_POST['delete_log_id'];

    $del = mysqli_prepare($conn, "DELETE FROM waste_logs WHERE log_id=?");
    mysqli_stmt_bind_param($del, "i", $delete_log_id);
    if (mysqli_stmt_execute($del)) {
        $success = "Waste log deleted.";
    } else {
        $error = "Failed to delete log.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_all_logs'])) {
    if (mysqli_query($conn, "DELETE FROM waste_logs")) {
        $success = "All waste logs have been deleted.";
    } else {
        $error = "Failed to delete all logs.";
    }
}

$filter_ingredient = isset($_GET['ingredient_id']) ? $_GET['ingredient_id'] : "";
$filter_reason = isset($_GET['reason']) ? $_GET['reason'] : "";
$filter_from = isset($_GET['date_from']) ? $_GET['date_from'] : "";
$filter_to = isset($_GET['date_to']) ? $_GET['date_to'] : "";

$date_error = "";
if ($filter_from != "" && $filter_to != "" && $filter_from > $filter_to) {
    $date_error = "\"From\" date cannot be later than \"To\" date. Showing unfiltered date range.";
    $filter_from = "";
    $filter_to = "";
}

$where = "1=1";
$params = [];
$types = "";

if ($filter_ingredient != "") {
    $where .= " AND waste_logs.ingredient_id = ?";
    $params[] = $filter_ingredient;
    $types .= "i";
}
if ($filter_reason != "") {
    $where .= " AND waste_logs.reason = ?";
    $params[] = $filter_reason;
    $types .= "s";
}
if ($filter_from != "") {
    $where .= " AND DATE(waste_logs.logged_at) >= ?";
    $params[] = $filter_from;
    $types .= "s";
}
if ($filter_to != "") {
    $where .= " AND DATE(waste_logs.logged_at) <= ?";
    $params[] = $filter_to;
    $types .= "s";
}

$sql = "
    SELECT waste_logs.*, 
           COALESCE(ingredients.name, 'Deleted Ingredient') AS ingredient_name, 
           COALESCE(ingredients.unit, '') AS unit, 
           users.name AS staff_name
    FROM waste_logs
    LEFT JOIN ingredients ON waste_logs.ingredient_id = ingredients.ingredient_id
    JOIN users ON waste_logs.user_id = users.user_id
    WHERE $where
    ORDER BY logged_at DESC
";

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$logs = mysqli_stmt_get_result($stmt);

$grand_total = 0;
$log_rows = [];
while ($row = mysqli_fetch_assoc($logs)) {
    $grand_total += $row['financial_loss'];
    $log_rows[] = $row;
}

$has_results = count($log_rows) > 0;

$ingredients = mysqli_query($conn, "SELECT * FROM ingredients ORDER BY name ASC");

$export_query_string = http_build_query([
    'ingredient_id' => $filter_ingredient,
    'reason' => $filter_reason,
    'date_from' => $filter_from,
    'date_to' => $filter_to
]);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reports</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-layout">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <h2>Reports: Waste Log History</h2>

        <?php if ($error) echo "<p class='alert alert-error'>" . htmlspecialchars($error) . "</p>"; ?>
        <?php if ($success) echo "<p class='alert alert-success'>" . htmlspecialchars($success) . "</p>"; ?>
        <?php if ($date_error) echo "<p class='alert alert-error'>" . htmlspecialchars($date_error) . "</p>"; ?>

        <h3>Filter Waste Logs</h3>
        <form method="GET">
            <select name="ingredient_id">
                <option value="">All Ingredients</option>
                <?php while ($row = mysqli_fetch_assoc($ingredients)) { ?>
                    <option value="<?php echo htmlspecialchars($row['ingredient_id']); ?>" <?php if ($filter_ingredient == $row['ingredient_id']) echo "selected"; ?>>
                        <?php echo htmlspecialchars($row['name']); ?>
                    </option>
                <?php } ?>
            </select>

            <select name="reason">
                <option value="">All Reasons</option>
                <?php
                $reasons = ['Expired', 'Overproduction', 'Spoilage', 'Prep Mistake', 'Other'];
                foreach ($reasons as $r) {
                    $selected = ($filter_reason == $r) ? "selected" : "";
                    echo "<option value='" . htmlspecialchars($r) . "' $selected>" . htmlspecialchars($r) . "</option>";
                }
                ?>
            </select>

            From: <input type="date" name="date_from" value="<?php echo htmlspecialchars($filter_from); ?>">
            To: <input type="date" name="date_to" value="<?php echo htmlspecialchars($filter_to); ?>">

            <button type="submit">Filter</button>
            <a href="reports.php" class="clear-link">Clear Filters</a>
        </form>

        <h3>Results</h3>

        <?php if ($has_results) { ?>
        <div class="stat-cards">
            <div class="stat-card">
                <h4>Total Loss (Filtered)</h4>
                <p class="stat-number">RM <?php echo number_format($grand_total, 2); ?></p>
            </div>
        </div>

        <div style="padding: 0 24px; margin-bottom: 16px; display: flex; gap: 12px; align-items: center;">
            <a href="export_csv.php?<?php echo $export_query_string; ?>" class="empty-cta">Export to CSV</a>

            <form method="POST" class="delete-all-form" onsubmit="return confirm('This will permanently delete ALL waste logs, not just the filtered ones. This cannot be undone. Are you sure?')">
                <input type="hidden" name="delete_all_logs" value="1">
                <button type="submit" class="delete-all-btn">Delete All Logs</button>
            </form>
        </div>

        <table>
            <tr>
                <th>Ingredient</th>
                <th>Quantity</th>
                <th>Reason</th>
                <th>Loss (RM)</th>
                <th>Logged By</th>
                <th>Date</th>
                <th style="width: 90px;">Action</th>
            </tr>
            <?php foreach ($log_rows as $row) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['ingredient_name']); ?></td>
                <td><?php echo htmlspecialchars($row['quantity'] . " " . $row['unit']); ?></td>
                <td><?php echo htmlspecialchars($row['reason']); ?></td>
                <td><?php echo number_format($row['financial_loss'], 2); ?></td>
                <td><?php echo htmlspecialchars($row['staff_name']); ?></td>
                <td><?php echo date('d/m/Y, h:i A', strtotime($row['logged_at'])); ?></td>
                <td>
                    <form method="POST" class="delete-form" onsubmit="return confirm('Delete this waste log entry?')">
                        <input type="hidden" name="delete_log_id" value="<?php echo htmlspecialchars($row['log_id']); ?>">
                        <button type="submit" class="delete-btn">Delete</button>
                    </form>
                </td>
            </tr>
            <?php } ?>
        </table>
        <?php } else { ?>
        <div class="empty-state">
            <p>No waste logs match this filter. Try adjusting your search criteria.</p>
            <a href="reports.php" class="empty-cta">Clear Filters</a>
        </div>
        <?php } ?>
    </div>
</div>
</body>
</html>