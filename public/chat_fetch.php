<?php
require '../includes/db.php';
require '../includes/auth.php';

requireLogin();

$community_id = $_GET['community_id'];
$last_id = (int)($_GET['last_id'] ?? 0);

$stmt = $db->prepare(
    "SELECT m.id, m.message, m.created_at, u.username, u.id AS sender_id
     FROM messages m
     JOIN users u ON u.id = m.user_id
     WHERE m.community_id = ? AND m.id > ?
     ORDER BY m.id ASC"
);
$stmt->execute([$community_id, $last_id]);

echo json_encode($stmt->fetchAll());