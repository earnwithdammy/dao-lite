<?php
require '../includes/db.php';
require '../includes/auth.php';

requireLogin();
$admin_id = currentUserId();
$request_id = $_POST['request_id'] ?? null;

// Get request + community
$stmt = $db->prepare(
  "SELECT * FROM payout_requests WHERE id = ?"
);
$stmt->execute([$request_id]);
$request = $stmt->fetch();

if (!$request) die('Request not found');

$community_id = $request['community_id'];

// Admin check
if (!isCommunityAdmin($db, $community_id, $admin_id)) {
  die('Access denied');
}

// Record approval (unique per admin)
$db->prepare(
  "INSERT OR IGNORE INTO approvals (payout_request_id, admin_user_id)
   VALUES (?, ?)"
)->execute([$request_id, $admin_id]);

// Check quorum
$totalAdmins = adminCount($db, $community_id);
$approvals = approvalCount($db, $request_id);

if ($approvals >= ceil((2/3) * $totalAdmins)) {
  $db->prepare(
    "UPDATE payout_requests SET status = 'approved' WHERE id = ?"
  )->execute([$request_id]);
}

redirect('/public/community.php?id=' . $community_id);