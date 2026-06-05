<?php
// Login Controller
declare(strict_types=1);

namespace App\Controller;

use App\Model\Auth;

class LoginController
{
    public function showLogin(): void
    {
        include __DIR__ . '/../View/login.php';
    }

    public function authenticate(): void
    {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (Auth::login($username, $password)) {
            header('Location: ?action=list');
            exit;
        }
        
        // Show error
        $_SESSION['login_error'] = 'Invalid username or password.';
        header('Location: ?action=login');
        exit;
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: ?action=login');
        exit;
    }
}
