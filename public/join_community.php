<?php
require '../includes/db.php';
require '../includes/auth.php';

requireLogin();
$user_id = currentUserId();
$community_id = $_GET['id'] ?? null;

if ($community_id) {
    $stmt = $db->prepare(
        "INSERT OR IGNORE INTO community_members (community_id, user_id, role)
         VALUES (?, ?, 'member')"
    );
    $stmt->execute([$community_id, $user_id]);
}

redirect('/community.php?id=' . $community_id);