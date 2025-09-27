<?php
require_once 'includes/auth_check.php';
require_once '../config/database.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=neetpathway", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE TABLE IF NOT EXISTS news (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        pdf_path VARCHAR(1024) NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(is_active), INDEX(created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    // Allow URL-only items by making pdf_path nullable
    try { $pdo->exec("ALTER TABLE news MODIFY COLUMN pdf_path VARCHAR(1024) NULL"); } catch (Exception $e) {}
    // Ensure newer columns exist (schema migration)
    try { $pdo->exec("ALTER TABLE news ADD COLUMN IF NOT EXISTS message TEXT NULL"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE news ADD COLUMN IF NOT EXISTS link_url VARCHAR(1024) NULL"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE news ADD COLUMN IF NOT EXISTS starts_at DATETIME NULL"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE news ADD COLUMN IF NOT EXISTS ends_at DATETIME NULL"); } catch (Exception $e) {}
    try { $pdo->exec("CREATE INDEX IF NOT EXISTS idx_news_starts ON news (starts_at)"); } catch (Exception $e) {}
    try { $pdo->exec("CREATE INDEX IF NOT EXISTS idx_news_ends ON news (ends_at)"); } catch (Exception $e) {}
} catch (PDOException $e) {
	die('DB connection error');
}

// Auto cleanup: delete expired items and files
try {
    $expired = $pdo->query("SELECT id, pdf_path FROM news WHERE ends_at IS NOT NULL AND ends_at < NOW()")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($expired as $ex) {
        $abs = __DIR__ . '/../' . $ex['pdf_path'];
        if (is_file($abs)) { @unlink($abs); }
        $stmt = $pdo->prepare('DELETE FROM news WHERE id = ?');
        $stmt->execute([$ex['id']]);
    }
} catch (Exception $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = $_POST['action'] ?? '';
	try {
        if ($action === 'create') {
			$dir = __DIR__ . '/../uploads/news';
			if (!is_dir($dir)) @mkdir($dir, 0777, true);
            $source = $_POST['source_type'] ?? 'pdf';
            $rel = null; $url = null;
            if ($source === 'url') {
                $url = trim($_POST['link_url'] ?? '');
                if ($url === '') { throw new Exception('Please provide a valid URL'); }
            } else {
                if (empty($_FILES['pdf']['name'])) { throw new Exception('Please choose a PDF'); }
                $ext = strtolower(pathinfo($_FILES['pdf']['name'], PATHINFO_EXTENSION));
                if ($ext !== 'pdf') { throw new Exception('Only PDF files are allowed'); }
                $safeName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $_FILES['pdf']['name']);
                $dest = $dir . '/' . $safeName;
                if (!move_uploaded_file($_FILES['pdf']['tmp_name'], $dest)) { throw new Exception('Upload failed'); }
                $rel = 'uploads/news/' . $safeName;
            }
            $stmt = $pdo->prepare("INSERT INTO news (title, message, link_url, pdf_path, starts_at, ends_at, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['title'],
                $_POST['message'] ?: null,
                $url,
                $rel,
                $_POST['starts_at'] ?: null,
                $_POST['ends_at'] ?: null,
                isset($_POST['is_active']) ? 1 : 0
            ]);
            $_SESSION['success'] = 'News saved';
		} elseif ($action === 'delete') {
			$stmt = $pdo->prepare('DELETE FROM news WHERE id = ?');
			$stmt->execute([$_POST['id']]);
			$_SESSION['success'] = 'Deleted';
		} elseif ($action === 'toggle') {
			$stmt = $pdo->prepare('UPDATE news SET is_active = NOT is_active WHERE id = ?');
			$stmt->execute([$_POST['id']]);
			$_SESSION['success'] = 'Status updated';
		}
	} catch (Exception $e) {
		$_SESSION['error'] = $e->getMessage();
	}
	header('Location: news.php');
	exit();
}

require_once 'includes/header.php';
?>

