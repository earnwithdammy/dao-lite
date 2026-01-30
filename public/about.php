<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About • DAO-Lite</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
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

    <h2 class="page-title">About DAO-Lite</h2>

    <section class="card">

        <p>
            <strong>DAO Lite</strong> was built to solve one simple problem:
        </p>

        <h3 style="margin-top:6px;">
            Pay contributors transparently — without governance complexity.
        </h3>

        <p class="muted" style="max-width:760px;">
            Many communities, startups, and DAOs don’t need heavy governance frameworks.
            They need a fast, reliable way to coordinate decisions and send payouts.
        </p>

        <p class="muted" style="max-width:760px;">
            DAO Lite focuses on practical coordination instead of politics or
            over-engineered voting systems.
        </p>

        <h3 style="margin-top:18px;">What DAO Lite Focuses On</h3>

        <ul class="list">
            <li class="list-item">
                <span>Simple member roles</span>
                <span class="muted">Clear permissions and responsibilities</span>
            </li>
            <li class="list-item">
                <span>Approval workflows</span>
                <span class="muted">Transparent review and decision tracking</span>
            </li>
            <li class="list-item">
                <span>Non-custodial payouts</span>
                <span class="muted">Fast blockchain payments without fund custody</span>
            </li>
        </ul>

        <h3 style="margin-top:18px;">Our Mission</h3>

        <p class="muted" style="max-width:760px;">
            Our mission is to make DAO coordination practical and accessible —
            starting in Nigeria and serving communities globally.
        </p>

        <p class="muted" style="max-width:760px;">
            We believe transparency should be the default,
            not an optional feature.
        </p>

        <p style="margin-top:16px;">
            <strong>
                DAO Lite is a non-custodial platform.
                We never hold or control user funds.
            </strong>
        </p>

        <p class="muted" style="margin-top:16px;">
            Contact us:
            <a href="mailto:support@daolite.app" class="link">
                support@daolite.app
            </a>
        </p>

    </section>

</main>

<!-- Bottom Navigation (same as dashboard) -->
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