<?php
require '../includes/db.php';
require '../includes/auth.php';

requireLogin();
verifyCsrf($_POST['csrf'] ?? '');

$request_id = (int)$_POST['request_id'];

$stmt = $db->prepare(
    "SELECT * FROM join_requests WHERE id = ? AND status = 'pending'"
);
$stmt->execute([$request_id]);
$req = $stmt->fetch();

if (!$req) {
    die('Invalid request');
}

if (!isCommunityAdmin($db, $req['community_id'], currentUserId())) {
    http_response_code(403);
    die('Forbidden');
}

$stmt = $db->prepare(
    "UPDATE join_requests SET status = 'rejected' WHERE id = ?"
);
$stmt->execute([$request_id]);

redirect('/public/community.php?id=' . $req['community_id']);