<?php
require '../includes/db.php';
require '../includes/auth.php';

requireLogin();

/* UNREAD ALERT COUNT (FIX) */
$stmt = $db->prepare(
    "SELECT COUNT(*) FROM alerts
     WHERE user_id = ? AND is_read = 0"
);
$stmt->execute([currentUserId()]);
$unreadAlerts = (int) $stmt->fetchColumn();

$community_id = $_GET['id'] ?? null;
if (!$community_id) {
    die('Invalid community');
}

/* Fetch community */
$stmt = $db->prepare("SELECT * FROM communities WHERE id = ?");
$stmt->execute([$community_id]);
$community = $stmt->fetch();

if (!$community) {
    die('Community not found');
}

$user_id = currentUserId();
$isAdmin = isCommunityAdmin($db, $community_id, $user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo e($community['name']); ?> • DAO-Lite</title>

    <!-- MOBILE APP META (CRITICAL) -->
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

    <link rel="stylesheet" href="/public/assets/app.css">
</head>

<body>

<!-- Dummy nav disables reader mode -->
<nav class="mobile-nav" aria-hidden="true"></nav>

<header class="app-header">
    <?php echo e($community['name']); ?>
</header>

<main role="application">
<div class="container">

    <p><?php echo e($community['description']); ?></p>

    <div class="card">
        <strong>Treasury Wallet</strong><br>
        <code><?php echo e($community['treasury_wallet']); ?></code>
    </div>

    <!-- ✅ ACTIONS (UPDATED) -->
    <div class="actions">

        <?php if ($isAdmin): ?>
            <a class="btn" href="/public/create_invite.php?community_id=<?php echo $community_id; ?>">
                Create Invite Link
            </a>
        <?php endif; ?>

        <a class="btn primary" href="/public/create_payout_request.php?community_id=<?php echo $community_id; ?>">
            Request Payout
        </a>

    </div>

    <hr>

<?php
/* MEMBERS */
$stmt = $db->prepare(
    "SELECT u.id, u.username, cm.role
     FROM community_members cm
     JOIN users u ON u.id = cm.user_id
     WHERE cm.community_id = ?"
);
$stmt->execute([$community_id]);
$members = $stmt->fetchAll();
?>

<h3>Members</h3>
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

<hr>

<?php
/* PAYOUT REQUESTS */
$stmt = $db->prepare(
    "SELECT pr.*, u.username
     FROM payout_requests pr
     JOIN users u ON u.id = pr.requester_user_id
     WHERE pr.community_id = ?
     ORDER BY pr.created_at DESC"
);
$stmt->execute([$community_id]);
$requests = $stmt->fetchAll();

$totalAdmins = adminCount($db, $community_id);
$quorum = (int) ceil(($totalAdmins * 2) / 3);

$approvalStmt = $db->prepare(
    "SELECT COUNT(*) FROM approvals WHERE payout_request_id = ?"
);
?>

<h3>Payout Requests</h3>

<?php if (!$requests): ?>
    <p>No payout requests yet.</p>
<?php else: ?>
<?php foreach ($requests as $r): ?>
<?php
    $approvalStmt->execute([$r['id']]);
    $approvalCount = (int) $approvalStmt->fetchColumn();
?>
<div class="card">

    <strong>Amount:</strong> <?php echo e($r['amount']); ?><br><br>

    <strong>Recipient Wallet</strong><br>
    <code><?php echo e($r['recipient_wallet']); ?></code><br><br>

    <strong>Reason</strong><br>
    <?php echo e($r['reason']); ?><br><br>

    Requested by <strong><?php echo e($r['username']); ?></strong><br>

    Status:
    <span class="badge <?php echo $r['tx_verified'] ? 'paid' : e($r['status']); ?>">
        <?php echo $r['tx_verified'] ? 'PAID' : strtoupper($r['status']); ?>
    </span>
    <br><br>

    Approvals: <?php echo $approvalCount; ?> / <?php echo $totalAdmins; ?>
    (Quorum <?php echo $quorum; ?>)

    <br><br>

    <?php if ($isAdmin && $r['status'] === 'pending'): ?>
        <form method="post" action="/public/approve_payout.php" class="inline">
            <input type="hidden" name="request_id" value="<?php echo $r['id']; ?>">
            <input type="hidden" name="csrf" value="<?php echo csrfToken(); ?>">
            <button class="btn">Approve</button>
        </form>
    <?php endif; ?>

    <?php if ($isAdmin && $r['status'] === 'approved' && !$r['tx_verified']): ?>
        <form method="post" action="/public/submit_tx.php">
            <input type="hidden" name="request_id" value="<?php echo $r['id']; ?>">
            <input type="hidden" name="csrf" value="<?php echo csrfToken(); ?>">
            <input
                type="text"
                name="tx_hash"
                placeholder="Solana transaction hash"
                required
                style="width:100%; margin-top:8px;"
            >
            <button class="btn primary" style="margin-top:8px;">
                Verify & Mark Paid
            </button>
        </form>
    <?php endif; ?>

    <?php if ($r['tx_verified']): ?>
        <p style="margin-top:8px;">
            🔗
            <a target="_blank"
               href="https://explorer.solana.com/tx/<?php echo e($r['tx_hash']); ?>">
               View Transaction
            </a>
        </p>
    <?php endif; ?>

</div>
<?php endforeach; ?>
<?php endif; ?>

</div>
</main>

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

<footer class="app-footer"></footer>

</body>
</html>