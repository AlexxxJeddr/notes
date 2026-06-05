<li>
    <a href="?action=list&folder_id=<?php echo $folder['id']; ?>" 
       class="<?php echo ($currentFolderId === $folder['id']) ? 'active' : ''; ?>">
        <?php echo htmlspecialchars($folder['name']); ?>
    </a>
    <?php if (!empty($folder['children'])): ?>
        <ul>
            <?php foreach ($folder['children'] as $child): ?>
                <?php 
                $currentFolderId = $currentFolderId ?? null;
                include __DIR__ . '/folder_item.php';
                ?>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <a href="?action=delete_folder&id=<?php echo $folder['id']; ?>" 
       class="delete-folder" 
       onclick="return confirm('Delete folder and move notes to root?')">
        &times;
    </a>
</li>
