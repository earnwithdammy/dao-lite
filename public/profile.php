<?php
require '../includes/db.php';
require '../includes/auth.php';

requireLogin();

$userId = currentUserId();

/* Fetch user (ONLY existing columns) */
$stmt = $db->prepare(
    "SELECT id, username, created_at
     FROM users
     WHERE id = ?"
);
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    die('User not found');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile • DAO-Lite</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/public/assets/app.css">
</head>

<body>

<header>My Profile</header>

<main class="container">

    <!-- PROFILE CARD -->
    <section class="card" style="text-align:center;">
        <div style="font-size:64px;">👤</div>

        <h2 style="margin-bottom:6px;">
            <?php echo e($user['username']); ?>
        </h2>

        <p class="muted">
            DAO-Lite Member
        </p>
    </section>

    <!-- ACCOUNT INFO -->
    <section class="card">
        <h3>Account</h3>

        <ul class="list">
            <li class="list-item">
                <span>User ID</span>
                <strong><?php echo e($user['id']); ?></strong>
            </li>

            <li class="list-item">
                <span>Joined</span>
                <strong>
                    <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                </strong>
            </li>
        </ul>
    </section>

    <!-- ACTIONS -->
    <section class="card">
        <h3>Actions</h3>

        <form method="post" action="/public/logout.php">
            <button
                type="submit"
                style="background:#ff6b6b;color:#000;"
            >
                🚪 Logout
            </button>
        </form>
    </section>

</main>

</body>
</html>