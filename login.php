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
    $pdo = getPDO();
    // We redirect only if the password is correct
    $username = $_POST['username'];
    $ok = tryLogin($pdo, $username, $_POST['password']);
    if ($ok)
    {
        login($username);
        redirectAndExit('index.php');
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
            action="login.php"
        >
            <p>
                Username:
                <input
                    type="text"
                    name="username"
                    value="<?php echo htmlEscape($username) ?>"
                />
            </p>
            <p>
                Password:
                <input type="password" name="password" />
            </p>
            <p>
                <input type="submit" value="Login" />
            </p>
        </form>
    </body>
</html>
