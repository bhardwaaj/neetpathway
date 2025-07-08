<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Panel - NEET Pathway</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" href="assets/css/admin-style.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="assets/js/admin.js"></script></head>
<body class="admin-body"><div class="admin-wrapper"><nav class="navbar navbar-expand-lg navbar-dark bg-primary"><div class="container-fluid">
<a class="navbar-brand" href="dashboard.php"><div class="logo-container"><img src="../images/logo.PNG" alt="NEET Pathway" class="logo-image"></div></a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain"><span class="navbar-toggler-icon"></span></button>
<div class="collapse navbar-collapse" id="navbarMain"><ul class="navbar-nav me-auto">
<li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a></li>
<li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" href="users.php"><i class="fas fa-users me-1"></i>Users</a></li>
<li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>" href="orders.php"><i class="fas fa-shopping-cart me-1"></i>Orders</a></li>
<li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : ''; ?>" href="messages.php"><i class="fas fa-envelope me-1"></i>Messages</a></li>
<li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'chat.php' ? 'active' : ''; ?>" href="chat.php"><i class="fas fa-comments me-1"></i>Chat<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=neetpathway", "root", "");
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_chat_messages WHERE receiver_id = ? AND is_read = 0");
    $stmt->execute([$_SESSION['admin_id']]);
    $unread = $stmt->fetchColumn();
    if ($unread > 0) echo '<span class="badge bg-danger rounded-pill">' . $unread . '</span>';
} catch(PDOException $e) {
    error_log("Error checking unread messages: " . $e->getMessage());
}
?></a></li></ul>
<ul class="navbar-nav"><li class="nav-item dropdown"><a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
<i class="fas fa-user-circle me-1"></i><?php echo htmlspecialchars($_SESSION['admin_name']); ?>
<span class="badge bg-success ms-1"><?php echo ucfirst($_SESSION['admin_role']); ?></span></a>
<ul class="dropdown-menu dropdown-menu-end">
<li><a class="dropdown-item" href="../" target="_blank"><i class="fas fa-external-link-alt me-2"></i>View Website</a></li>
<li><hr class="dropdown-divider"></li>
<li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
</ul></li></ul></div></div></nav>
<main class="admin-content"><div class="container-fluid"><?php /* Page content will be inserted here */ ?></div></main></div></body></html> 