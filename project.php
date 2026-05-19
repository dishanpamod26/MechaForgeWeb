<?php
session_start();
require_once 'config/db.php';

// Fetch settings for logo and footer
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Defaults for settings
$defaults = [
    'logo' => 'assets/images/logo.jpeg'
];
foreach ($defaults as $key => $val) {
    if (!isset($settings[$key]) || empty($settings[$key])) {
        $settings[$key] = $val;
    }
}

$projectId = $_GET['id'] ?? null;
$project = null;

if ($projectId) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = :id");
    $stmt->execute([':id' => $projectId]);
    $project = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $project ? htmlspecialchars($project['title']) . " | MechaForge Engineering" : "Project Details | MechaForge Engineering"; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/d6f3576e01.js" crossorigin="anonymous"></script>
    <style>
        .details-container {
            padding-top: 100px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-left: 1rem;
            padding-right: 1rem;
        }
        .full-media {
            width: 100%;
            max-width: 900px;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            margin-bottom: 2rem;
            border: 1px solid var(--glass-border);
        }
        .project-content {
            max-width: 800px;
            width: 100%;
            background: var(--glass);
            padding: 3rem;
            border-radius: 20px;
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            margin-bottom: 3rem;
        }
        .project-content h1 {
            font-size: 2.2rem;
            margin-bottom: 1.5rem;
            color: var(--primary);
        }
        .project-content p {
            font-size: 1.1rem;
            color: var(--text-dim);
            line-height: 1.8;
            white-space: pre-wrap;
        }
        .back-btn {
            margin-bottom: 2rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text);
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }
        .back-btn:hover { color: var(--primary); }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <img src="<?php echo htmlspecialchars($settings['logo']); ?>" alt="MechaForge Engineering" id="site-logo" style="height: 40px; display: block;">
        </div>
        <ul class="nav-links">
            <li><a href="index.php">Back to Home</a></li>
        </ul>
    </nav>

    <div class="container details-container">
        <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Projects</a>

        <div id="project-details" style="width: 100%; display: flex; flex-direction: column; align-items: center;">
            <?php if ($project): ?>
                <?php if ($project['type'] === 'video'): ?>
                    <video class="full-media" src="<?php echo htmlspecialchars($project['url']); ?>" controls autoplay muted loop></video>
                <?php else: ?>
                    <img src="<?php echo htmlspecialchars($project['url']); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>" class="full-media">
                <?php endif; ?>
                <div class="project-content">
                    <h1><?php echo htmlspecialchars($project['title']); ?></h1>
                    <p><?php echo htmlspecialchars($project['description']); ?></p>
                </div>
            <?php else: ?>
                <div class="project-content">
                    <h1 style="color:var(--primary)">Project Not Found</h1>
                    <p>The project you are looking for does not exist or may have been removed.</p>
                    <br>
                    <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Go back to Home</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 MechaForge Engineering. All Rights Reserved.</p>
    </footer>
</body>
</html>
