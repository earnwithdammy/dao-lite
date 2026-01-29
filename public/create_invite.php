<?php
require '../includes/db.php';
require '../includes/auth.php';

requireLogin();

$community_id = (int) ($_GET['community_id'] ?? 0);
$user_id = currentUserId();

if (!isCommunityAdmin($db, $community_id, $user_id)) {
    http_response_code(403);
    die('Not allowed');
}

// generate short code
$code = bin2hex(random_bytes(4)); // 8 chars

$stmt = $db->prepare(
    "INSERT INTO community_invites (community_id, code, created_by)
     VALUES (?, ?, ?)"
);
$stmt->execute([$community_id, $code, $user_id]);

$inviteLink = "http://localhost:8000/public/join_community.php?code=" . $code;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Invite • DAO-Lite</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="/public/assets/app.css">

    <style>
        .invite-card {
            background: #fff;
            padding: 16px;
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0,0,0,.08);
            max-width: 420px;
            margin: 60px auto;
        }

        .invite-card h2 {
            margin: 0 0 10px;
            font-size: 20px;
        }

        .invite-card p {
            color: #666;
            font-size: 14px;
        }

        .invite-input {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #ddd;
            font-size: 14px;
            margin-top: 12px;
        }

        .copy-btn {
            margin-top: 12px;
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: none;
            background: #4f46e5;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
        }

        .copy-btn:active {
            transform: scale(0.98);
        }

        .copied {
            margin-top: 10px;
            color: #16a34a;
            font-size: 13px;
            text-align: center;
            display: none;
        }
    </style>
</head>

<body>

<header class="app-header">
    Invite Link
</header>

<main>
    <div class="invite-card">
        <h2>Community Invite</h2>
        <p>Share this link to invite new members to your community.</p>

        <input
            type="text"
            id="inviteLink"
            class="invite-input"
            value="<?php echo e($inviteLink); ?>"
            readonly
        >

        <button class="copy-btn" onclick="copyInvite()">
            Copy Invite Link
        </button>

        <div class="copied" id="copiedText">✔ Link copied</div>
    </div>
</main>

<script>
function copyInvite() {
    const input = document.getElementById('inviteLink');
    input.select();
    input.setSelectionRange(0, 99999);

    navigator.clipboard.writeText(input.value).then(() => {
        document.getElementById('copiedText').style.display = 'block';
    });
}
</script>

</body>
</html>