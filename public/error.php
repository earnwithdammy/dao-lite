<?php
require '../includes/auth.php';
requireLogin();

$messages = [
    'invalid_tx' => 'Transaction does not match the treasury wallet or amount.',
    'invalid_csrf' => 'Security verification failed. Please try again.',
    'invalid_request' => 'Invalid or already paid request.'
];

$key = $_GET['msg'] ?? '';
$message = $messages[$key] ?? 'Something went wrong.';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Error • DAO-Lite</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/public/assets/app.css">
</head>
<body>

<header>Action Failed</header>

<main class="container" style="max-width:480px;">

    <section class="card" style="text-align:center;">

        <div style="font-size:40px; margin-bottom:10px;">⚠️</div>

        <h3>Verification Failed</h3>

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