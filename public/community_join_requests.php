<?php
require '../includes/db.php';
require '../includes/auth.php';

requireLogin();

/* UNREAD ALERT COUNT */
$stmt = $db->prepare(
    "SELECT COUNT(*) FROM alerts
     WHERE user_id = ? AND is_read = 0"
);
$stmt->execute([currentUserId()]);
$unreadAlerts = (int)$stmt->fetchColumn();

$community_id = $_GET['id'] ?? null;
if (!$community_id) die('Invalid community');

$isAdmin = isCommunityAdmin($db, $community_id, currentUserId());
if (!$isAdmin) die('Access denied');

$stmt = $db->prepare("SELECT * FROM communities WHERE id = ?");
$stmt->execute([$community_id]);
$community = $stmt->fetch();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Join Requests • <?php echo e($community['name']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/public/assets/app.css">
</head>
<body>

<header class="app-header"><?php echo e($community['name']); ?></header>

<main>
<div class="container">

<div class="tabs">
    <a class="tab" href="/public/community.php?id=<?php echo $community_id; ?>">Overview</a>
    <a class="tab" href="/public/community_members.php?id=<?php echo $community_id; ?>">Members</a>
    <a class="tab" href="/public/community_payouts.php?id=<?php echo $community_id; ?>">Payouts</a>
    <a class="tab active">Join Requests</a>
</div>

<?php
$stmt = $db->prepare(
    "SELECT jr.id, u.username
     FROM join_requests jr
     JOIN users u ON u.id = jr.user_id
     WHERE jr.community_id = ? AND jr.status = 'pending'"
);
$stmt->execute([$community_id]);
$joinRequests = $stmt->fetchAll();
?>

<ul class="list">
<?php foreach ($joinRequests as $jr): ?>
<li style="display:flex; align-items:center; justify-content:space-between; gap:10px;">

    <strong><?php echo e($jr['username']); ?></strong>

    <div style="display:flex; gap:6px;">

        <!-- APPROVE -->
        <form method="post" action="/public/approve_join.php">
            <input type="hidden" name="request_id" value="<?php echo $jr['id']; ?>">
            <input type="hidden" name="csrf" value="<?php echo csrfToken(); ?>">
            <button class="btn small">Approve</button>
        </form>

        <!-- REJECT -->
        <form method="post" action="/public/reject_join.php">
            <input type="hidden" name="request_id" value="<?php echo $jr['id']; ?>">
            <input type="hidden" name="csrf" value="<?php echo csrfToken(); ?>">
            <button class="btn small danger">Reject</button>
        </form>

    </div>
</li>
<?php endforeach; ?>
</ul>

</div>
</main>
<!-- BOTTOM NAV -->
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