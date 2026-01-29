<?php
require '../includes/db.php';
require '../includes/auth.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'All fields are required';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $db->prepare(
                "INSERT INTO users (username, password_hash)
                 VALUES (?, ?)"
            );
            $stmt->execute([$username, $hash]);

            // ✅ AUTO-LOGIN AFTER REGISTER (no logic break)
            $_SESSION['user_id'] = (int) $db->lastInsertId();

            // ✅ AUTO-JOIN IF INVITED
            if (!empty($_SESSION['pending_invite'])) {
                $code = $_SESSION['pending_invite'];
                unset($_SESSION['pending_invite']);
                redirect('/public/join_community.php?code=' . $code);
            }

            // ✅ DEFAULT BEHAVIOR (unchanged intent)
            redirect('/public/dashboard.php');

        } catch (PDOException $e) {
            $error = 'Username already exists';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register • DAO-Lite</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/public/assets/app.css">
</head>
<body>

<header>DAO-Lite</header>

<main class="container center-screen">
    <div class="card auth-card">

        <h2>Create Account</h2>
        <p class="muted">Join a Solana-powered community</p>

        <?php if ($error): ?>
            <div class="alert error">
                <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <form method="post" class="auth-form">

            <label for="username">Username</label>
            <input
                id="username"
                type="text"
                name="username"
                placeholder="yourname"
                required
                autofocus
            >

            <label for="password">Password</label>
            <input
                id="password"
                type="password"
                name="password"
                placeholder="••••••••"
                required
            >

            <button type="submit" class="btn primary">
                Create Account
            </button>
        </form>

        <div class="auth-footer">
            Already have an account?
            <a href="/public/login.php">Login</a>
        </div>

    </div>
</main>

</body>
</html>