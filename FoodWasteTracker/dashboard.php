<?php
include 'access_control.php';

if ($_SESSION['role'] != 'manager') {
    die("Access denied. Managers only.");
}

$current_page = basename($_SERVER['PHP_SELF']);

$total_result = mysqli_query($conn, "SELECT SUM(financial_loss) AS total FROM waste_logs");
$total_row = mysqli_fetch_assoc($total_result);
$total_loss = $total_row['total'] ? $total_row['total'] : 0;

$count_result = mysqli_query($conn, "SELECT COUNT(*) AS total_logs FROM waste_logs");
$count_row = mysqli_fetch_assoc($count_result);
$total_logs = $count_row['total_logs'];

$reason_result = mysqli_query($conn, "SELECT reason, COUNT(*) AS cnt FROM waste_logs GROUP BY reason ORDER BY cnt DESC LIMIT 1");
$reason_row = mysqli_fetch_assoc($reason_result);
$top_reason = $reason_row ? $reason_row['reason'] : "N/A";

$has_data = $total_logs > 0;

$this_week = mysqli_query($conn, "
    SELECT SUM(financial_loss) AS total FROM waste_logs 
    WHERE logged_at >= NOW() - INTERVAL 7 DAY
");
$this_week_row = mysqli_fetch_assoc($this_week);
$this_week_loss = $this_week_row['total'] ? $this_week_row['total'] : 0;

$last_week = mysqli_query($conn, "
    SELECT SUM(financial_loss) AS total FROM waste_logs 
    WHERE logged_at >= NOW() - INTERVAL 14 DAY AND logged_at < NOW() - INTERVAL 7 DAY
");
$last_week_row = mysqli_fetch_assoc($last_week);
$last_week_loss = $last_week_row['total'] ? $last_week_row['total'] : 0;

$trend_direction = "same";
$trend_percentage = 0;
if ($last_week_loss > 0) {
    $trend_percentage = round((($this_week_loss - $last_week_loss) / $last_week_loss) * 100);
    $trend_direction = $trend_percentage > 0 ? "up" : ($trend_percentage < 0 ? "down" : "same");
} elseif ($this_week_loss > 0) {
    $trend_direction = "up";
    $trend_percentage = 100;
}

$top_ingredients = mysqli_query($conn, "
    SELECT COALESCE(ingredients.name, 'Deleted Ingredient') AS name, SUM(waste_logs.financial_loss) AS total_loss
    FROM waste_logs
    LEFT JOIN ingredients ON waste_logs.ingredient_id = ingredients.ingredient_id
    GROUP BY COALESCE(ingredients.name, 'Deleted Ingredient')
    ORDER BY total_loss DESC
    LIMIT 5
");

$ingredient_labels = [];
$ingredient_values = [];
$top_ingredient_name = "";
$top_ingredient_value = 0;
$first = true;
while ($row = mysqli_fetch_assoc($top_ingredients)) {
    $ingredient_labels[] = $row['name'];
    $ingredient_values[] = $row['total_loss'];
    if ($first) {
        $top_ingredient_name = $row['name'];
        $top_ingredient_value = $row['total_loss'];
        $first = false;
    }
}

$by_reason = mysqli_query($conn, "
    SELECT reason, SUM(financial_loss) AS total_loss
    FROM waste_logs
    GROUP BY reason
");

$reason_labels = [];
$reason_values = [];
$top_reason_value = 0;
while ($row = mysqli_fetch_assoc($by_reason)) {
    $reason_labels[] = $row['reason'];
    $reason_values[] = $row['total_loss'];
    if ($row['reason'] == $top_reason) {
        $top_reason_value = $row['total_loss'];
    }
}

$insight = "";
if ($has_data && $total_loss > 0) {
    $reason_percentage = round(($top_reason_value / $total_loss) * 100);
    $ingredient_percentage = round(($top_ingredient_value / $total_loss) * 100);

    if ($reason_percentage < 50 && $ingredient_percentage < 40) {
        $insight = "Your waste losses are spread across multiple causes. No single dominant issue detected yet — keep logging to track trends.";
    } elseif ($ingredient_percentage > $reason_percentage) {
        $insight = "<strong>$top_ingredient_name</strong> alone makes up <strong>{$ingredient_percentage}%</strong> of your financial losses. This ingredient may need better portion control or storage.";
    } else {
        $insight = "<strong>$top_reason</strong> accounts for <strong>{$reason_percentage}%</strong> of your total losses. Consider reviewing this area first for the biggest impact.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="app-layout">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <h2>Dashboard</h2>

        <?php if ($has_data && $insight) { ?>
        <div class="insight-banner">
            <span class="insight-text"><?php echo $insight; ?></span>
        </div>
        <?php } ?>

        <div class="stat-cards">
            <div class="stat-card">
                <h4>Total Financial Loss</h4>
                <p class="stat-number">RM <?php echo number_format($total_loss, 2); ?></p>
            </div>
            <div class="stat-card">
                <h4>Total Waste Logs</h4>
                <p class="stat-number"><?php echo $total_logs; ?></p>
            </div>
            <div class="stat-card">
                <h4>Top Reason</h4>
                <p class="stat-number"><?php echo htmlspecialchars($top_reason); ?></p>
            </div>
            <div class="stat-card">
                <h4>This Week vs Last Week</h4>
                <?php if ($trend_direction == "up") { ?>
                    <p class="stat-number trend-up">Up <?php echo abs($trend_percentage); ?>%</p>
                <?php } elseif ($trend_direction == "down") { ?>
                    <p class="stat-number trend-down">Down <?php echo abs($trend_percentage); ?>%</p>
                <?php } else { ?>
                    <p class="stat-number">No change</p>
                <?php } ?>
            </div>
        </div>

        <?php if ($has_data) { ?>
        <div class="chart-grid">
            <div class="chart-widget">
                <h3>Top Wasted Ingredients (by cost)</h3>
                <canvas id="ingredientChart"></canvas>
            </div>
            <div class="chart-widget">
                <h3>Waste by Reason (by cost)</h3>
                <canvas id="reasonChart"></canvas>
            </div>
        </div>
        <?php } else { ?>
        <div class="empty-state">
            <p>No waste logs yet. Start logging waste to see analytics here.</p>
            <a href="log_waste.php" class="empty-cta">Log Your First Waste Entry</a>
        </div>
        <?php } ?>
    </div>
</div>

<script>
    const ingredientLabels = <?php echo json_encode($ingredient_labels); ?>;
    const ingredientValues = <?php echo json_encode($ingredient_values); ?>;

    new Chart(document.getElementById('ingredientChart'), {
        type: 'bar',
        data: {
            labels: ingredientLabels,
            datasets: [{
                label: 'Loss (RM)',
                data: ingredientValues,
                backgroundColor: 'rgba(220, 53, 69, 0.7)'
            }]
        },
        options: {
            maintainAspectRatio: false
        }
    });

    const reasonLabels = <?php echo json_encode($reason_labels); ?>;
    const reasonValues = <?php echo json_encode($reason_values); ?>;

    new Chart(document.getElementById('reasonChart'), {
        type: 'pie',
        data: {
            labels: reasonLabels,
            datasets: [{
                data: reasonValues,
                backgroundColor: ['#dc3545', '#fd7e14', '#ffc107', '#20c997', '#0d6efd']
            }]
        },
        options: {
            maintainAspectRatio: false
        }
    });
</script>
</body>
</html>