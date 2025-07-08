<?php
require_once 'includes/auth_check.php';
require_once '../config/database.php';

$pdo = new PDO("mysql:host=localhost;dbname=neetpathway", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete':
                if (isset($_POST['user_id'])) {
                    try {
                        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                        $stmt->execute([$_POST['user_id']]);
                        
                        // Log activity
                        $admin_id = $_SESSION['admin_id'];
                        $stmt = $pdo->prepare("INSERT INTO admin_activity_log (admin_id, action, description) VALUES (?, 'delete_user', ?)");
                        $stmt->execute([$admin_id, "Deleted user ID: " . $_POST['user_id']]);
                        
                        $_SESSION['success'] = "User deleted successfully";
                    } catch(PDOException $e) {
                        $_SESSION['error'] = "Error deleting user";
                        error_log("Error: " . $e->getMessage());
                    }
                }
                break;
                
            case 'make_admin':
                if (isset($_POST['user_id'])) {
                    try {
                        // Get user details
                        $stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
                        $stmt->execute([$_POST['user_id']]);
                        $user = $stmt->fetch();
                        
                        // Create admin account
                        $username = strtolower(explode(' ', $user['name'])[0]) . rand(100, 999);
                        $password = bin2hex(random_bytes(8));
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        
                        $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, name, email, role) VALUES (?, ?, ?, ?, 'support')");
                        $stmt->execute([$username, $hashed_password, $user['name'], $user['email']]);
                        
                        // Log activity
                        $admin_id = $_SESSION['admin_id'];
                        $stmt = $pdo->prepare("INSERT INTO admin_activity_log (admin_id, action, description) VALUES (?, 'grant_admin', ?)");
                        $stmt->execute([$admin_id, "Granted admin access to user ID: " . $_POST['user_id']]);
                        
                        $_SESSION['success'] = "Admin access granted. Username: $username, Password: $password";
                    } catch(PDOException $e) {
                        $_SESSION['error'] = "Error granting admin access";
                        error_log("Error: " . $e->getMessage());
                    }
                }
                break;
        }
        header("Location: users.php");
        exit();
    }
}

require_once 'includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
        <h1 class="h3">User Management</h1>
        <div class="btn-toolbar">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-user-plus me-1"></i> Add New User
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

    <!-- Users Table Card -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="usersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
                            while($row = $stmt->fetch()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                                echo "<td>" . date('Y-m-d', strtotime($row['created_at'])) . "</td>";
                                echo "<td>";
                                echo "<div class='btn-group btn-group-sm'>";
                                echo "<button type='button' class='btn btn-outline-primary' onclick='viewUser(" . $row['id'] . ")'><i class='fas fa-eye'></i></button>";
                                echo "<button type='button' class='btn btn-outline-warning' onclick='editUser(" . $row['id'] . ")'><i class='fas fa-edit'></i></button>";
                                echo "<button type='button' class='btn btn-outline-success' onclick='makeAdmin(" . $row['id'] . ")'><i class='fas fa-user-shield'></i></button>";
                                echo "<button type='button' class='btn btn-outline-danger' onclick='deleteUser(" . $row['id'] . ")'><i class='fas fa-trash'></i></button>";
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

<!-- View User Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="userDetails"></div>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="editUserId" name="user_id">
                    <div class="mb-3">
                        <label for="editName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="editName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="editEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="editPhone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="editPhone" name="phone">
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="mb-3">
                        <label for="addName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="addName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="addEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="addEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="addPhone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="addPhone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label for="addPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="addPassword" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#usersTable').DataTable({
        responsive: true,
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    });
});

function viewUser(userId) {
    fetch(`ajax/get_user.php?id=${userId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('userDetails').innerHTML = `
                <p><strong>Name:</strong> ${data.name}</p>
                <p><strong>Email:</strong> ${data.email}</p>
                <p><strong>Phone:</strong> ${data.phone}</p>
                <p><strong>Registered:</strong> ${data.created_at}</p>
            `;
            new bootstrap.Modal(document.getElementById('viewUserModal')).show();
        });
}

function editUser(userId) {
    fetch(`ajax/get_user.php?id=${userId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('editUserId').value = userId;
            document.getElementById('editName').value = data.name;
            document.getElementById('editEmail').value = data.email;
            document.getElementById('editPhone').value = data.phone;
            new bootstrap.Modal(document.getElementById('editUserModal')).show();
        });
}

function makeAdmin(userId) {
    if (confirm('Are you sure you want to grant admin access to this user?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="make_admin">
            <input type="hidden" name="user_id" value="${userId}">
        `;
        document.body.append(form);
        form.submit();
    }
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="user_id" value="${userId}">
        `;
        document.body.append(form);
        form.submit();
    }
}

document.getElementById('editUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('ajax/update_user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error updating user');
        }
    });
});

document.getElementById('addUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('ajax/add_user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error adding user');
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 