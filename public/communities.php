<?php
require '../includes/db.php';
require '../includes/auth.php';

requireLogin();

$user_id = currentUserId();

/* UNREAD ALERT COUNT */
$stmt = $db->prepare(
    "SELECT COUNT(*) FROM alerts
     WHERE user_id = ? AND is_read = 0"
);
$stmt->execute([$user_id]);
$unreadAlerts = (int) $stmt->fetchColumn();

/* Fetch all communities */
$stmt = $db->query(
    "SELECT c.*, 
        (SELECT COUNT(*) 
         FROM community_members cm 
         WHERE cm.community_id = c.id) AS member_count
     FROM communities c
     ORDER BY c.created_at DESC"
);
$communities = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Communities · DAO-Lite</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/public/assets/app.css">
</head>
<body>

<header>DAO-Lite</header>

<div class="container">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <h2 class="page-title">Communities</h2>

        <a href="/public/create_community.php" class="link">
            ➕ Create
        </a>
    </div>

    <?php if (!$communities): ?>
        <div class="card muted" style="text-align:center;">
            No communities yet.
        </div>
    <?php else: ?>

        <?php foreach ($communities as $c): ?>

            <?php
            $isMember = isCommunityMember($db, $c['id'], $user_id);
            $isPending = hasPendingJoinRequest($db, $c['id'], $user_id);
            ?>

            <div class="card">

                <div style="display:flex;justify-content:space-between;align-items:start;gap:12px;">
                    <div>
                        <h3 style="margin-bottom:6px;">
                            <?php echo e($c['name']); ?>
                        </h3>

                        <p class="muted" style="margin:0 0 10px;">
                            <?php echo e($c['description']); ?>
                        </p>
                    </div>

                    <span class="badge approved">
                        <?php echo (int)$c['member_count']; ?> members
                    </span>
                </div>

                <div class="muted mono" style="font-size:12px;margin-bottom:12px;">
                    Treasury: <?php echo e($c['treasury_wallet']); ?>
                </div>

                <div style="display:flex;gap:10px;">

                    <!-- OPEN -->
                    <a
                        href="/public/community.php?id=<?php echo $c['id']; ?>"
                        class="link"
                        style="flex:1;text-align:center;padding:10px;border-radius:10px;background:#151824;border:1px solid #23283a;"
                    >
                        Open
                    </a>

                    <!-- JOIN STATE -->
                    <?php if ($isMember): ?>

                        <!-- Already a member: nothing -->

                    <?php elseif ($isPending): ?>

                        <span
                            style="flex:1;text-align:center;padding:10px;
                                   border-radius:10px;
                                   background:#23283a;
                                   color:#aaa;
                                   font-weight:600;"
                        >
                            Pending
                        </span>

                    <?php else: ?>

                        <form method="post" action="/public/request_join.php" style="flex:1;">
                            <input type="hidden" name="community_id" value="<?php echo $c['id']; ?>">
                            <input type="hidden" name="csrf" value="<?php echo csrfToken(); ?>">
                            <button
                                type="submit"
                                style="width:100%;
                                       padding:10px;
                                       border-radius:10px;
                                       background:linear-gradient(135deg,#9945ff,#14f195);
                                       border:none;
                                       color:#000;
                                       font-weight:700;"
                            >
                                Request to Join
                            </button>
                        </form>

                    <?php endif; ?>

                </div>

            </div>
        <?php endforeach; ?>

    <?php endif; ?>

</div>

<!-- ✅ BOTTOM NAV -->
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