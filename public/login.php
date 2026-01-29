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
        $stmt = $db->prepare(
            "SELECT id, password_hash FROM users WHERE username = ?"
        );
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {

            // ✅ LOGIN SUCCESS (existing logic)
            $_SESSION['user_id'] = $user['id'];

            // ✅ NEW: AUTO-JOIN IF INVITED
            if (!empty($_SESSION['pending_invite'])) {
                $code = $_SESSION['pending_invite'];
                unset($_SESSION['pending_invite']);
                redirect('/public/join_community.php?code=' . $code);
            }

            // ✅ DEFAULT BEHAVIOR (unchanged)
            redirect('/public/dashboard.php');

        } else {
            $error = 'Invalid username or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login • DAO-Lite</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/public/assets/app.css">
</head>
<body>

<header>DAO-Lite</header>

<main class="container center-screen">
    <div class="card auth-card">

        <h2>Welcome Back</h2>
        <p class="muted">Sign in to your community</p>

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
                Login
            </button>
        </form>

        <div class="auth-footer">
            Don’t have an account?
            <a href="/public/register.php">Create one</a>
        </div>

    </div>
</main>

</body>
</html>