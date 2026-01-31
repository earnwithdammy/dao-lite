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
    <title>Payouts • <?php echo e($community['name']); ?> • DAO-Lite</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <link rel="stylesheet" href="/public/assets/app.css">

    <style>
        .action-row {
            display: flex;
            gap: 12px;
            margin-top: 8px;
        }
        .action-row form {
            flex: 1;
        }
        .action-row .btn {
            width: 100%;
        }
    </style>
</head>

<body>

<header class="app-header">
    <?php echo e($community['name']); ?>
</header>

<main role="application">
<div class="container">

<!-- TABS -->
<div class="tabs">
    <a class="tab" href="/public/community.php?id=<?php echo $community_id; ?>">Overview</a>
    <a class="tab" href="/public/community_members.php?id=<?php echo $community_id; ?>">Members</a>
    <a class="tab active">Payouts</a>
    <?php if ($isAdmin): ?>
        <a class="tab" href="/public/community_join_requests.php?id=<?php echo $community_id; ?>">Join Requests</a>
    <?php endif; ?>
</div>

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
$quorum = (int)ceil(($totalAdmins * 2) / 3);

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
    $approvalCount = (int)$approvalStmt->fetchColumn();
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

    <!-- ADMIN ACTIONS -->
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
            <a
                href="https://explorer.solana.com/tx/<?php echo e($r['tx_hash']); ?>"
                target="_blank"
            >
                View Transaction
            </a>
        </p>
    <?php endif; ?>

</div>

<?php endforeach; ?>
<?php endif; ?>

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