<?php
require '../includes/db.php';
require '../includes/auth.php';

requireLogin();
$user_id = currentUserId();
$community_id = $_GET['community_id'] ?? null;

if (!$community_id) {
    http_response_code(400);
    exit('Invalid community');
}

/* Ensure membership */
$stmt = $db->prepare(
    "SELECT 1 FROM community_members WHERE community_id = ? AND user_id = ?"
);
$stmt->execute([$community_id, $user_id]);
if (!$stmt->fetch()) {
    http_response_code(403);
    exit('Access denied');
}

/* AJAX send */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');
    if ($message === '') exit;

    $stmt = $db->prepare(
        "INSERT INTO messages (community_id, user_id, message)
         VALUES (?, ?, ?)"
    );
    $stmt->execute([$community_id, $user_id, $message]);

    echo json_encode([
        'message' => htmlspecialchars($message),
        'time' => date('H:i')
    ]);
    exit;
}

/* Initial messages */
$stmt = $db->prepare(
    "SELECT m.message, m.created_at, u.username, u.id AS sender_id
     FROM messages m
     JOIN users u ON u.id = m.user_id
     WHERE m.community_id = ?
     ORDER BY m.id ASC"
);
$stmt->execute([$community_id]);
$messages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Community Chat</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <link rel="stylesheet" href="/public/assets/app.css">
</head>

<body>

<header class="app-header">Community Chat</header>

<main class="chat-wrapper">
    <div class="chat-messages" id="chat">
        <?php foreach ($messages as $msg): ?>
            <div class="chat-bubble <?php echo $msg['sender_id'] == $user_id ? 'me' : 'them'; ?>">
                <div class="chat-username"><?php echo e($msg['username']); ?></div>
                <div class="chat-text"><?php echo nl2br(e($msg['message'])); ?></div>
                <div class="chat-time"><?php echo date('H:i', strtotime($msg['created_at'])); ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<form class="chat-input-bar" onsubmit="return false;">
    <textarea
        id="message-box"
        placeholder="Write a message…"
        rows="1"
    ></textarea>
    <button type="button" id="send-btn">Send</button>
</form>

<script>
const chatBox  = document.getElementById('chat');
const textarea = document.getElementById('message-box');
const sendBtn  = document.getElementById('send-btn');

/* Scroll */
function scrollDown() {
    chatBox.scrollTop = chatBox.scrollHeight;
}
scrollDown();

/* Auto grow */
function autoGrow() {
    textarea.style.height = '44px';
    textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
}
textarea.addEventListener('input', autoGrow);

/* Append message instantly */
function appendMyMessage(text, time) {
    const div = document.createElement('div');
    div.className = 'chat-bubble me';
    div.innerHTML = `
        <div class="chat-username">You</div>
        <div class="chat-text">${text.replace(/\n/g,'<br>')}</div>
        <div class="chat-time">${time}</div>
    `;
    chatBox.appendChild(div);
    scrollDown();
}

/* Send */
async function sendMessage() {
    const text = textarea.value.trim();
    if (!text) return;

    const res = await fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'message=' + encodeURIComponent(text)
    });

    const data = await res.json();
    appendMyMessage(text, data.time);

    textarea.value = '';
    autoGrow();
}

/* Desktop keyboard logic */
textarea.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey && window.innerWidth > 768) {
        e.preventDefault();
        sendMessage();
    }
});

/* Button send (all devices) */
sendBtn.addEventListener('click', sendMessage);
</script>

</body>
</html>