<?php
require '../includes/db.php';
require '../includes/auth.php';

requireLogin();
$userId = currentUserId();

$stmt = $db->prepare(
    "SELECT COUNT(*) FROM alerts
     WHERE user_id = ? AND is_read = 0"
);
$stmt->execute([currentUserId()]);
$unreadAlerts = $stmt->fetchColumn();

/* Communities joined */
$stmt = $db->prepare(
    "SELECT c.id, c.name, cm.role
     FROM community_members cm
     JOIN communities c ON c.id = cm.community_id
     WHERE cm.user_id = ?"
);
$stmt->execute([$userId]);
$communities = $stmt->fetchAll();

/* Payout requests by user */
$stmt = $db->prepare(
    "SELECT pr.*, c.name AS community_name
     FROM payout_requests pr
     JOIN communities c ON c.id = pr.community_id
     WHERE pr.requester_user_id = ?
     ORDER BY pr.created_at DESC"
);
$stmt->execute([$userId]);
$payouts = $stmt->fetchAll();

/* Stats */
$stats = [
    'pending'   => 0,
    'approved'  => 0,
    'paid'      => 0,
    'rejected'  => 0
];

foreach ($payouts as $p) {
    if ($p['tx_verified']) {
        $stats['paid']++;
    } else {
        $stats[$p['status']]++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard • DAO-Lite</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <link rel="stylesheet" href="/public/assets/app.css">
</head>

<body>

<header>DAO-Lite</header>

<main class="container">

    <h2 class="page-title">My Dashboard</h2>

    <!-- STATS -->
    <section class="stats-grid">
        <div class="stat-card pending">
            <span>Pending</span>
            <strong><?php echo $stats['pending']; ?></strong>
        </div>

        <div class="stat-card approved">
            <span>Approved</span>
            <strong><?php echo $stats['approved']; ?></strong>
        </div>

        <div class="stat-card paid">
            <span>Paid</span>
            <strong><?php echo $stats['paid']; ?></strong>
        </div>

        <div class="stat-card rejected">
            <span>Rejected</span>
            <strong><?php echo $stats['rejected']; ?></strong>
        </div>
    </section>

    <!-- COMMUNITIES -->
    <section class="card">
        <h3>My Communities</h3>

        <?php if (!$communities): ?>
            <p class="muted">You haven’t joined any communities yet.</p>
        <?php else: ?>
            <ul class="list">
                <?php foreach ($communities as $c): ?>
                    <li class="list-item">
                        <div>
                            <strong><?php echo e($c['name']); ?></strong>
                            <span class="muted"> (<?php echo e($c['role']); ?>)</span>
                        </div>
                        <a class="link" href="/public/community.php?id=<?php echo $c['id']; ?>">
                            Open →
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <!-- PAYOUT REQUESTS -->
    <section class="card">
        <h3>My Payout Requests</h3>

        <?php if (!$payouts): ?>
            <p class="muted">No payout requests yet.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Community</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Wallet</th>
                            <th>Tx</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payouts as $p): ?>
                            <tr>
                                <td><?php echo e($p['community_name']); ?></td>
                                <td><?php echo e($p['amount']); ?></td>
                                <td>
                                    <?php if ($p['tx_verified']): ?>
                                        <span class="badge paid">Paid</span>
                                    <?php else: ?>
                                        <span class="badge <?php echo e($p['status']); ?>">
                                            <?php echo ucfirst($p['status']); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="mono">
                                    <?php echo e(substr($p['recipient_wallet'], 0, 6)); ?>…
                                </td>
                                <td>
                                    <?php if ($p['tx_verified'] && $p['tx_hash']): ?>
                                        <a
                                            class="link"
                                            target="_blank"
                                            href="https://explorer.solana.com/tx/<?php echo e($p['tx_hash']); ?>"
                                        >
                                            View
                                        </a>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

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