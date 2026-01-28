<?php
require '../includes/db.php';
require '../includes/auth.php';

requireLogin();
$admin_id = currentUserId();
$community_id = $_POST['community_id'] ?? null;
$target_user_id = $_POST['user_id'] ?? null;

if (!$community_id || !$target_user_id) {
    die('Invalid request');
}

// Only admins can promote
if (!isCommunityAdmin($db, $community_id, $admin_id)) {
    die('Access denied');
}

$db->prepare(
    "UPDATE community_members 
     SET role = 'admin' 
     WHERE community_id = ? AND user_id = ?"
)->execute([$community_id, $target_user_id]);

redirect('/community.php?id=' . $community_id);