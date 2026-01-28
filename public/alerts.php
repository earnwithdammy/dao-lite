<?php
require '../includes/db.php';
require '../includes/auth.php';

requireLogin();
$user_id = currentUserId();

$stmt = $db->prepare(
    "SELECT COUNT(*) FROM alerts
     WHERE user_id = ? AND is_read = 0"
);
$stmt->execute([currentUserId()]);
$unreadAlerts = $stmt->fetchColumn();

/* Mark all as read */
if (isset($_GET['read'])) {
    $db->prepare(
        "UPDATE alerts SET is_read = 1 WHERE user_id = ?"
    )->execute([$user_id]);

    redirect('/public/alerts.php');
}

/* Fetch alerts */
$stmt = $db->prepare(
    "SELECT * FROM alerts
     WHERE user_id = ?
     ORDER BY created_at DESC"
);
$stmt->execute([$user_id]);
$alerts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Alerts • DAO-Lite</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/public/assets/app.css">
</head>
<body>

<header>🔔 Alerts</header>

<main class="container">

    <div style="text-align:right; margin-bottom:10px;">
        <a class="link" href="?read=1">Mark all as read</a>
    </div>

    <?php if (!$alerts): ?>
        <p class="muted">No alerts yet.</p>
    <?php else: ?>
        <ul class="list">
            <?php foreach ($alerts as $a): ?>
                <li class="list-item" style="<?php echo $a['is_read'] ? 'opacity:.6' : ''; ?>">
                    <div>
                        <strong><?php echo e($a['title']); ?></strong><br>
                        <span class="muted"><?php echo e($a['message']); ?></span><br>
                        <small class="muted">
                            <?php echo date('M j, H:i', strtotime($a['created_at'])); ?>
                        </small>
                    </div>

                    <?php if ($a['link']): ?>
                        <a class="link" href="<?php echo e($a['link']); ?>">Open</a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

</main>
<!-- ✅ BOTTOM NAV (INSIDE BODY, ROW LAYOUT) -->
<nav class="app-nav">

    <a href="/public/dashboard.php" class="nav-item">
        <span class="nav-icon">🏠</span>
        <span class="nav-label">Home</span>
    </a>

    <a href="/public/communities.php" class="nav-item">
        <span class="nav-icon">👥</span>
        <span class="nav-label">Communities</span>
    </a>

    <a href="/public/create_community.php" class="nav-item nav-main">
        <span class="nav-icon">＋</span>
    </a>

    <!-- 🔔 ALERTS -->
    <a href="/public/alerts.php" class="nav-item" style="position:relative;">
        <span class="nav-icon">🔔</span>
        <span class="nav-label">Alerts</span>

        <?php if ($unreadAlerts > 0): ?>
            <span style="
                position:absolute;
                top:6px;
                right:22%;
                background:#ff4d4d;
                color:#fff;
                font-size:10px;
                padding:2px 6px;
                border-radius:999px;
                font-weight:700;
            ">
                <?php echo $unreadAlerts; ?>
            </span>
        <?php endif; ?>
    </a>

    <a href="/public/profile.php" class="nav-item">
        <span class="nav-icon">👤</span>
        <span class="nav-label">Profile</span>
    </a>

</nav>

</body>
</html>