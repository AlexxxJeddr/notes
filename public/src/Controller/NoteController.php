<?php
// Note Controller
declare(strict_types=1);

namespace App\Controller;

use App\Model\NoteModel;
use App\Model\FolderModel;

class NoteController
{
    private NoteModel $noteModel;
    private FolderModel $folderModel;

    public function __construct()
    {
        $this->noteModel = new NoteModel();
        $this->folderModel = new FolderModel();
    }

    public function list(): void
    {
        $folderId = isset($_GET['folder_id']) ? (int)$_GET['folder_id'] : null;
        $folder = null;
        
        if ($folderId !== null) {
            $folder = $this->folderModel->getById($folderId);
        }
        
        $notes = $this->noteModel->getAll($folderId);
        $folders = $this->folderModel->getTree();
        
        include __DIR__ . '/../View/list.php';
    }

    public function edit(): void
    {
        $noteId = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $note = null;
        
        if ($noteId !== null) {
            $note = $this->noteModel->getById($noteId);
        }
        
        $folders = $this->folderModel->getTree();
        
        include __DIR__ . '/../View/edit.php';
    }

    public function save(): void
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
        $title = trim($_POST['title'] ?? '');
        $body = $_POST['body'] ?? '';
        $folderId = isset($_POST['folder_id']) && $_POST['folder_id'] !== '' ? (int)$_POST['folder_id'] : null;
        
        if (empty($title)) {
            $_SESSION['error'] = 'Title is required.';
            header('Location: ?action=edit&id=' . $id);
            exit;
        }
        
        try {
            if ($id === null) {
                $this->noteModel->create($title, $body, $folderId);
            } else {
                $this->noteModel->update($id, $title, $body, $folderId);
            }
            header('Location: ?action=list' . ($folderId !== null ? '&folder_id=' . $folderId : ''));
            exit;
        } catch (\PDOException $e) {
            $_SESSION['error'] = 'Failed to save note: ' . $e->getMessage();
            header('Location: ?action=edit&id=' . $id);
            exit;
        }
    }

    public function delete(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        
        if ($id !== null) {
            try {
                $note = $this->noteModel->getById($id);
                $folderId = $note['folder_id'] ?? null;
                $this->noteModel->delete($id);
                header('Location: ?action=list' . ($folderId !== null ? '&folder_id=' . $folderId : ''));
                exit;
            } catch (\PDOException $e) {
                $_SESSION['error'] = 'Failed to delete note: ' . $e->getMessage();
            }
        }
        
        header('Location: ?action=list');
        exit;
    }
}
