<?php
require_once '../config/db.php';

// Authentication Check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../index.php?login_error=Access Denied. Please login first.");
    exit;
}

// Generate CSRF Token if not present
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Ensure uploads directory exists
if (!file_exists('../uploads')) {
    mkdir('../uploads', 0777, true);
}

$success_msg = $_GET['success'] ?? '';
$error_msg = $_GET['error'] ?? '';
$active_tab = $_GET['tab'] ?? 'projects-tab';

// Helper to update settings
function update_setting($pdo, $key, $value) {
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (:key, :val) ON DUPLICATE KEY UPDATE setting_value = :val2");
    $stmt->execute([
        ':key' => $key,
        ':val' => $value,
        ':val2' => $value
    ]);
}

// POST Action Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        header("Location: dashboard.php?tab=" . htmlspecialchars($active_tab) . "&error=Security Token Validation Failed.");
        exit;
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'add_project') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $type = $_POST['type'] ?? 'image';
        
        if (empty($title)) {
            header("Location: dashboard.php?tab=projects-tab&error=Project title is required.");
            exit;
        }

        if (!isset($_FILES['proj-file']) || $_FILES['proj-file']['error'] !== UPLOAD_ERR_OK) {
            header("Location: dashboard.php?tab=projects-tab&error=Please upload a media file.");
            exit;
        }

        // Handle File Upload
        $file = $_FILES['proj-file'];
        
        // 1. Extension validation
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'webm', 'mov', 'avi'];
        if (!in_array(strtolower($ext), $allowed_exts)) {
            header("Location: dashboard.php?tab=projects-tab&error=Invalid file extension.");
            exit;
        }

        // 2. MIME type validation (XSS & File Execution prevention)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        $allowed_mimes = [
            'image/jpeg', 'image/png', 'image/gif', 
            'video/mp4', 'video/webm', 'video/quicktime', 'video/x-msvideo'
        ];
        if (!in_array($mime_type, $allowed_mimes)) {
            header("Location: dashboard.php?tab=projects-tab&error=Invalid file format (MIME type mismatch).");
            exit;
        }

        $new_filename = 'project_' . time() . '_' . uniqid() . '.' . $ext;
        $filesystem_path = '../uploads/' . $new_filename;
        $db_path = 'uploads/' . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $filesystem_path)) {
            // Save to database
            $stmt = $pdo->prepare("INSERT INTO projects (title, description, type, url) VALUES (:title, :description, :type, :url)");
            $stmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':type' => $type,
                ':url' => $db_path
            ]);
            header("Location: dashboard.php?tab=projects-tab&success=Project published successfully!");
            exit;
        } else {
            header("Location: dashboard.php?tab=projects-tab&error=Failed to save uploaded file.");
            exit;
        }
    }

    if ($action === 'delete_project') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            // Retrieve file path to delete it
            $stmt = $pdo->prepare("SELECT url FROM projects WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $proj = $stmt->fetch();

            if ($proj) {
                $file_path = $proj['url'];
                $filesystem_path = '../' . $file_path;
                if (file_exists($filesystem_path) && strpos($file_path, 'uploads/') === 0) {
                    unlink($filesystem_path);
                }

                // Delete from DB
                $stmt = $pdo->prepare("DELETE FROM projects WHERE id = :id");
                $stmt->execute([':id' => $id]);
                header("Location: dashboard.php?tab=projects-tab&success=Project deleted successfully.");
                exit;
            }
        }
        header("Location: dashboard.php?tab=projects-tab&error=Invalid project ID.");
        exit;
    }

    if ($action === 'update_settings') {
        $slogan = trim($_POST['slogan'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (!empty($slogan)) update_setting($pdo, 'slogan', $slogan);
        if (!empty($email)) update_setting($pdo, 'email', $email);
        if (!empty($phone)) update_setting($pdo, 'phone', $phone);

        // Logo Upload
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['logo'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'svg'])) {
                // Validate MIME
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                $allowed_logo_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
                if (in_array($mime_type, $allowed_logo_mimes)) {
                    $new_filename = 'logo_' . time() . '.' . $ext;
                    $filesystem_path = '../uploads/' . $new_filename;
                    $db_path = 'uploads/' . $new_filename;
                    if (move_uploaded_file($file['tmp_name'], $filesystem_path)) {
                        update_setting($pdo, 'logo', $db_path);
                    }
                }
            }
        }

        // Slideshow Backgrounds Upload
        if (isset($_FILES['backgrounds']) && count($_FILES['backgrounds']['name']) > 0 && !empty($_FILES['backgrounds']['name'][0])) {
            $files = $_FILES['backgrounds'];
            $uploaded_paths = [];
            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                    if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif'])) {
                        // Validate MIME
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mime_type = finfo_file($finfo, $files['tmp_name'][$i]);
                        finfo_close($finfo);
                        $allowed_bg_mimes = ['image/jpeg', 'image/png', 'image/gif'];
                        if (in_array($mime_type, $allowed_bg_mimes)) {
                            $new_filename = 'bg_' . time() . '_' . $i . '.' . $ext;
                            $filesystem_path = '../uploads/' . $new_filename;
                            $db_path = 'uploads/' . $new_filename;
                            if (move_uploaded_file($files['tmp_name'][$i], $filesystem_path)) {
                                $uploaded_paths[] = $db_path;
                            }
                        }
                    }
                }
            }
            if (!empty($uploaded_paths)) {
                update_setting($pdo, 'backgrounds', json_encode($uploaded_paths));
            }
        }

        header("Location: dashboard.php?tab=settings-tab&success=Branding settings updated successfully!");
        exit;
    }
}

