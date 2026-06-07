<?php
// Folder Controller
declare(strict_types=1);

namespace App\Controller;

use App\Model\FolderModel;

class FolderController
{
    private FolderModel $folderModel;

    public function __construct()
    {
        $this->folderModel = new FolderModel();
    }

    public function create(): void
    {
        $name = trim($_POST['name'] ?? '');
        $parentId = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? (int)$_POST['parent_id'] : null;
        
        if (empty($name)) {
            $_SESSION['error'] = 'Folder name is required.';
            header('Location: ?action=list');
            exit;
        }
        
        try {
            $this->folderModel->create($name, $parentId);
            header('Location: ?action=list');
            exit;
        } catch (\PDOException $e) {
            $_SESSION['error'] = 'Failed to create folder: ' . $e->getMessage();
            header('Location: ?action=list');
            exit;
        }
    }

    public function delete(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        
        if ($id !== null) {
            try {
                $this->folderModel->delete($id);
            } catch (\PDOException $e) {
                $_SESSION['error'] = 'Failed to delete folder: ' . $e->getMessage();
            }
        }
        
        header('Location: ?action=list');
        exit;
    }
}
