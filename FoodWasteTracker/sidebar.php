<div class="sidebar">
    <div class="sidebar-brand">
        <img src="logo.png" alt="Logo" class="brand-logo">
        <span>Food Waste Analytics</span>
    </div>
    <div class="sidebar-user">
        <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong>
        <?php echo htmlspecialchars(ucfirst($_SESSION['role'])); ?>
    </div>
    <a href="log_waste.php" class="<?php echo $current_page == 'log_waste.php' ? 'active' : ''; ?>">Log Waste</a>
    <?php if ($_SESSION['role'] == 'manager') { ?>
        <a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
        <a href="settings.php" class="<?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">Settings</a>
        <a href="reports.php" class="<?php echo $current_page == 'reports.php' ? 'active' : ''; ?>">Reports</a>
    <?php } ?>
    <a href="logout.php" class="logout-link">Logout</a>
</div>