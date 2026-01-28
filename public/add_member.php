<?php
/*
STEP 6: ADD MEMBERS & ADMINS (SEPARATE FILE)
Allows an admin to add members or promote admins within a community.
No wallet custody. Simple role-based structure.
*/

// public/add_member.php
require '../includes/db.php';

$community_id = $_GET['community_id'] ?? null;

if (!$community_id) {
    die('Community not found');
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = trim($_POST['name']);
    $wallet = trim($_POST['wallet']);
    $role   = $_POST['role']; // member | admin

    if ($name !== '' && in_array($role, ['member', 'admin'])) {
        $stmt = $db->prepare(
            "INSERT INTO users (community_id, name, wallet_address, role)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$community_id, $name, $wallet, $role]);

        redirect("community.php?id=$community_id");
    }
}

// Fetch current users
$stmt = $db->prepare(
    "SELECT * FROM users WHERE community_id = ? ORDER BY role DESC"
);
$stmt->execute([$community_id]);
$users = $stmt->fetchAll();
?><!DOCTYPE html><html>
<head>
    <title>Add Member</title>
</head>
<body><h2>Add Member / Admin</h2><form method="post">
    <input type="text" name="name" placeholder="Name" required><br><br>
    <input type="text" name="wallet" placeholder="Wallet Address (optional)"><br><br><select name="role">
    <option value="member">Member</option>
    <option value="admin">Admin</option>
</select><br><br>

<button type="submit">Add</button>

</form><h3>Current Members</h3>
<ul>
<?php foreach ($users as $u): ?>
    <li>
        <?php echo e($u['name']); ?> — <?php echo e($u['role']); ?>
    </li>
<?php endforeach; ?>
</ul></body>
</html>