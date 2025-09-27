<?php
require_once 'includes/auth_check.php';
require_once '../config/database.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=neetpathway", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Ensure table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        audience ENUM('all','user','admin') DEFAULT 'all',
        user_id INT NULL,
        is_active TINYINT(1) DEFAULT 1,
        starts_at DATETIME NULL,
        ends_at DATETIME NULL,
        link_url VARCHAR(1024) NULL,
        attachment_path VARCHAR(1024) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (audience), INDEX (user_id), INDEX (is_active), INDEX (starts_at), INDEX (ends_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
} catch (PDOException $e) {
    die('DB connection error');
}

// Handle create/update/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'create') {
            // handle file upload
            $uploadPath = null;
            if (!empty($_FILES['attachment']['name'])) {
                $dir = __DIR__ . '/../uploads';
                if (!is_dir($dir)) @mkdir($dir, 0777, true);
                $safeName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $_FILES['attachment']['name']);
                $dest = $dir . '/' . $safeName;
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $dest)) {
                    $uploadPath = 'uploads/' . $safeName;
                }
            }
            $stmt = $pdo->prepare("INSERT INTO notifications (title, message, audience, user_id, is_active, starts_at, ends_at, link_url, attachment_path) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->execute([
                $_POST['title'],
                $_POST['message'],
                $_POST['audience'] ?? 'all',
                $_POST['audience'] === 'user' ? ($_POST['user_id'] ?: null) : null,
                isset($_POST['is_active']) ? 1 : 0,
                $_POST['starts_at'] ?: null,
                $_POST['ends_at'] ?: null,
                $_POST['link_url'] ?: null,
                $uploadPath
            ]);
            $_SESSION['success'] = 'Notification created';
        } elseif ($action === 'update') {
            $uploadPath = null;
            if (!empty($_FILES['attachment']['name'])) {
                $dir = __DIR__ . '/../uploads';
                if (!is_dir($dir)) @mkdir($dir, 0777, true);
                $safeName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $_FILES['attachment']['name']);
                $dest = $dir . '/' . $safeName;
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $dest)) {
                    $uploadPath = 'uploads/' . $safeName;
                }
            }
            $sql = "UPDATE notifications SET title=?, message=?, audience=?, user_id=?, is_active=?, starts_at=?, ends_at=?, link_url=?" . ($uploadPath ? ", attachment_path=?" : "") . " WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $params = [
                $_POST['title'],
                $_POST['message'],
                $_POST['audience'] ?? 'all',
                $_POST['audience'] === 'user' ? ($_POST['user_id'] ?: null) : null,
                isset($_POST['is_active']) ? 1 : 0,
                $_POST['starts_at'] ?: null,
                $_POST['ends_at'] ?: null,
                $_POST['link_url'] ?: null
            ];
            if ($uploadPath) { $params[] = $uploadPath; }
            $params[] = $_POST['id'];
            $stmt->execute($params);
            $_SESSION['success'] = 'Notification updated';
        } elseif ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM notifications WHERE id=?");
            $stmt->execute([$_POST['id']]);
            $_SESSION['success'] = 'Notification deleted';
        } elseif ($action === 'toggle') {
            $stmt = $pdo->prepare("UPDATE notifications SET is_active = NOT is_active WHERE id=?");
            $stmt->execute([$_POST['id']]);
            $_SESSION['success'] = 'Notification status toggled';
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Database error';
    }
    header('Location: notifications.php');
    exit();
}

