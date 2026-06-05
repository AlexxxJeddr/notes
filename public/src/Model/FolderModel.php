<?php
// Folder Model
declare(strict_types=1);

namespace App\Model;

use PDO;

class FolderModel
{
    public function getAll(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM folders ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM folders WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getChildren(int $parentId = null): array
    {
        $pdo = Database::getConnection();
        $parentId ??= null;
        $stmt = $pdo->prepare("SELECT * FROM folders WHERE parent_id IS NULL OR parent_id = ? ORDER BY name ASC");
        $stmt->execute([$parentId]);
        return $stmt->fetchAll();
    }

    public function create(string $name, ?int $parentId = null): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO folders (name, parent_id) VALUES (?, ?)");
        $stmt->execute([$name, $parentId]);
        return (int)$pdo->lastInsertId();
    }

    public function update(int $id, string $name, ?int $parentId = null): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE folders SET name = ?, parent_id = ? WHERE id = ?");
        return $stmt->execute([$name, $parentId, $id]);
    }

    public function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        // Move notes in this folder to root
        $pdo->prepare("UPDATE notes SET folder_id = NULL WHERE folder_id = ?")->execute([$id]);
        // Delete folder (cascade will handle children)
        $stmt = $pdo->prepare("DELETE FROM folders WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getTree(): array
    {
        $folders = $this->getAll();
        return $this->buildTree($folders);
    }

    private function buildTree(array $folders, ?int $parentId = null): array
    {
        $tree = [];
        foreach ($folders as $folder) {
            if ($folder['parent_id'] === $parentId) {
                $folder['children'] = $this->buildTree($folders, $folder['id']);
                $tree[] = $folder;
            }
        }
        return $tree;
    }
}
