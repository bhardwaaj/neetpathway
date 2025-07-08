<?php
require_once 'includes/auth_check.php';
require_once '../config/database.php';

$pdo = new PDO("mysql:host=localhost;dbname=neetpathway", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Get all admin users except current user
$current_admin_id = $_SESSION['admin_id'];
try {
    $stmt = $pdo->prepare("SELECT id, username, name, role FROM admin_users WHERE id != ? ORDER BY name");
    $stmt->execute([$current_admin_id]);
    $admin_users = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $admin_users = [];
}

require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
            <div class="position-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-users"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">
                            <i class="fas fa-shopping-cart"></i> Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="counselling.php">
                            <i class="fas fa-calendar"></i> Counselling
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="chat.php">
                            <i class="fas fa-comments"></i> Admin Chat
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Admin Chat</h1>
            </div>

            <div class="row">
                <!-- Admin Users List -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Admin Users</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush" id="adminList">
                                <?php foreach($admin_users as $admin): ?>
                                    <a href="#" class="list-group-item list-group-item-action d-flex align-items-center" 
                                       onclick="loadChat(<?php echo $admin['id']; ?>, '<?php echo htmlspecialchars($admin['name']); ?>')">
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0"><?php echo htmlspecialchars($admin['name']); ?></h6>
                                                <small class="text-muted"><?php echo ucfirst($admin['role']); ?></small>
                                            </div>
                                            <small class="text-muted">@<?php echo htmlspecialchars($admin['username']); ?></small>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chat Area -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0" id="chatTitle">Select an admin to start chatting</h5>
                        </div>
                        <div class="card-body">
                            <div id="chatMessages" style="height: 400px; overflow-y: auto;" class="mb-3">
                                <div class="text-center text-muted">
                                    <i class="fas fa-comments fa-3x mb-2"></i>
                                    <p>Select an admin from the list to start chatting</p>
                                </div>
                            </div>
                            <form id="chatForm" class="d-none">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="messageInput" placeholder="Type your message...">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-paper-plane"></i> Send
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
let currentReceiverId = null;
let lastMessageId = 0;
let chatUpdateInterval = null;

function loadChat(adminId, adminName) {
    currentReceiverId = adminId;
    document.getElementById('chatTitle').textContent = `Chat with ${adminName}`;
    document.getElementById('chatForm').classList.remove('d-none');
    document.getElementById('chatMessages').innerHTML = '';
    lastMessageId = 0;
    
    // Clear previous interval if exists
    if (chatUpdateInterval) {
        clearInterval(chatUpdateInterval);
    }
    
    // Load initial messages
    updateChat();
    
    // Set up periodic updates
    chatUpdateInterval = setInterval(updateChat, 5000);
}

function updateChat() {
    if (!currentReceiverId) return;
    
    fetch(`ajax/get_messages.php?receiver_id=${currentReceiverId}&last_id=${lastMessageId}`)
        .then(response => response.json())
        .then(data => {
            if (data.messages && data.messages.length > 0) {
                const chatMessages = document.getElementById('chatMessages');
                data.messages.forEach(msg => {
                    if (msg.id > lastMessageId) {
                        lastMessageId = msg.id;
                        const messageDiv = document.createElement('div');
                        messageDiv.className = `chat-message ${msg.sender_id == <?php echo $current_admin_id; ?> ? 'text-end' : ''}`;
                        messageDiv.innerHTML = `
                            <div class="message ${msg.sender_id == <?php echo $current_admin_id; ?> ? 'sent' : 'received'} mb-2">
                                <div class="message-content p-2 rounded ${msg.sender_id == <?php echo $current_admin_id; ?> ? 'bg-primary text-white' : 'bg-light'}">
                                    ${msg.message}
                                    <small class="d-block text-${msg.sender_id == <?php echo $current_admin_id; ?> ? 'white' : 'muted'}">${msg.created_at}</small>
                                </div>
                            </div>
                        `;
                        chatMessages.appendChild(messageDiv);
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    }
                });
            }
        });
}

document.getElementById('chatForm').addEventListener('submit', function(e) {
    e.preventDefault();
    if (!currentReceiverId) return;
    
    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();
    if (!message) return;
    
    fetch('ajax/send_message.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            receiver_id: currentReceiverId,
            message: message
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageInput.value = '';
            updateChat();
        }
    });
});
</script>

<style>
.chat-message .message {
    max-width: 70%;
    display: inline-block;
}

.chat-message.text-end .message {
    margin-left: 30%;
}

.chat-message .message-content {
    word-break: break-word;
}
</style>

<?php require_once 'includes/footer.php'; ?> 