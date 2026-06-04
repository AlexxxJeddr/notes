<?php
$title = 'Login';
$showSidebar = false;
ob_start();
?>

<div class="login-container">
    <h1>Notes</h1>
    <form method="POST" action="?action=authenticate" class="login-form">
        <?php if (isset($_SESSION['login_error'])): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($_SESSION['login_error']); unset($_SESSION['login_error']); ?>
            </div>
        <?php endif; ?>
        
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required autofocus>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn">Log In</button>
    </form>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
