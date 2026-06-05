<?php
$title = $note ? htmlspecialchars($note['title']) : 'New Note';
$showSidebar = true;
$currentFolderId = $note ? $note['folder_id'] : null;
ob_start();
?>

<form method="POST" action="?action=save" class="note-form">
    <input type="hidden" name="id" value="<?php echo $note ? $note['id'] : ''; ?>">
    
    <div class="form-group">
        <label for="title">Title</label>
        <input type="text" 
               id="title" 
               name="title" 
               value="<?php echo $note ? htmlspecialchars($note['title']) : ''; ?>" 
               required 
               autofocus>
    </div>
    
    <div class="form-group">
        <label for="folder_id">Folder</label>
        <select id="folder_id" name="folder_id">
            <option value="">-- No Folder --</option>
            <?php foreach ($folders as $folder): ?>
                <?php include __DIR__ . '/partials/folder_option.php'; ?>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="form-group">
        <label for="body">Body</label>
        <textarea id="body" 
                  name="body" 
                  rows="20" 
                  required><?php echo $note ? htmlspecialchars($note['body']) : ''; ?></textarea>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn">Save</button>
        <a href="?action=list" class="btn btn-secondary">Cancel</a>
        <?php if ($note): ?>
            <a href="?action=delete&id=<?php echo $note['id']; ?>" 
               onclick="return confirm('Delete this note?')" 
               class="btn btn-danger">
                Delete
            </a>
        <?php endif; ?>
    </div>
</form>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
