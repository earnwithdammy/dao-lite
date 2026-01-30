<?php
require '../includes/db.php';
require '../includes/auth.php';

requireLogin();

$admin_id   = currentUserId();
$request_id = $_POST['request_id'] ?? null;

/* Fetch payout request */
$stmt = $db->prepare(
  "SELECT * FROM payout_requests WHERE id = ?"
);
$stmt->execute([$request_id]);
$request = $stmt->fetch();

if (!$request) {
    die('Request not found');
}

$community_id = $request['community_id'];

/* Admin check */
if (!isCommunityAdmin($db, $community_id, $admin_id)) {
    die('Access denied');
}

/* Only pending requests can be rejected */
if ($request['status'] !== 'pending') {
    die('Cannot reject this request');
}

/* Reject request */
$db->prepare(
  "UPDATE payout_requests
   SET status = 'rejected'
   WHERE id = ?"
)->execute([$request_id]);

redirect('/public/community.php?id=' . $community_id);