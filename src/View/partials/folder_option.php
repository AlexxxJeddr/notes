<option value="<?php echo $folder['id']; ?>"
    <?php echo (($currentFolderId ?? null) === $folder['id']) ? 'selected' : ''; ?>>
    <?php echo htmlspecialchars(str_repeat('&nbsp;&nbsp;', $depth ?? 0) . $folder['name']); ?>
</option>
<?php if (!empty($folder['children'])): ?>
    <?php 
    $depth = ($depth ?? 0) + 1;
    foreach ($folder['children'] as $child): 
        $currentFolderId = $currentFolderId ?? null;
        include __DIR__ . '/folder_option.php';
    endforeach;
    ?>
<?php endif; ?>
