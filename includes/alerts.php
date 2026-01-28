<?php

function createAlert($db, $userId, $type, $title, $message, $link = null) {
    $stmt = $db->prepare(
        "INSERT INTO alerts (user_id, type, title, message, link)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $userId,
        $type,
        $title,
        $message,
        $link
    ]);
}