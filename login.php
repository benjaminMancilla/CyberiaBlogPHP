<?php
require_once 'lib/common.php';
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
    </head>
    <body>
        <?php require 'templates/title.php' ?>

        <?php // If we have a username, then the user got something wrong, so let's have an error ?>
        <?php if ($username): ?>
            <div class="error box">
                The username or password is incorrect, try again
            </div>
        <?php endif ?>

        <p>Login here:</p>
        <form
            method="post"
            action="login.php?action=login"
            class="user-form"
        >
            <div>
            <label for="username">
                    Username:
                </label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    value="<?php echo htmlEscape($username) ?>"
                />
            </div>
            <div>
                <label for="password">
                    Password:
                </label>
                <input
                    type="password"
                    id="password"
                    name="password"
                />
            </div>
            <p>
                <input type="submit" value="Login" />
            </p>
        </form>
        <p>Sign up here:</p>
        <form
            method="post"
            action="login.php?action=signup"
            class="user-form"
        >
            <div>
            <label for="username">
                    Username:
                </label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    value="<?php echo htmlEscape($username) ?>"
                />
            </div>
            <div>
                <label for="password">
                    Password:
                </label>
                <input
                    type="password"
                    id="password"
                    name="password"
                />
            </div>
            <p>
                <input type="submit" value="Sign up" />
            </p>
        </form>

    </body>
</html>
