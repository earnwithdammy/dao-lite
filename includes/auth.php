<?php
// includes/auth.php

// ---------------------------
// SESSION
// ---------------------------
if (session_status() !== PHP_SESSION_ACTIVE) {
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
    if (empty($_SESSION['user_id'])) {

        // Save where the user was trying to go (optional but powerful)
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];

        header('Location: /public/login.php');
        exit;
    }
}

function currentUserId()
{
    return $_SESSION['user_id'] ?? null;
}

// ---------------------------
// COMMUNITY ROLES
// ---------------------------

function isCommunityAdmin(PDO $db, int $community_id, int $user_id): bool
{
    $stmt = $db->prepare(
        "SELECT 1
         FROM community_members
         WHERE community_id = ? AND user_id = ? AND role = 'admin'
         LIMIT 1"
    );
    $stmt->execute([$community_id, $user_id]);
    return (bool) $stmt->fetchColumn();
}

function adminCount(PDO $db, int $community_id): int
{
    $stmt = $db->prepare(
        "SELECT COUNT(*)
         FROM community_members
         WHERE community_id = ? AND role = 'admin'"
    );
    $stmt->execute([$community_id]);
    return (int) $stmt->fetchColumn();
}

function approvalCount(PDO $db, int $payout_request_id): int
{
    $stmt = $db->prepare(
        "SELECT COUNT(*)
         FROM approvals
         WHERE payout_request_id = ?"
    );
    $stmt->execute([$payout_request_id]);
    return (int) $stmt->fetchColumn();
}

// ---------------------------
// CSRF PROTECTION
// ---------------------------

function csrfToken(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function verifyCsrf(?string $token): bool
{
    return isset($_SESSION['csrf'], $token)
        && hash_equals($_SESSION['csrf'], $token);
}

// ---------------------------
// ESCAPING
// ---------------------------

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// ---------------------------
// DEV ADMIN (OPTIONAL)
// ---------------------------

/**
 * Only you (dev/admin)
 * Change ID if needed
 */
function isDevAdmin(): bool
{
    return currentUserId() === 1;
}