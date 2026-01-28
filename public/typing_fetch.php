<?php
require '../includes/db.php';
require '../includes/auth.php';

requireLogin();

$community_id = $_GET['community_id'];
$user_id = currentUserId();

$stmt = $db->prepare(
    "SELECT u.username
     FROM typing_status t
     JOIN users u ON u.id = t.user_id
     WHERE t.community_id = ?
       AND t.user_id != ?
       AND t.updated_at >= datetime('now', '-3 seconds')"
);
$stmt->execute([$community_id, $user_id]);

$users = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo $users ? implode(', ', $users) . ' typing…' : '';