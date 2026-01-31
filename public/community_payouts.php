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
        .payout-card {
            background: #141428;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 12px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.25);
            font-size: 13px;
        }

        .payout-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
        }

        .payout-label {
            font-size: 11px;
            color: #a5a5ff;
            font-weight: 600;
        }

        .wallet {
            font-family: monospace;
            font-size: 12px;
            max-width: 160px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .copy-btn {
            background: #2d2df0;
            border: none;
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 11px;
            color: #fff;
            cursor: pointer;
        }

        /* ===== REASON EXPAND ===== */

        .reason {
            font-size: 12px;
            color: #ddd;
            max-height: 40px;
            overflow: hidden;
            transition: max-height 0.25s ease;
        }

        .reason.expanded {
            max-height: 500px;
        }

        .reason-toggle {
            font-size: 11px;
            color: #7aa2ff;
            cursor: pointer;
            margin-top: 4px;
            display: inline-block;
        }

        .payout-footer {
            font-size: 11px;
            color: #aaa;
            margin-top: 6px;
        }

        .action-row {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }

        .action-row .btn {
            width: 100%;
            font-size: 12px;
            padding: 6px;
        }

        input.tx-input {
            width: 100%;
            margin-top: 6px;
            padding: 6px;
            font-size: 12px;
            border-radius: 6px;
            border: 1px solid #333;
            background: #0f0f1f;
            color: #fff;
        }
    </style>
</head>

<body>

<header class="app-header">
    <?php echo e($community['name']); ?>
</header>

<main role="application">
<div class="container">

<div class="tabs">
    <a class="tab" href="/public/community.php?id=<?php echo $community_id; ?>">Overview</a>
    <a class="tab" href="/public/community_members.php?id=<?php echo $community_id; ?>">Members</a>
    <a class="tab active">Payouts</a>
    <?php if ($isAdmin): ?>
        <a class="tab" href="/public/community_join_requests.php?id=<?php echo $community_id; ?>">Join Requests</a>
    <?php endif; ?>
</div>

<?php
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

<?php foreach ($requests as $r): ?>
<?php
    $approvalStmt->execute([$r['id']]);
    $approvalCount = (int)$approvalStmt->fetchColumn();
?>

<div class="payout-card">

    <div class="payout-row">
        <div>
            <div class="payout-label">Amount</div>
            <strong><?php echo e($r['amount']); ?></strong>
        </div>

        <span class="badge <?php echo $r['tx_verified'] ? 'paid' : e($r['status']); ?>">
            <?php echo $r['tx_verified'] ? 'PAID' : strtoupper($r['status']); ?>
        </span>
    </div>

    <div class="payout-row">
        <div>
            <div class="payout-label">Wallet</div>
            <div class="wallet"><?php echo e($r['recipient_wallet']); ?></div>
        </div>
        <button class="copy-btn" data-copy="<?php echo e($r['recipient_wallet']); ?>">Copy</button>
    </div>

    <div class="payout-label">Reason</div>
    <div class="reason"><?php echo e($r['reason']); ?></div>
    <span class="reason-toggle">Read more</span>

    <div class="payout-footer">
        Requested by <strong><?php echo e($r['username']); ?></strong><br>
        Approvals: <?php echo $approvalCount; ?> / <?php echo $totalAdmins; ?> (Quorum <?php echo $quorum; ?>)
    </div>

    <?php if ($isAdmin && $r['status'] === 'pending'): ?>
    <div class="action-row">

        <!-- APPROVE -->
        <form method="post" action="/public/approve_payout.php">
            <input type="hidden" name="request_id" value="<?php echo $r['id']; ?>">
            <input type="hidden" name="csrf" value="<?php echo csrfToken(); ?>">
            <button class="btn">Approve</button>
        </form>

        <!-- REJECT -->
        <form method="post" action="/public/reject_payout.php">
            <input type="hidden" name="request_id" value="<?php echo $r['id']; ?>">
            <input type="hidden" name="csrf" value="<?php echo csrfToken(); ?>">
            <button class="btn danger">Reject</button>
        </form>

    </div>
<?php endif; ?>

    <?php if ($isAdmin && $r['status'] === 'approved' && !$r['tx_verified']): ?>
        <form method="post" action="/public/submit_tx.php">
            <input type="hidden" name="request_id" value="<?php echo $r['id']; ?>">
            <input type="hidden" name="csrf" value="<?php echo csrfToken(); ?>">
            <input type="text" name="tx_hash" class="tx-input" placeholder="Solana transaction hash" required>
            <button class="btn primary" style="margin-top:6px;">Verify & Mark Paid</button>
        </form>
    <?php endif; ?>

</div>
<?php endforeach; ?>

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

<script>
document.addEventListener('click', function (e) {

    if (e.target.classList.contains('copy-btn')) {
        navigator.clipboard.writeText(e.target.dataset.copy);
        e.target.textContent = 'Copied';
        setTimeout(() => e.target.textContent = 'Copy', 1200);
    }

    if (e.target.classList.contains('reason-toggle')) {
        const reason = e.target.previousElementSibling;
        reason.classList.toggle('expanded');
        e.target.textContent = reason.classList.contains('expanded')
            ? 'Show less'
            : 'Read more';
    }
});
</script>

</body>
</html>