<?php
require $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

requireLogin();

$community_id = (int) ($_GET['community_id'] ?? 0);
$user_id = currentUserId();

if (!$community_id) {
    die('Invalid community');
}

if (!isCommunityAdmin($db, $community_id, $user_id)) {
    http_response_code(403);
    die('Not allowed');
}

// SAFE CODE GENERATION (InfinityFree compatible)
$code = substr(md5(uniqid((string) time(), true)), 0, 8);

$stmt = $db->prepare(
    "INSERT INTO community_invites (community_id, code, created_by)
     VALUES (?, ?, ?)"
);
$stmt->execute([$community_id, $code, $user_id]);

$inviteLink = "https://YOURDOMAIN.infinityfreeapp.com/public/join_community.php?code=" . $code;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Create Invite • DAO-Lite</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/public/assets/app.css">
</head>

<body>
<header class="app-header">Invite Link</header>

<main class="container">
    <div class="card">
        <p>Share this invite link:</p>

        <input type="text" id="inviteLink" value="<?php echo e($inviteLink); ?>" readonly>

        <button class="btn primary" onclick="copyInvite()">Copy Link</button>

        <p id="copied" style="display:none;color:green;">Copied ✔</p>
    </div>
</main>

<script>
function copyInvite() {
    const el = document.getElementById('inviteLink');
    el.select();
    document.execCommand('copy');
    document.getElementById('copied').style.display = 'block';
}
</script>
</body>
</html>