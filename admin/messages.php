<?php
require_once 'includes/auth_check.php';
require_once '../config/database.php';

$pdo = new PDO("mysql:host=localhost;dbname=neetpathway", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Handle message status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        switch ($_POST['action']) {
            case 'mark_read':
                $stmt = $pdo->prepare("UPDATE contacts SET is_read = 1 WHERE id = ?");
                $stmt->execute([$_POST['message_id']]);
                $_SESSION['success'] = "Message marked as read";
                break;
                
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
                $stmt->execute([$_POST['message_id']]);
                $_SESSION['success'] = "Message deleted successfully";
                break;
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error updating message";
        error_log("Error: " . $e->getMessage());
    }
    header("Location: messages.php");
    exit();
}

require_once 'includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="h3">Contact Messages</h1>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Messages Table Card -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="messagesTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $stmt = $pdo->query("SELECT * FROM contacts ORDER BY created_at DESC");
                            while($row = $stmt->fetch()) {
                                $statusClass = $row['is_read'] ? 'success' : 'warning';
                                $statusText = $row['is_read'] ? 'Read' : 'Unread';

                                echo "<tr class='" . (!$row['is_read'] ? 'table-warning' : '') . "'>";
                                echo "<td>#" . str_pad($row['id'], 4, '0', STR_PAD_LEFT) . "</td>";
                                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                                echo "<td><span class='badge bg-" . $statusClass . "'>" . $statusText . "</span></td>";
                                echo "<td>" . date('Y-m-d H:i', strtotime($row['created_at'])) . "</td>";
                                echo "<td>";
                                echo "<div class='btn-group btn-group-sm'>";
                                echo "<button type='button' class='btn btn-outline-primary' onclick='viewMessage(" . $row['id'] . ")'><i class='fas fa-eye'></i></button>";
                                if (!$row['is_read']) {
                                    echo "<button type='button' class='btn btn-outline-success' onclick='markAsRead(" . $row['id'] . ")'><i class='fas fa-check'></i></button>";
                                }
                                echo "<button type='button' class='btn btn-outline-danger' onclick='deleteMessage(" . $row['id'] . ")'><i class='fas fa-trash'></i></button>";
                                echo "</div>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } catch(PDOException $e) {
                            error_log("Error: " . $e->getMessage());
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- View Message Modal -->
<div class="modal fade" id="viewMessageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Message Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3">Contact Information</h6>
                        <p><strong>Name:</strong> <span id="contact-name"></span></p>
                        <p><strong>Email:</strong> <span id="contact-email"></span></p>
                        <p><strong>Phone:</strong> <span id="contact-phone"></span></p>
                        <p><strong>Date:</strong> <span id="contact-date"></span></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3">Message</h6>
                        <div class="border rounded p-3 bg-light">
                            <p id="message-content" style="white-space: pre-wrap;"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="replyButton">
                    <i class="fas fa-reply me-1"></i> Reply via Email
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#messagesTable').DataTable({
        responsive: true,
        order: [[5, 'desc']], // Sort by date
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    });
});

function viewMessage(messageId) {
    fetch(`ajax/get_message.php?id=${messageId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.message) {
                const message = data.message;
                document.getElementById('contact-name').textContent = message.name;
                document.getElementById('contact-email').textContent = message.email;
                document.getElementById('contact-phone').textContent = message.phone;
                document.getElementById('contact-date').textContent = message.created_at;
                document.getElementById('message-content').textContent = message.message;
                
                // Update reply button
                document.getElementById('replyButton').onclick = () => {
                    window.location.href = `mailto:${message.email}?subject=Re: Contact Form Submission`;
                };
                
                new bootstrap.Modal(document.getElementById('viewMessageModal')).show();
                
                // If message is unread, mark it as read
                if (!message.is_read) {
                    markAsRead(messageId, false);
                }
            } else {
                alert('Error loading message details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading message details');
        });
}

function markAsRead(messageId, reload = true) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="action" value="mark_read">
        <input type="hidden" name="message_id" value="${messageId}">
    `;
    document.body.append(form);
    if (reload) {
        form.submit();
    } else {
        fetch('messages.php', {
            method: 'POST',
            body: new FormData(form)
        });
    }
}

function deleteMessage(messageId) {
    if (confirm('Are you sure you want to delete this message?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="message_id" value="${messageId}">
        `;
        document.body.append(form);
        form.submit();
    }
}
</script>

<?php require_once 'includes/footer.php'; ?> 