<?php
require_once 'lib/common.php';
require_once 'lib/login.php';
// We need to test for a minimum version of PHP, because earlier versions have bugs that affect security
if (version_compare(PHP_VERSION, '5.3.7') < 0)
{
    throw new Exception(
        'This system needs PHP 5.3.7 or later'
    );
}
session_start();

// If the user is already logged in, redirect them home
if (isLoggedIn())
{
    redirectAndExit('index.php');
}
// Handle the form posting
$username = '';
if ($_POST)
{
    switch ($_GET['action'])
    {
        case 'login':
            $username = $_POST['username'];
            $password = $_POST['password'];
            $ok = tryLogin(getPDO(), $username, $password);
            if ($ok)
            {
                login($username);
                redirectAndExit('index.php');
            }
            break;
        case 'signup':
            $username = $_POST['username'];
            $password = $_POST['password'];
            $ok = trySignup(getPDO(), $username, $password);
            if ($ok)
            {
                login($username);
                redirectAndExit('index.php');
            }
            break;
    }

    
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <?php require 'templates/head.php' ?>
    <link rel="stylesheet" href="assets/login.css" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <?php //<script src="assets/login-bg.js"></script> ?>

    <div class="main-logo">
        <a href="index.php" class="no-class">
            <img src="assets/images/logo.png" alt="Background Image">
        </a>
    </div>

    <div class="login-main-container">
        <?php if ($username): ?>
            <?php if ($username === 'anonymous'): ?>
                <div class="error box">
                    :)
                </div>
            <?php else: ?>
                <div class="error box">
                    The username or password is incorrect, try again
                </div>
            <?php endif ?>
        <?php endif ?>

        <!-- Login Form -->
        <div id="login-container" class="login-container">
            <p>Login here:</p>
            <form method="post" action="login.php?action=login" class="user-form">
                <div>
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlEscape($username) ?>" />
                </div>
                <div>
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" />
                </div>
                <p>
                    <input type="submit" value="Login" />
                </p>
            </form>
            <button onclick="toggleForms()">Sign up</button>
        </div>

        <!-- Signup Form (Initially Hidden) -->
        <div id="signup-container" class="signup-container" style="display: none;">
            <p>Sign up here:</p>
            <form method="post" action="login.php?action=signup" class="user-form">
                <div>
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlEscape($username) ?>" />
                </div>
                <div>
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" />
                </div>
                <p>
                    <input type="submit" value="Sign up" />
                </p>
            </form>
            <button onclick="toggleForms()">Login</button>
        </div>
    </div>

    <script>
        function toggleForms() {
            var loginContainer = document.getElementById('login-container');
            var signupContainer = document.getElementById('signup-container');
            
            if (loginContainer.style.display === 'none') {
                loginContainer.style.display = 'block';
                signupContainer.style.display = 'none';
            } else {
                loginContainer.style.display = 'none';
                signupContainer.style.display = 'block';
            }
        }
    </script>
</body>
</html>