// Fetch settings for pre-filling
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Fetch all projects for listing
$stmt = $pdo->query("SELECT id, title FROM projects ORDER BY id DESC");
$projects = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | MechaForge Engineering</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/d6f3576e01.js" crossorigin="anonymous"></script>
    <style>
        body {
            background: #050508;
            padding: 40px;
        }
        .admin-view-container {
            max-width: 1000px;
            margin: 0 auto;
            background: var(--glass);
            padding: 40px;
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(20px);
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="admin-view-container">
        <div class="admin-header">
            <h2><i class="fas fa-user-shield"></i> Admin Control Center</h2>
            <div class="admin-tabs">
                <button id="tab-btn-projects" onclick="showTab('projects-tab')" class="btn-secondary <?php echo $active_tab === 'projects-tab' ? 'active' : ''; ?>">Projects</button>
                <button id="tab-btn-settings" onclick="showTab('settings-tab')" class="btn-secondary <?php echo $active_tab === 'settings-tab' ? 'active' : ''; ?>">General Settings</button>
            </div>
        </div>

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_msg); ?></div>
        <?php endif; ?>
        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>

        <!-- Projects Tab -->
        <div id="projects-tab" class="tab-content admin-card <?php echo $active_tab === 'projects-tab' ? '' : 'hidden'; ?>">
            <h3><i class="fas fa-plus-circle"></i> Add New Project</h3>
            <form action="dashboard.php" method="POST" enctype="multipart/form-data" class="upload-form">
                <input type="hidden" name="action" value="add_project">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                
                <div class="input-group">
                    <label>Project Title</label>
                    <input type="text" name="title" placeholder="Project Title" required>
                </div>
                
                <div class="input-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Project Description" rows="4" required></textarea>
                </div>
                
                <div class="file-inputs grid-2">
                    <div>
                        <label>Media Type</label>
                        <select name="type">
                            <option value="image">Image (PNG/JPG)</option>
                            <option value="video">Video (MP4/WebM)</option>
                        </select>
                    </div>
                    <div>
                        <label>File Upload</label>
                        <input type="file" name="proj-file" accept="image/*,video/*" required>
                    </div>
                </div>
                <button type="submit" class="btn-primary" style="margin-top: 1rem;">Publish to Website</button>
            </form>

            <hr style="margin: 2rem 0; border: 0; border-top: 1px solid var(--glass-border);">
            
            <h3>Current Projects</h3>
            <div id="projects-list-admin" class="projects-list-compact" style="margin-top: 1rem;">
                <?php if (empty($projects)): ?>
                    <p style="color: var(--text-dim);">No projects found.</p>
                <?php else: ?>
                    <?php foreach ($projects as $proj): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid var(--glass-border);">
                            <span><?php echo htmlspecialchars($proj['title']); ?></span>
                            <form action="dashboard.php" method="POST" onsubmit="return confirm('Delete this project?');">
                                <input type="hidden" name="action" value="delete_project">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <input type="hidden" name="id" value="<?php echo $proj['id']; ?>">
                                <button type="submit" class="btn-outline" style="padding: 5px 12px; font-size: 0.8rem;">Delete</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Settings Tab -->
        <div id="settings-tab" class="tab-content admin-card <?php echo $active_tab === 'settings-tab' ? '' : 'hidden'; ?>">
            <h3><i class="fas fa-cog"></i> Website Branding</h3>
            <form action="dashboard.php" method="POST" enctype="multipart/form-data" class="upload-form">
                <input type="hidden" name="action" value="update_settings">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                
                <div class="grid-2">
                    <div class="input-group">
                        <label>Company Logo</label>
                        <input type="file" name="logo" accept="image/*">
                        <span class="small-hint">Current: <?php echo htmlspecialchars(basename($settings['logo'] ?? 'logo.jpeg')); ?></span>
                    </div>
                    <div class="input-group">
                        <label>Hero Slogan</label>
                        <input type="text" name="slogan" value="<?php echo htmlspecialchars($settings['slogan'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="grid-2">
                    <div class="input-group">
                        <label>Contact Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($settings['email'] ?? ''); ?>" required>
                    </div>
                    <div class="input-group">
                        <label>Contact Phone</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($settings['phone'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="input-group">
                    <label>Hero Slideshow Backgrounds (Upload multiple images)</label>
                    <input type="file" name="backgrounds[]" accept="image/*" multiple>
                    <span class="small-hint">Current files: 
                    <?php 
                    $bgs = json_decode($settings['backgrounds'] ?? '[]', true);
                    if (is_array($bgs)) {
                        echo htmlspecialchars(implode(', ', array_map('basename', $bgs)));
                    } else {
                        echo 'hero-bg.png';
                    }
                    ?>
                    </span>
                </div>
                
                <button type="submit" class="btn-primary" style="margin-top: 1rem;">Save Changes</button>
            </form>
        </div>

        <div class="admin-footer">
            <a href="logout.php" class="btn-outline" style="text-decoration: none;">Logout & Exit Dashboard</a>
        </div>
    </div>

    <script>
        function showTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.add('hidden'));
            document.querySelectorAll('.admin-tabs .btn-secondary').forEach(b => b.classList.remove('active'));
            document.getElementById(tabId).classList.remove('hidden');
            if (tabId === 'projects-tab') document.getElementById('tab-btn-projects').classList.add('active');
            if (tabId === 'settings-tab') document.getElementById('tab-btn-settings').classList.add('active');
            
            // Update URL to preserve tab state
            const url = new URL(window.location);
            url.searchParams.set('tab', tabId);
            window.history.replaceState({}, '', url);
        }
    </script>
</body>
</html>
