<?php
require '../includes/db.php';
require '../includes/auth.php';
require '../includes/solana.php';

requireLogin();

/* ---------- ERROR RENDER (DESIGN ONLY) ---------- */
function renderError($message)
{
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <title>Action Failed • DAO-Lite</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="/public/assets/app.css">
    </head>
    <body>

    <header>Verification Failed</header>

    <main class="container" style="max-width:480px;">
        <section class="card" style="text-align:center;">
            <div style="font-size:42px; margin-bottom:12px;">⚠️</div>

            <h3>Transaction Error</h3>

            <p class="muted" style="margin:14px 0;">
                <?php echo e($message); ?>
            </p>

            <a href="javascript:history.back()" class="link">
                ← Go back and try again
            </a>
        </section>
    </main>

    </body>
    </html>
    <?php
    exit;
}
/* ---------------------------------------------- */

$request_id = $_POST['request_id'] ?? null;
$tx_hash    = trim($_POST['tx_hash'] ?? '');
$csrf       = $_POST['csrf'] ?? '';

if (!verifyCsrf($csrf)) {
    renderError('Invalid CSRF token. Please refresh and try again.');
}

if (!$request_id || !$tx_hash) {
    renderError('Invalid submission. Transaction hash is required.');
}

/* Fetch payout request */
$stmt = $db->prepare(
    "SELECT pr.*, c.treasury_wallet
     FROM payout_requests pr
     JOIN communities c ON c.id = pr.community_id
     WHERE pr.id = ? AND pr.status = 'approved' AND pr.tx_verified = 0"
);
$stmt->execute([$request_id]);
$request = $stmt->fetch();

if (!$request) {
    renderError('Invalid or already paid payout request.');
}

/* Verify on Solana */
$ok = verifySolanaTx(
    $tx_hash,
    $request['treasury_wallet'],
    $request['recipient_wallet'],
    (float) $request['amount']
);

if (!$ok) {
    renderError('Transaction does not match the treasury wallet or payout amount.');
}

/* Mark as PAID */
$db->prepare(
    "UPDATE payout_requests
     SET tx_hash = ?, tx_verified = 1
     WHERE id = ?"
)->execute([$tx_hash, $request_id]);

redirect('/public/community.php?id=' . $request['community_id']);