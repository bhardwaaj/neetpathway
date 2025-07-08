<?php
require_once 'includes/auth_check.php';
require_once '../config/database.php';

$pdo = new PDO("mysql:host=localhost;dbname=neetpathway", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Handle session status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_status':
                try {
                    $stmt = $pdo->prepare("UPDATE counselling_sessions SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                    $stmt->execute([$_POST['status'], $_POST['session_id']]);
                    
                    // Log activity
                    $admin_id = $_SESSION['admin_id'];
                    $stmt = $pdo->prepare("INSERT INTO admin_activity_log (admin_id, action, description) VALUES (?, 'update_session', ?)");
                    $stmt->execute([$admin_id, "Updated session ID: " . $_POST['session_id'] . " status to: " . $_POST['status']]);
                    
                    $_SESSION['success'] = "Session status updated successfully";
                } catch(PDOException $e) {
                    $_SESSION['error'] = "Error updating session status";
                    error_log("Error: " . $e->getMessage());
                }
                break;

            case 'add_session':
                try {
                    $stmt = $pdo->prepare("INSERT INTO counselling_sessions (user_id, session_date, time_slot, status, notes) VALUES (?, ?, ?, 'pending', ?)");
                    $stmt->execute([
                        $_POST['user_id'],
                        $_POST['session_date'],
                        $_POST['time_slot'],
                        $_POST['notes']
                    ]);
                    
                    // Log activity
                    $admin_id = $_SESSION['admin_id'];
                    $stmt = $pdo->prepare("INSERT INTO admin_activity_log (admin_id, action, description) VALUES (?, 'create_session', ?)");
                    $stmt->execute([$admin_id, "Created new session for user ID: " . $_POST['user_id']]);
                    
                    $_SESSION['success'] = "New session created successfully";
                } catch(PDOException $e) {
                    $_SESSION['error'] = "Error creating session";
                    error_log("Error: " . $e->getMessage());
                }
                break;
        }
        header("Location: sessions.php");
        exit();
    }
}

require_once 'includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="h3">Counselling Sessions</h1>
        <div class="btn-toolbar">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSessionModal">
                <i class="fas fa-plus me-1"></i> Schedule New Session
            </button>
        </div>
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

    <!-- Sessions Table Card -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="sessionsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>Date</th>
                            <th>Time Slot</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $stmt = $pdo->query("
                                SELECT cs.*, u.name as user_name, u.email as user_email 
                                FROM counselling_sessions cs 
                                JOIN users u ON cs.user_id = u.id 
                                ORDER BY cs.session_date DESC, cs.time_slot ASC
                            ");
                            while($row = $stmt->fetch()) {
                                $statusClass = [
                                    'pending' => 'warning',
                                    'confirmed' => 'info',
                                    'completed' => 'success',
                                    'cancelled' => 'danger'
                                ][$row['status']] ?? 'secondary';

                                echo "<tr>";
                                echo "<td>#" . str_pad($row['id'], 4, '0', STR_PAD_LEFT) . "</td>";
                                echo "<td title='" . htmlspecialchars($row['user_email']) . "'>" . htmlspecialchars($row['user_name']) . "</td>";
                                echo "<td>" . date('Y-m-d', strtotime($row['session_date'])) . "</td>";
                                echo "<td>" . htmlspecialchars($row['time_slot']) . "</td>";
                                echo "<td><span class='badge bg-" . $statusClass . "'>" . ucfirst($row['status']) . "</span></td>";
                                echo "<td>" . date('Y-m-d H:i', strtotime($row['created_at'])) . "</td>";
                                echo "<td>";
                                echo "<div class='btn-group btn-group-sm'>";
                                echo "<button type='button' class='btn btn-outline-primary' onclick='viewSession(" . $row['id'] . ")'><i class='fas fa-eye'></i></button>";
                                echo "<button type='button' class='btn btn-outline-warning' onclick='updateStatus(" . $row['id'] . ")'><i class='fas fa-edit'></i></button>";
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

<!-- View Session Modal -->
<div class="modal fade" id="viewSessionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Session Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3">Session Information</h6>
                        <p><strong>Session ID:</strong> <span id="session-id"></span></p>
                        <p><strong>Date:</strong> <span id="session-date"></span></p>
                        <p><strong>Time Slot:</strong> <span id="time-slot"></span></p>
                        <p><strong>Status:</strong> <span id="status"></span></p>
                        <p><strong>Notes:</strong> <span id="notes"></span></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3">Student Information</h6>
                        <p><strong>Name:</strong> <span id="student-name"></span></p>
                        <p><strong>Email:</strong> <span id="student-email"></span></p>
                        <p><strong>Phone:</strong> <span id="student-phone"></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Session Modal -->
<div class="modal fade" id="addSessionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Schedule New Session</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addSessionForm" method="POST">
                    <input type="hidden" name="action" value="add_session">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Student</label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="">Select Student</option>
                            <?php
                            try {
                                $stmt = $pdo->query("SELECT id, name, email FROM users ORDER BY name");
                                while($user = $stmt->fetch()) {
                                    echo "<option value='" . $user['id'] . "'>" . htmlspecialchars($user['name']) . " (" . $user['email'] . ")</option>";
                                }
                            } catch(PDOException $e) {
                                error_log("Error: " . $e->getMessage());
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="session_date" class="form-label">Session Date</label>
                        <input type="date" class="form-control" id="session_date" name="session_date" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="time_slot" class="form-label">Time Slot</label>
                        <select class="form-select" id="time_slot" name="time_slot" required>
                            <option value="">Select Time Slot</option>
                            <?php
                            $start = 9; // 9 AM
                            $end = 17; // 5 PM
                            for($hour = $start; $hour < $end; $hour++) {
                                $time = sprintf("%02d:00", $hour);
                                echo "<option value='" . $time . "'>" . date("g:i A", strtotime($time)) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Schedule Session</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Session Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="updateStatusForm" method="POST">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" id="updateSessionId" name="session_id">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#sessionsTable').DataTable({
        responsive: true,
        order: [[2, 'asc'], [3, 'asc']], // Sort by date and time
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    });

    // Initialize date picker with min date
    document.getElementById('session_date').min = new Date().toISOString().split('T')[0];
});

function viewSession(sessionId) {
    fetch(`ajax/get_session.php?id=${sessionId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.session) {
                const session = data.session;
                document.getElementById('session-id').textContent = `#${String(session.id).padStart(4, '0')}`;
                document.getElementById('session-date').textContent = session.session_date;
                document.getElementById('time-slot').textContent = session.time_slot;
                document.getElementById('status').textContent = session.status;
                document.getElementById('notes').textContent = session.notes || 'No notes';
                document.getElementById('student-name').textContent = session.user_name;
                document.getElementById('student-email').textContent = session.user_email;
                document.getElementById('student-phone').textContent = session.user_phone || 'Not provided';
                
                new bootstrap.Modal(document.getElementById('viewSessionModal')).show();
            } else {
                alert('Error loading session details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading session details');
        });
}

function updateStatus(sessionId) {
    document.getElementById('updateSessionId').value = sessionId;
    new bootstrap.Modal(document.getElementById('updateStatusModal')).show();
}
</script>

<?php require_once 'includes/footer.php'; ?> 