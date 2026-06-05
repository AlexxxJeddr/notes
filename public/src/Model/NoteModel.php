<?php
// Note Model
declare(strict_types=1);

namespace App\Model;

use PDO;

class NoteModel
{
    public function getAll(?int $folderId = null): array
    {
        $pdo = Database::getConnection();
        
        if ($folderId === null) {
            $stmt = $pdo->query("SELECT * FROM notes ORDER BY updated_at DESC");
        } else {
            $stmt = $pdo->prepare("SELECT * FROM notes WHERE folder_id = ? ORDER BY updated_at DESC");
            $stmt->execute([$folderId]);
        }
        
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM notes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $title, string $body, ?int $folderId = null): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO notes (title, body, folder_id) VALUES (?, ?, ?)");
        $stmt->execute([$title, $body, $folderId]);
        return (int)$pdo->lastInsertId();
    }

    public function update(int $id, string $title, string $body, ?int $folderId = null): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE notes SET title = ?, body = ?, folder_id = ? WHERE id = ?");
        return $stmt->execute([$title, $body, $folderId, $id]);
    }

    public function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM notes WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function moveToFolder(int $noteId, ?int $folderId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE notes SET folder_id = ? WHERE id = ?");
        return $stmt->execute([$folderId, $noteId]);
    }

    public function search(string $query): array
    {
        $pdo = Database::getConnection();
        $searchTerm = "%" . $query . "%";
        $stmt = $pdo->prepare("SELECT * FROM notes WHERE title LIKE ? OR body LIKE ? ORDER BY updated_at DESC");
        $stmt->execute([$searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
}
