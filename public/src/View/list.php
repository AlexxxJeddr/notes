<?php
$title = $folder ? htmlspecialchars($folder['name']) : 'All Notes';
$showSidebar = true;
$currentFolderId = $folder ? $folder['id'] : null;
ob_start();
?>

<div class="notes-header">
    <h2><?php echo $title; ?></h2>
    <a href="?action=edit" class="btn">New Note</a>
</div>

<?php if (empty($notes)): ?>
    <div class="empty-state">
        <p>No notes yet.</p>
        <p>Create your first note to get started.</p>
    </div>
<?php else: ?>
    <div class="notes-list">
        <?php foreach ($notes as $note): ?>
            <article class="note-card">
                <div class="note-header">
                    <h3>
                        <a href="?action=edit&id=<?php echo $note['id']; ?>">
                            <?php echo htmlspecialchars($note['title']); ?>
                        </a>
                    </h3>
                    <span class="note-date">
                        <?php echo date('M j, Y', strtotime($note['updated_at'])); ?>
                    </span>
                </div>
                <div class="note-preview">
                    <?php 
                    $parsed = App\Model\NoteModel::parseMarkdown($note['body']);
                    $preview = strip_tags($parsed);
                    echo htmlspecialchars(substr($preview, 0, 150)) . (strlen($preview) > 150 ? '...' : '');
                    ?>
                </div>
                <div class="note-actions">
                    <a href="?action=delete&id=<?php echo $note['id']; ?>" 
                       onclick="return confirm('Delete this note?')" 
                       class="delete-btn">
                        Delete
                    </a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
