<?php
require '../includes/db.php';
require '../includes/auth.php';

requireLogin();

$user_id = currentUserId();
$community_id = $_GET['community_id'] ?? null;

if (!$community_id) {
    die('Community not specified');
}

/* Ensure membership */
$stmt = $db->prepare(
    "SELECT 1 FROM community_members
     WHERE community_id = ? AND user_id = ?"
);
$stmt->execute([$community_id, $user_id]);
if (!$stmt->fetch()) {
    die('You must join this community to request a payout');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (float) ($_POST['amount'] ?? 0);
    $wallet = trim($_POST['recipient_wallet'] ?? '');
    $reason = trim($_POST['reason'] ?? '');

    if ($amount <= 0) {
        $error = 'Invalid amount';
    } elseif ($wallet === '') {
        $error = 'Recipient wallet is required';
    } elseif (!preg_match('/^[1-9A-HJ-NP-Za-km-z]{32,44}$/', $wallet)) {
        $error = 'Invalid Solana wallet address';
    } elseif ($reason === '') {
        $error = 'Reason is required';
    } else {
        $stmt = $db->prepare(
            "INSERT INTO payout_requests
             (community_id, requester_user_id, amount, recipient_wallet, reason)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $community_id,
            $user_id,
            $amount,
            $wallet,
            $reason
        ]);

        redirect('/public/community.php?id=' . $community_id);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Create Payout Request • DAO-Lite</title>

    <!-- Prevent mobile reading mode -->
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

    <link rel="stylesheet" href="/public/assets/app.css">
</head>

<body>

<header class="app-header">
    Request Payout
</header>

<main class="container">

    <div class="card">

        <h3>Create Payout Request</h3>
        <p class="muted">
            Submit a payout request for this community. Amount is in <strong>SOL</strong>.
        </p>

        <?php if ($error): ?>
            <div class="card" style="border-color:#ff6b6b; color:#ff6b6b;">
                <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <form method="post" class="form">

            <label class="muted">Amount (SOL)</label>
            <input
                type="number"
                step="0.000000001"
                name="amount"
                required
                placeholder="e.g. 1.25"
            >

            <small class="muted">
                Enter amount in SOL (not USD or USDC)
            </small>

            <br><br>

            <label class="muted">Recipient Wallet</label>
            <input
                type="text"
                name="recipient_wallet"
                required
                placeholder="Solana wallet address"
                class="mono"
            >

            <br><br>

            <label class="muted">Reason</label>
            <textarea
                name="reason"
                required
                placeholder="Explain why this payout is needed…"
                rows="4"
            ></textarea>

            <br><br>

            <button type="submit">
                Submit Request
            </button>

        </form>

    </div>

    <p style="margin-top:16px;">
        <a class="link" href="/public/community.php?id=<?php echo $community_id; ?>">
            ← Back to Community
        </a>
    </p>

</main>
<!-- ✅ BOTTOM NAV (INSIDE BODY, ROW LAYOUT) -->
<nav class="app-nav">
    <a href="/public/dashboard.php" class="nav-item active">
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

    <a href="/public/alerts.php" class="nav-item">
        <span class="nav-icon">🔔</span>
        <span class="nav-label">Alerts</span>
    </a>

    <a href="/public/profile.php" class="nav-item">
        <span class="nav-icon">👤</span>
        <span class="nav-label">Profile</span>
    </a>
</nav>

</body>
</html>