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

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $treasury_wallet = trim($_POST['treasury_wallet'] ?? '');

    if ($name === '') {
        $error = 'Community name is required';
    } elseif ($treasury_wallet === '') {
        $error = 'Treasury wallet is required';
    } elseif (!preg_match('/^[1-9A-HJ-NP-Za-km-z]{32,44}$/', $treasury_wallet)) {
        $error = 'Invalid Solana wallet address';
    } else {
        // Create community
        $stmt = $db->prepare(
            "INSERT INTO communities (name, description, treasury_wallet, created_by)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$name, $description, $treasury_wallet, $user_id]);

        $community_id = $db->lastInsertId();

        // Creator becomes admin
        $db->prepare(
            "INSERT INTO community_members (community_id, user_id, role)
             VALUES (?, ?, 'admin')"
        )->execute([$community_id, $user_id]);

        redirect('/public/community.php?id=' . $community_id);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Create Community • DAO-Lite</title>

    <!-- Prevent mobile reading mode -->
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

    <link rel="stylesheet" href="/public/assets/app.css">
</head>

<body>

<header class="app-header">
    Create Community
</header>

<main class="container">

    <div class="card">

        <h3>New Community</h3>
        <p class="muted">
            Create a new DAO-style community powered by Solana.
            You’ll automatically become the admin.
        </p>

        <?php if ($error): ?>
            <div class="card" style="border-color:#ff6b6b; color:#ff6b6b;">
                <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <form method="post">

            <label class="muted">Community Name</label>
            <input
                type="text"
                name="name"
                required
                placeholder="e.g. Solana Builders DAO"
            >

            <br><br>

            <label class="muted">Description</label>
            <textarea
                name="description"
                placeholder="What is this community about?"
                rows="4"
            ></textarea>

            <br><br>

            <label class="muted">Treasury Wallet</label>
            <input
                type="text"
                name="treasury_wallet"
                required
                placeholder="Solana treasury wallet address"
                class="mono"
            >

            <small class="muted">
                This wallet will be used for payouts and treasury tracking.
            </small>

            <br><br>

            <button type="submit">
                Create Community
            </button>

        </form>

    </div>

    <p style="margin-top:16px;">
        <a class="link" href="/public/dashboard.php">
            ← Back to Dashboard
        </a>
    </p>

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