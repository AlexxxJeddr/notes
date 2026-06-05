<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Notes'); ?></title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
    <div class="app">
        <?php if (isset($showSidebar) && $showSidebar): ?>
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1><a href="?action=list">Notes</a></h1>
            </div>
            <nav class="folder-nav">
                <ul>
                    <li>
                        <a href="?action=list" class="<?php echo ($currentFolderId === null) ? 'active' : ''; ?>">
                            All Notes
                        </a>
                    </li>
                    <?php foreach ($folders as $folder): ?>
                        <?php include __DIR__ . '/partials/folder_item.php'; ?>
                    <?php endforeach; ?>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <form method="POST" action="?action=create_folder" class="folder-form">
                    <input type="text" name="name" placeholder="New folder..." required>
                    <input type="hidden" name="parent_id" value="">
                    <button type="submit">+</button>
                </form>
                <a href="?action=logout" class="logout-btn">Logout</a>
            </div>
        </aside>
        <?php endif; ?>
        
        <main class="main-content">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php echo $content ?? ''; ?>
        </main>
    </div>
</body>
</html>
