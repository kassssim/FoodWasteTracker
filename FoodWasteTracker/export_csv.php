<?php
include 'access_control.php';

if ($_SESSION['role'] != 'manager') {
    die("Access denied. Managers only.");
}

$filter_ingredient = isset($_GET['ingredient_id']) ? $_GET['ingredient_id'] : "";
$filter_reason = isset($_GET['reason']) ? $_GET['reason'] : "";
$filter_from = isset($_GET['date_from']) ? $_GET['date_from'] : "";
$filter_to = isset($_GET['date_to']) ? $_GET['date_to'] : "";

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

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=waste_logs_export_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');

fputcsv($output, ['Ingredient', 'Quantity', 'Unit', 'Reason', 'Loss (RM)', 'Logged By', 'Date']);

$grand_total = 0;
while ($row = mysqli_fetch_assoc($logs)) {
    fputcsv($output, [
        $row['ingredient_name'],
        $row['quantity'],
        $row['unit'],
        $row['reason'],
        number_format($row['financial_loss'], 2),
        $row['staff_name'],
        date('d/m/Y h:i A', strtotime($row['logged_at']))
    ]);
    $grand_total += $row['financial_loss'];
}

fputcsv($output, []);
fputcsv($output, ['', '', '', '', 'Total Loss:', number_format($grand_total, 2), '']);

fclose($output);
exit;
?>