<?php
require_once 'lib/common.php';

session_start();
if (!isLoggedIn())
{
    redirectAndExit('index.php');
}

if (!empty($_POST))
{
    if (isset($_POST['delete-user']))
    {
        $deleteResponse = $_POST['delete-user'];
        $deleteUserId = array_key_first($deleteResponse);  // Obtén la clave del primer elemento

        if ($deleteUserId !== null)
        {
            try {
                echo "<p>Deleting user with ID $deleteUserId</p>";
                $deleted = deleteUser(getPDO(), $deleteUserId);
                if ($deleted)
                {
                    redirectAndExit('list-users.php');
                }
                else
                {
                    echo "<p>Error: Could not delete the user.</p>";
                }
            } catch (Exception $e) {
                echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    }
}

$pdo = getPDO();
$users = getAllUsers($pdo);
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Cyberia | User list</title>
        <?php require 'templates/head.php' ?>
    </head>
    <body>
        <?php require 'templates/top-menu.php' ?>
        <h1>User list</h1>
        <p>You have <?php echo count($users) ?> users.</p>
        <form method="post">
            <table id="user-list">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Creation date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlEscape($user['username']) ?></td>
                            <td><?php echo htmlEscape($user['role']) ?></td>
                            <td><?php echo convertSqlDate($user['created_at']) ?></td>
                            <td>
                                <button type="submit" name="delete-user[<?php echo $user['id'] ?>]">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </form>
    </body>
</html>


