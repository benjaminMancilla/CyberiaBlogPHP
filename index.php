<?php
require_once 'lib/common.php';
session_start();
$pdo = getPDO();
$posts = getAllPosts($pdo);
$notFound = isset($_GET['not-found']);
$profile = getAuthProfile($pdo);
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Cyberia</title>
        <?php require 'templates/head.php' ?>
    </head>
    <body>
        <?php require 'templates/top-menu.php' ?>
        <?php require 'templates/sidebar-left.php' ?>
        <?php require 'templates/bg-logo.php' ?>
        
        <div class="main-container">
            <div class="content-container">
                <div class="post-list">
                    <?php foreach ($posts as $post): ?>
                        <?php
                        $authAvatar = getProfileAvatar($pdo, $post['user_id']);
                        $authVisibleName = getUserVisiblleName($pdo, $post['user_id']);
                        ?>
                        <div class="post-synopsis">
                            <div class="post-main-container">
                                <div class="post-creation-info">
                                    <a href="profile.php?profile_id=<?php echo $post['user_id'] ?>" class="post-author-avatar">
                                        <?php echo renderProfileImage($authAvatar, 'small'); ?>
                                    </a>
                                    <a href="profile.php?profile_id=<?php echo $post['user_id'] ?>" class="post-author-name">
                                        <?php echo htmlEscape($authVisibleName) ?>
                                    </a>
                                    <div class="meta">
                                        <?php echo convertSqlDate($post['created_at']) ?>
                                        (<?php echo $post['comment_count'] ?> comments)
                                    </div>
                                </div>
                                <h2>
                                    <?php echo htmlEscape($post['title']) ?>
                                </h2>
                                
                                <div class="post-body">
                                    <p><?php echo convertNewlinesToSumary($post['body']) ?></p>
                                </div>
                                <?php if ($post['image']): ?>
                                    <div class="post-index-image-container">
                                        <div class="post-index-image">
                                            <?php echo renderPostImageFull($post['image']) ?>
                                        </div>
                                    </div>
                                <?php endif ?>
                                <div class="post-controls">
                                    <a href="view-post.php?post_id=<?php echo $post['id'] ?>">Read more...</a>
                                    <?php if (isLoggedIn()): ?>
                                        | <a href="edit-post.php?post_id=<?php echo $post['id'] ?>">Edit</a>
                                        | <a href="delete-post.php?post_id=<?php echo $post['id'] ?>">Delete</a>
                                    <?php endif ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>
            </div>

            <!-- Espacio para futura sidebar derecha -->
        </div>

    </body>
</html>

