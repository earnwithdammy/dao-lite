<?php
// includes/auth.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ---------- REDIRECT ---------- */

function redirect($url)
{
    header("Location: $url");
    exit;
}

/* ---------- AUTH ---------- */

function requireLogin()
{
    if (!isset($_SESSION['user_id'])) {
        redirect('/public/login.php');
    }
}

function currentUserId()
{
    return $_SESSION['user_id'] ?? null;
}

/* ---------- COMMUNITY ROLES ---------- */

function isCommunityAdmin($db, $community_id, $user_id)
{
    $stmt = $db->prepare(
        "SELECT 1 FROM community_members
         WHERE community_id = ? AND user_id = ? AND role = 'admin'"
    );
    $stmt->execute([$community_id, $user_id]);
    return (bool) $stmt->fetchColumn();
}

function adminCount($db, $community_id)
{
    $stmt = $db->prepare(
        "SELECT COUNT(*) FROM community_members
         WHERE community_id = ? AND role = 'admin'"
    );
    $stmt->execute([$community_id]);
    return (int) $stmt->fetchColumn();
}

function approvalCount($db, $payout_request_id)
{
    $stmt = $db->prepare(
        "SELECT COUNT(*) FROM approvals WHERE payout_request_id = ?"
    );
    $stmt->execute([$payout_request_id]);
    return (int) $stmt->fetchColumn();
}

/* ---------- CSRF ---------- */

function csrfToken()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function verifyCsrf($token)
{
    return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

/* ---------- ESCAPE ---------- */

function e($str)
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}