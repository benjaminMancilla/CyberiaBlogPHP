<?php
require_once 'lib/common.php';
session_start();
$pdo = getPDO();
$posts = getAllPosts($pdo);
$notFound = isset($_GET['not-found']);
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Cyberia</title>
        <?php require 'templates/head.php' ?>
    </head>
    <body>
        <?php require 'templates/title.php' ?>
        
        <?php if ($notFound): ?>
            <div style="border: 1px solid #ff6666; padding: 6px;">
                Error: cannot find the requested blog post
            </div>
        <?php endif ?>

        <div class="post-list">
            <?php foreach ($posts as $post): ?>
                <div class="post-synopsis">
                    <h2>
                        <?php echo htmlEscape($post['title']) ?>
                    </h2>
                    <div class="meta">
                        <?php echo convertSqlDate($post['created_at']) ?>
                        (<?php echo $post['comment_count'] ?> comments)
                    </div>
                    <p>
                        <?php echo convertNewlinesToParagraphs($post['body']) ?>
                    </p>
                    <div class="post-controls">
                        <a
                            href="view-post.php?post_id=<?php echo $post['id'] ?>"
                        >Read more...</a>
                        <?php if (isLoggedIn()): ?>
                            |
                            <a
                                href="edit-post.php?post_id=<?php echo $post['id'] ?>"
                            >Edit</a>
                            |
                            <a
                                href="delete-post.php?post_id=<?php echo $post['id'] ?>"
                            >Delete</a>
                        <?php endif ?>
                    </div>
                </div>
            <?php endforeach ?>
        </div>

    </body>
</html>    