<?php
// Authentication Handler
declare(strict_types=1);

namespace App\Model;

class Auth
{
    public static function check(string $username, string $password): bool
    {
        $config = require __DIR__ . '/../../../config/auth.php';
        
        if ($username === $config['username']) {
            return password_verify($password, $config['password_hash']);
        }
        
        return false;
    }

    public static function isAuthenticated(): bool
    {
        return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
    }

    public static function login(string $username, string $password): bool
    {
        if (self::check($username, $password)) {
            $_SESSION['authenticated'] = true;
            $_SESSION['username'] = $username;
            return true;
        }
        return false;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
    }
}
