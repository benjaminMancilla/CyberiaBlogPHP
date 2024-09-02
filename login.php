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

    <div class="main-logo-container">
        <a href="index.php" class="main-logo">
            <img src="assets/images/logo.png" alt="Background Image">
        </a>
        <div class="main-logo">
            <img src="assets/images/p-logo.png" alt="Background Image">
        </div>
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
        <div id="login-container" class="login-container" style="display: block;">
            <h1>Login here:</h1>
            <form method="post" action="login.php?action=login" class="user-form" >
                <div>
                    <input type="text" id="username" name="username" placeholder="Username" value="<?php echo htmlEscape($username) ?>" />
                </div>
                <div>
                    <input type="password" id="password" name="password" placeholder="Password" />
                </div>
                <div id="sumbit-login-button">
                    <input type="submit" value="Login" />
                </div>
            </form>
            <button onclick="toggleForms()">Sign up</button>
        </div>

        <!-- Signup Form (Initially Hidden) -->
        <div id="signup-container" class="signup-container" style="display: none;">
            <h1>Sign up here:</h1>
            <form method="post" action="login.php?action=signup" class="user-form">
                <div>
                    <input type="text" id="username" name="username" placeholder="Username" value="<?php echo htmlEscape($username) ?>" />
                </div>
                <div>
                    <input type="password" id="password" name="password" placeholder="Password" />
                </div>
                <div id="sumbit-login-button">
                    <input type="submit" value="Sign up" />
                </div>
            </form>
            <button onclick="toggleForms()">Login</button>
        </div>
    </div>

    <?php require 'templates/reserved-rights.php' ?>

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
