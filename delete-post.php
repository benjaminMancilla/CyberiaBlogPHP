<?php 
require_once 'lib/edit-post.php';
require_once 'lib/common.php';
require_once 'lib/view-post.php';
require_once 'lib/list-posts.php';

session_start();

if (!isLoggedIn() || !isAdmin()) {
    redirectAndExit('index.php');
}


$postID = null;
if (isset($_GET['post_id']))
{
    $post = getPostRow(getPDO(), $_GET['post_id']);
    if ($post)
    {
        $postID = $_GET['post_id'];
        $title = $post['title'];
        $body = $post['body'];
    }
    else
    {
        redirectAndExit('index.php');
    }
}
else 
{
    redirectAndExit('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') 
    {
        deletePost(getPDO(), $postID);
        redirectAndExit('index.php');
    } 
    else 
    {
        redirectAndExit('index.php');
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Delete Post</title>
    <?php require 'templates/head.php' ?>
    <style>
        /* Aquí puedes añadir estilos específicos para esta página si es necesario */
    </style>
</head>
<body>
    <?php require 'templates/top-menu.php' ?>
    <div class="container">
        <div class="post">
            <h1>Delete Post</h1>
            <form method="post">
                <div class="box">
                    <p>Are you sure you want to delete the following post?</p>
                    <h2><?php echo htmlspecialchars($title); ?></h2>
                    <p><?php echo nl2br(htmlspecialchars($body)); ?></p>
                    <div>
                        <button type="submit" name="confirm" value="yes">Yes, delete it</button>
                        <button type="submit" name="confirm" value="no">No, keep it</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>



