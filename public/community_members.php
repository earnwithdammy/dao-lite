<?php
require '../includes/db.php';
require '../includes/auth.php';

requireLogin();

$stmt = $db->prepare(
    "SELECT COUNT(*) FROM alerts WHERE user_id = ? AND is_read = 0"
);
$stmt->execute([currentUserId()]);
$unreadAlerts = (int)$stmt->fetchColumn();

$community_id = $_GET['id'] ?? null;
if (!$community_id) die('Invalid community');

$stmt = $db->prepare("SELECT * FROM communities WHERE id = ?");
$stmt->execute([$community_id]);
$community = $stmt->fetch();
if (!$community) die('Community not found');

$isAdmin = isCommunityAdmin($db, $community_id, currentUserId());
?>
<!DOCTYPE html>
<html>
<head>
    <title>Members • <?php echo e($community['name']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/public/assets/app.css">
</head>
<body>

<header class="app-header"><?php echo e($community['name']); ?></header>

<main>
<div class="container">

<div class="tabs">
    <a class="tab" href="/public/community.php?id=<?php echo $community_id; ?>">Overview</a>
    <a class="tab active">Members</a>
    <?php if ($isAdmin): ?>
        <a class="tab" href="/public/community_join_requests.php?id=<?php echo $community_id; ?>">Join Requests</a>
        <a class="tab" href="/public/community_payouts.php?id=<?php echo $community_id; ?>">Payouts</a>
    <?php endif; ?>
</div>

<?php
$stmt = $db->prepare(
    "SELECT u.id, u.username, cm.role
     FROM community_members cm
     JOIN users u ON u.id = cm.user_id
     WHERE cm.community_id = ?"
);
$stmt->execute([$community_id]);
$members = $stmt->fetchAll();
?>

<ul class="list">
<?php foreach ($members as $m): ?>
<li>
    <?php echo e($m['username']); ?>
    <span class="badge"><?php echo e($m['role']); ?></span>

    <?php if ($isAdmin && $m['role'] === 'member'): ?>
    <form method="post" action="/public/promote_admin.php" class="inline">
        <input type="hidden" name="community_id" value="<?php echo $community_id; ?>">
        <input type="hidden" name="user_id" value="<?php echo $m['id']; ?>">
        <input type="hidden" name="csrf" value="<?php echo csrfToken(); ?>">
        <button class="btn small">Make Admin</button>
    </form>
    <?php endif; ?>
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