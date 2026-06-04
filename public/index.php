<?php
// Front Controller
declare(strict_types=1);

// Start session
session_start();

// Autoload classes
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Check if config exists
if (!file_exists(__DIR__ . '/../config/db.php') || !file_exists(__DIR__ . '/../config/auth.php')) {
    header('Location: ../install/setup.php');
    exit;
}

// Check authentication
use App\Model\Auth;

$action = $_GET['action'] ?? 'list';

// Public actions (no auth required)
$publicActions = ['login', 'authenticate'];

if (!Auth::isAuthenticated() && !in_array($action, $publicActions, true)) {
    header('Location: ?action=login');
    exit;
}

// Route to appropriate controller
switch ($action) {
    case 'login':
        include __DIR__ . '/../src/Controller/LoginController.php';
        (new App\Controller\LoginController())->showLogin();
        break;
    
    case 'authenticate':
        include __DIR__ . '/../src/Controller/LoginController.php';
        (new App\Controller\LoginController())->authenticate();
        break;
    
    case 'logout':
        include __DIR__ . '/../src/Controller/LoginController.php';
        (new App\Controller\LoginController())->logout();
        break;
    
    case 'list':
        include __DIR__ . '/../src/Controller/NoteController.php';
        (new App\Controller\NoteController())->list();
        break;
    
    case 'edit':
        include __DIR__ . '/../src/Controller/NoteController.php';
        (new App\Controller\NoteController())->edit();
        break;
    
    case 'save':
        include __DIR__ . '/../src/Controller/NoteController.php';
        (new App\Controller\NoteController())->save();
        break;
    
    case 'delete':
        include __DIR__ . '/../src/Controller/NoteController.php';
        (new App\Controller\NoteController())->delete();
        break;
    
    case 'create_folder':
        include __DIR__ . '/../src/Controller/FolderController.php';
        (new App\Controller\FolderController())->create();
        break;
    
    case 'delete_folder':
        include __DIR__ . '/../src/Controller/FolderController.php';
        (new App\Controller\FolderController())->delete();
        break;
    
    default:
        header('Location: ?action=list');
        exit;
}