<div class="container-fluid px-4">
	<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3">
		<h1 class="h3">News (PDF)</h1>
		<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal"><i class="fas fa-plus me-1"></i>Upload</button>
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
				<table class="table table-hover align-middle">
					<thead>
						<tr>
							<th>ID</th>
							<th>Title</th>
                            <th>Start</th>
                            <th>End</th>
							<th>Active</th>
							<th>Created</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
                        <?php $rows = $pdo->query('SELECT * FROM news ORDER BY created_at DESC')->fetchAll(); foreach ($rows as $row): ?>
						<tr>
							<td><?php echo $row['id']; ?></td>
							<td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo $row['starts_at']; ?></td>
                            <td><?php echo $row['ends_at']; ?></td>
							<td><span class="badge bg-<?php echo $row['is_active'] ? 'success' : 'secondary'; ?>"><?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
							<td><?php echo $row['created_at']; ?></td>
							<td>
								<a class="btn btn-sm btn-outline-info" href="../<?php echo $row['pdf_path']; ?>" target="_blank"><i class="fas fa-file-pdf"></i></a>
								<form class="d-inline" method="post">
									<input type="hidden" name="action" value="toggle">
									<input type="hidden" name="id" value="<?php echo $row['id']; ?>">
									<button class="btn btn-sm btn-outline-secondary" type="submit"><i class="fas fa-toggle-on"></i></button>
								</form>
								<form class="d-inline" method="post" onsubmit="return confirm('Delete this item?')">
									<input type="hidden" name="action" value="delete">
									<input type="hidden" name="id" value="<?php echo $row['id']; ?>">
									<button class="btn btn-sm btn-outline-danger" type="submit"><i class="fas fa-trash"></i></button>
								</form>
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
			<div class="modal-header"><h5 class="modal-title">Upload News PDF</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="post" enctype="multipart/form-data">
				<input type="hidden" name="action" value="create">
				<div class="modal-body">
                    <div class="mb-3"><label class="form-label">Title</label><input class="form-control" name="title" required></div>
                    <div class="mb-3"><label class="form-label">Message (optional)</label><textarea class="form-control" name="message" rows="3" placeholder="Short description"></textarea></div>
                    <div class="mb-3">
                        <label class="form-label">Source</label>
                        <div class="d-flex align-items-center gap-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="source_type" id="srcPdf" value="pdf" checked>
                                <label class="form-check-label" for="srcPdf">Upload PDF</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="source_type" id="srcUrl" value="url">
                                <label class="form-check-label" for="srcUrl">External URL</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3" id="pdfGroup"><label class="form-label">PDF File</label><input class="form-control" type="file" name="pdf" accept="application/pdf"></div>
                    <div class="mb-3 d-none" id="urlGroup"><label class="form-label">Link URL</label><input class="form-control" name="link_url" placeholder="https://..."></div>
                    <div class="row g-2">
                        <div class="col-6"><label class="form-label">Start</label><input type="datetime-local" class="form-control" name="starts_at"></div>
                        <div class="col-6"><label class="form-label">End</label><input type="datetime-local" class="form-control" name="ends_at"></div>
                    </div>
					<div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" id="isActive" checked><label class="form-check-label" for="isActive">Active</label></div>
				</div>
				<div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Close</button><button class="btn btn-primary" type="submit">Save</button></div>
			</form>
		</div>
	</div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function(){
  const srcPdf = document.getElementById('srcPdf');
  const srcUrl = document.getElementById('srcUrl');
  const pdfGroup = document.getElementById('pdfGroup');
  const urlGroup = document.getElementById('urlGroup');
  function sync(){
    const isUrl = srcUrl.checked;
    pdfGroup.classList.toggle('d-none', isUrl);
    urlGroup.classList.toggle('d-none', !isUrl);
  }
  srcPdf.addEventListener('change', sync); srcUrl.addEventListener('change', sync); sync();
});
</script>


