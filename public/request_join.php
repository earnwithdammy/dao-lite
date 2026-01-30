<?php
require '../includes/db.php';
require '../includes/auth.php';

requireLogin();

$user_id = currentUserId();
$community_id = $_POST['community_id'] ?? null;

if (!$community_id) {
    die('Invalid request');
}

// Already a member → do nothing
if (isCommunityMember($db, $community_id, $user_id)) {
    redirect('/public/community.php?id=' . $community_id);
}

// Already requested → do nothing
if (hasPendingJoinRequest($db, $community_id, $user_id)) {
    redirect('/public/communities.php');
}

// Create join request
$stmt = $db->prepare(
    "INSERT INTO join_requests (community_id, user_id)
     VALUES (?, ?)"
);
$stmt->execute([$community_id, $user_id]);

redirect('/public/communities.php');