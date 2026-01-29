<?php
require '../includes/db.php';
require '../includes/auth.php';

$code = $_GET['code'] ?? null;
if (!$code) {
    die('Invalid invite');
}

// NOT logged in → store invite + redirect
if (empty($_SESSION['user_id'])) {
    $_SESSION['pending_invite'] = $code;
    header('Location: /public/login.php');
    exit;
}

$user_id = currentUserId();

// validate invite
$stmt = $db->prepare(
    "SELECT community_id
     FROM community_invites
     WHERE code = ?
     LIMIT 1"
);
$stmt->execute([$code]);
$invite = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$invite) {
    die('Invite not found or expired');
}

$community_id = (int) $invite['community_id'];

// join (safe, no duplicates)
$stmt = $db->prepare(
    "INSERT OR IGNORE INTO community_members (community_id, user_id, role)
     VALUES (?, ?, 'member')"
);
$stmt->execute([$community_id, $user_id]);

header('Location: /public/community.php?id=' . $community_id);
exit;