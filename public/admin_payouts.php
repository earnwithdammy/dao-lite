<?php
require '../includes/db.php';
require '../includes/auth.php';

requireLogin();

if (!isDevAdmin()) {
    http_response_code(403);
    die('Access denied');
}

/* Handle delete */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int) $_POST['delete_id'];

    $stmt = $db->prepare("DELETE FROM payout_requests WHERE id = ?");
    $stmt->execute([$id]);

    redirect('/public/admin_payouts.php');
}

/* Fetch all payout requests */
$stmt = $db->query(
    "SELECT pr.*, u.username, c.name AS community_name
     FROM payout_requests pr
     JOIN users u ON u.id = pr.requester_user_id
     JOIN communities c ON c.id = pr.community_id
     ORDER BY pr.created_at DESC"
);

$payouts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin • Payout Requests</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/public/assets/app.css">
</head>

<body>

<header>Admin Panel</header>

<main class="container">

    <h2 class="page-title">All Payout Requests</h2>

    <section class="card">

        <?php if (!$payouts): ?>
            <p class="muted">No payout requests found.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Community</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Wallet</th>
                            <th>Tx</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php foreach ($payouts as $p): ?>
                        <tr>
                            <td><?php echo $p['id']; ?></td>
                            <td><?php echo e($p['username']); ?></td>
                            <td><?php echo e($p['community_name']); ?></td>
                            <td><?php echo e($p['amount']); ?> SOL</td>
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
                            <td class="mono">
                                <?php echo $p['tx_hash'] ? '✔' : '—'; ?>
                            </td>
                            <td>
                                <form method="post" onsubmit="return confirm('Delete this payout request?');">
                                    <input type="hidden" name="delete_id" value="<?php echo $p['id']; ?>">
                                    <button style="background:#ff4d4d;color:#000;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </section>

</main>

</body>
</html>