require_once 'includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3">
        <h1 class="h3">Notifications</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal"><i class="fas fa-plus me-1"></i>New</button>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="notificationsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Audience</th>
                            <th>Active</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $rows = [];
                        try {
                            $rows = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC")->fetchAll();
                        } catch (PDOException $e) {
                            $rows = [];
                        }
                        foreach ($rows as $row): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars($row['audience'] . ($row['user_id'] ? ' (#'.$row['user_id'].')' : '')); ?></td>
                                <td><span class="badge bg-<?php echo $row['is_active'] ? 'success' : 'secondary'; ?>"><?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
                                <td><?php echo $row['starts_at']; ?></td>
                                <td><?php echo $row['ends_at']; ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick='editNotification(<?php echo json_encode($row); ?>)'><i class="fas fa-edit"></i></button>
                                        <form method="post" onsubmit="return confirm('Delete this notification?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button class="btn btn-outline-danger" type="submit"><i class="fas fa-trash"></i></button>
                                        </form>
                                        <form method="post">
                                            <input type="hidden" name="action" value="toggle">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-toggle-on"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Create Notification</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create">
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Title</label><input class="form-control" name="title" required></div>
                    <div class="mb-3"><label class="form-label">Message</label><textarea class="form-control" name="message" rows="4" required></textarea></div>
                    <div class="mb-3"><label class="form-label">Link URL (optional)</label><input class="form-control" name="link_url" placeholder="https://..."></div>
                    <div class="mb-3"><label class="form-label">Attachment (PDF)</label><input class="form-control" type="file" name="attachment" accept="application/pdf"></div>
                    <div class="row g-2">
                        <div class="col-6"><label class="form-label">Start</label><input type="datetime-local" class="form-control" name="starts_at"></div>
                        <div class="col-6"><label class="form-label">End</label><input type="datetime-local" class="form-control" name="ends_at"></div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Audience</label>
                        <select class="form-select" name="audience" id="audienceSelect" onchange="toggleUserId()">
                            <option value="all">All users</option>
                            <option value="user">Specific user</option>
                        </select>
                    </div>
                    <div class="mt-2 d-none" id="userIdGroup"><label class="form-label">User ID</label><input class="form-control" name="user_id" id="userIdInput"></div>
                    <div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="is_active" id="activeCheck" checked><label class="form-check-label" for="activeCheck">Active</label></div>
                </div>
                <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Close</button><button class="btn btn-primary" type="submit">Save</button></div>
            </form>
        </div>
    </div>
 </div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Edit Notification</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="editId">
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Title</label><input class="form-control" name="title" id="editTitle" required></div>
                    <div class="mb-3"><label class="form-label">Message</label><textarea class="form-control" name="message" id="editMessage" rows="4" required></textarea></div>
                    <div class="mb-3"><label class="form-label">Link URL (optional)</label><input class="form-control" name="link_url" id="editLink"></div>
                    <div class="mb-3"><label class="form-label">Replace Attachment (PDF)</label><input class="form-control" type="file" name="attachment" accept="application/pdf"></div>
                    <div class="row g-2">
                        <div class="col-6"><label class="form-label">Start</label><input type="datetime-local" class="form-control" name="starts_at" id="editStarts"></div>
                        <div class="col-6"><label class="form-label">End</label><input type="datetime-local" class="form-control" name="ends_at" id="editEnds"></div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Audience</label>
                        <select class="form-select" name="audience" id="editAudience" onchange="toggleEditUserId()">
                            <option value="all">All users</option>
                            <option value="user">Specific user</option>
                        </select>
                    </div>
                    <div class="mt-2 d-none" id="editUserIdGroup"><label class="form-label">User ID</label><input class="form-control" name="user_id" id="editUserId"></div>
                    <div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="is_active" id="editActive"><label class="form-check-label" for="editActive">Active</label></div>
                </div>
                <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Close</button><button class="btn btn-primary" type="submit">Save</button></div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleUserId(){
  const sel=document.getElementById('audienceSelect');
  document.getElementById('userIdGroup').classList.toggle('d-none', sel.value!=='user');
}
function toggleEditUserId(){
  const sel=document.getElementById('editAudience');
  document.getElementById('editUserIdGroup').classList.toggle('d-none', sel.value!=='user');
}
function editNotification(row){
  document.getElementById('editId').value = row.id;
  document.getElementById('editTitle').value = row.title;
  document.getElementById('editMessage').value = row.message;
  document.getElementById('editStarts').value = row.starts_at ? row.starts_at.replace(' ', 'T') : '';
  document.getElementById('editEnds').value = row.ends_at ? row.ends_at.replace(' ', 'T') : '';
  document.getElementById('editAudience').value = row.audience;
  document.getElementById('editUserId').value = row.user_id || '';
  document.getElementById('editActive').checked = row.is_active==1;
  toggleEditUserId();
  new bootstrap.Modal(document.getElementById('editModal')).show();
}

document.addEventListener('DOMContentLoaded', function(){
  if (window.jQuery) {
    $('#notificationsTable').DataTable({ responsive: true });
  }
});
</script>

<?php require_once 'includes/footer.php'; ?>


