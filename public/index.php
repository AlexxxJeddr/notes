<?php
// Front Controller
declare(strict_types=1);

// Debug: Always show this to confirm script is running
echo '<!-- DEBUG: index.php is executing -->';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// Global exception handler
set_exception_handler(function ($e) {
    echo "<div style='color: red; padding: 20px; border: 2px solid red; margin: 20px;'>";
    echo "<h2>Uncaught Exception</h2>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>";
    echo "<p><strong>Trace:</strong></p><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
});

// Global error handler
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    echo "<div style='color: orange; padding: 20px; border: 2px solid orange; margin: 20px;'>";
    echo "<h2>Error</h2>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($errstr) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($errfile) . ":" . $errline . "</p>";
    echo "</div>";
    return true;
});

// Start session
session_start();

// Autoload classes
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/src/';
    
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
$configDbPath = __DIR__ . '/config/db.php';
$configAuthPath = __DIR__ . '/config/auth.php';
error_log('DEBUG: Checking config files at: ' . $configDbPath . ' and ' . $configAuthPath);
error_log('DEBUG: db.php exists: ' . (file_exists($configDbPath) ? 'YES' : 'NO'));
error_log('DEBUG: auth.php exists: ' . (file_exists($configAuthPath) ? 'YES' : 'NO'));

if (!file_exists($configDbPath) || !file_exists($configAuthPath)) {
    echo '<div style="color: red; padding: 20px;">DEBUG: Config files missing. Looking for: ' . htmlspecialchars($configDbPath) . ' and ' . htmlspecialchars($configAuthPath) . '</div>';
    error_log('DEBUG: Redirecting to setup - config missing');
    header('Location: install/setup.php');
    exit;
}

error_log('DEBUG: Config files found, proceeding');

// Check authentication
use App\Model\Auth;

$action = $_GET['action'] ?? 'list';

// Debug: Log the action
error_log('DEBUG: Action requested: ' . $action);

// Public actions (no auth required)
$publicActions = ['login', 'authenticate'];

if (!Auth::isAuthenticated() && !in_array($action, $publicActions, true)) {
    error_log('DEBUG: Not authenticated, redirecting to login for action: ' . $action);
    header('Location: ?action=login');
    exit;
}

error_log('DEBUG: Authentication passed for action: ' . $action);

// Route to appropriate controller
error_log('DEBUG: Routing to action: ' . $action);
switch ($action) {
    case 'login':
        error_log('DEBUG: Routing to login');
        include __DIR__ . '/src/Controller/LoginController.php';
        (new App\Controller\LoginController())->showLogin();
        break;
    
    case 'authenticate':
        error_log('DEBUG: Routing to authenticate');
        include __DIR__ . '/src/Controller/LoginController.php';
        (new App\Controller\LoginController())->authenticate();
        break;
    
    case 'logout':
        error_log('DEBUG: Routing to logout');
        include __DIR__ . '/src/Controller/LoginController.php';
        (new App\Controller\LoginController())->logout();
        break;
    
    case 'list':
        error_log('DEBUG: Routing to list');
        include __DIR__ . '/src/Controller/NoteController.php';
        (new App\Controller\NoteController())->list();
        break;
    
    case 'edit':
        error_log('DEBUG: Routing to edit');
        include __DIR__ . '/src/Controller/NoteController.php';
        (new App\Controller\NoteController())->edit();
        break;
    
    case 'save':
        error_log('DEBUG: Routing to save');
        echo '<!-- DEBUG: save action triggered, POST data: ' . htmlspecialchars(print_r($_POST, true)) . ' -->';
        include __DIR__ . '/src/Controller/NoteController.php';
        (new App\Controller\NoteController())->save();
        break;
    
    case 'delete':
        include __DIR__ . '/src/Controller/NoteController.php';
        (new App\Controller\NoteController())->delete();
        break;
    
    case 'create_folder':
        include __DIR__ . '/src/Controller/FolderController.php';
        (new App\Controller\FolderController())->create();
        break;
    
    case 'delete_folder':
        include __DIR__ . '/src/Controller/FolderController.php';
        (new App\Controller\FolderController())->delete();
        break;
    
    default:
        header('Location: ?action=list');
        exit;
}
