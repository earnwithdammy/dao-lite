<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DAO-Lite — Transparent Community Treasury Management</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="DAO-Lite helps communities manage payouts transparently on Solana without complex governance.">

    <link rel="stylesheet" href="/public/assets/app.css">
</head>
<body>

<header class="top-header">
    <span class="brand">DAO-Lite</span>

    <div class="header-menu">
        <button class="menu-btn" aria-label="More">⋮</button>

        <div class="menu-dropdown">
            <a href="/public/about.php">About</a>
            <a href="/public/terms.php">Terms</a>
            <a href="/public/privacy.php">Privacy</a>
        </div>
    </div>
</header>

<main class="container">

    <!-- =========================
         HERO
    ========================== -->
    <section class="card">
        <h2 class="page-title">Simple, Transparent Treasury Management for Communities</h2>

        <p class="muted" style="max-width:760px;">
            DAO-Lite is a lightweight treasury and payout management tool built for
            communities, DAOs, and grant programs that want transparency without
            unnecessary governance complexity.
        </p>

        <div style="margin-top:20px;">
            <a href="/public/communities.php" class="link">Explore Communities →</a>
            &nbsp;&nbsp;•&nbsp;&nbsp;
            <a href="/public/create_community.php" class="link">Create a Community →</a>
        </div>
    </section>

    <!-- =========================
         WHAT DAO-LITE DOES
    ========================== -->
    <section class="card">
        <h3>What DAO-Lite Does</h3>

        <div class="stats-grid">
            <div class="stat-card">
                <strong>Payout Requests</strong>
                <span>Members submit transparent payout requests</span>
            </div>

            <div class="stat-card">
                <strong>Admin Review</strong>
                <span>Admins approve or reject with full visibility</span>
            </div>

            <div class="stat-card">
                <strong>On-Chain Proof</strong>
                <span>Payments verified directly on Solana</span>
            </div>

            <div class="stat-card">
                <strong>Community Chat</strong>
                <span>Contextual discussion around requests</span>
            </div>
        </div>

        <p class="muted" style="max-width:760px; margin-top:14px;">
            DAO-Lite focuses on operational clarity — who requested funds, why they were
            approved, and whether payment actually happened.
        </p>
    </section>

    <!-- =========================
         WHY WE BUILT THIS
    ========================== -->
    <section class="card">
        <h3>Why We Built DAO-Lite</h3>

        <p class="muted" style="max-width:760px;">
            Many communities and early-stage DAOs don’t fail because of lack of funds —
            they fail because treasury operations become unclear, manual, or opaque.
        </p>

        <p class="muted" style="max-width:760px; margin-top:12px;">
            We saw teams managing payouts through spreadsheets, screenshots, and private
            messages. Approvals were informal. Transactions were hard to track. Trust
            depended on individuals instead of systems.
        </p>

        <p class="muted" style="max-width:760px; margin-top:12px;">
            DAO-Lite was built to remove that friction — not by adding complex governance,
            but by providing a simple, transparent workflow that any community can use.
        </p>

        <div class="stats-grid" style="margin-top:20px;">
            <div class="stat-card">
                <strong>Transparency First</strong>
                <span>Every payout is traceable and verifiable on-chain</span>
            </div>

            <div class="stat-card">
                <strong>Low Overhead</strong>
                <span>No heavy voting or smart-contract complexity</span>
            </div>

            <div class="stat-card">
                <strong>Built for Real Teams</strong>
                <span>Designed for small DAOs and grant programs</span>
            </div>

            <div class="stat-card">
                <strong>Solana-Native</strong>
                <span>Fast, low-cost payments with public verification</span>
            </div>
        </div>

        <!-- ADD-ON LINE (IMPORTANT) -->
        <p class="muted" style="max-width:760px; margin-top:18px; font-style:italic;">
            DAO-Lite is not trying to replace full governance frameworks —
            it’s designed to be the missing operational layer many communities need first.
        </p>
    </section>

    <!-- =========================
         CALL TO ACTION
    ========================== -->
    <section class="card">
        <h3>Who DAO-Lite Is For</h3>

        <ul class="list">
            <li class="list-item">
                <span>Early-stage DAOs</span>
                <span class="muted">Treasury clarity without governance overhead</span>
            </li>
            <li class="list-item">
                <span>Grant Programs</span>
                <span class="muted">Transparent payout verification</span>
            </li>
            <li class="list-item">
                <span>Online Communities</span>
                <span class="muted">Simple financial accountability</span>
            </li>
        </ul>

        <div style="margin-top:18px;">
            <a href="/public/create_community.php" class="link">
                Start a Community →
            </a>
        </div>
    </section>

</main>
<!-- ✅ BOTTOM NAV (INSIDE BODY, ROW LAYOUT) -->
<nav class="app-nav">

    <a href="/public/dashboard.php" class="nav-item">
        <span class="nav-icon">🏠</span>
        <span class="nav-label">Home</span>
    </a>

    <a href="/public/communities.php" class="nav-item">
        <span class="nav-icon">👥</span>
        <span class="nav-label">Communities</span>
    </a>

    <a href="/public/create_community.php" class="nav-item nav-main">
        <span class="nav-icon">＋</span>
    </a>

    <!-- 🔔 ALERTS -->
    <a href="/public/alerts.php" class="nav-item">
        <span class="nav-icon">🔔</span>
        <span class="nav-label">Alerts</span>
    </a>

    <a href="/public/profile.php" class="nav-item">
        <span class="nav-icon">👤</span>
        <span class="nav-label">Profile</span>
    </a>

</nav>

</body>
</html>