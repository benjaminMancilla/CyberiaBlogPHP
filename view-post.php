<?php
require_once 'lib/common.php';
require_once 'lib/view-post.php';

session_start();

// Get the post ID
if (isset($_GET['post_id']))
{
    $postId = $_GET['post_id'];
}
else
{
    // So we always have a post ID var defined
    $postId = 0;
}

// Connect to the database, run a query, handle errors
$pdo = getPDO();
$row = getPostRow($pdo, $postId);
$commentCount = $row['comment_count'];

// If the post does not exist, let's deal with that here
if (!$row)
{
    redirectAndExit('index.php?not-found=1');
}

$errors = null;
if ($_POST)
{
    switch ($_GET['action'])
    {
        
        case 'add-comment':
            $commentData = array(
                'user_name' => $_POST['comment-name'],
                'website' => $_POST['comment-website'],
                'body' => $_POST['comment-text'],
            );
            $errors = handleAddComment($pdo, $postId, $commentData);
            break;
        case 'delete-comment':
            $deleteResponse = $_POST['delete-comment'];
            handleDeleteComment($pdo, $postId, $deleteResponse);
            break;

        case 'edit-comment':
            // Obtener el ID del comentario del array de la solicitud POST
            $editResponse = $_POST['edit-comment'];
            foreach ($editResponse as $commentId => $editText) {
                handleEditComment($pdo, $commentId, $editText);
            }
            break;
    }
}
else
{
    $commentData = array(
        'user_name' => '',
        'website' => '',
        'body' => '',
    );
}


?>
<!DOCTYPE html>
<html>
    <head>
        <title>
            Cyberia |
            <?php echo htmlEscape($row['title']) ?>
        </title>
        <?php require 'templates/head.php' ?>
    </head>
    <body>
        <?php require 'templates/title.php' ?>

        <div class="post">
            <h2>
                <?php echo htmlEscape($row['title']) ?>
            </h2>
            <h3>
                <a href="profile.php?profile_id=<?php echo $row['user_id']?>">
                <?php echo htmlEscape($row['author']) ?>
                </a>
            </h3>
            <div class="date">
                <?php echo convertSqlDate($row['created_at']) ?>
            </div>
            <?php if ($row['thumbnail']): ?>

                <div class="post-thumbnail-container">
                    <?php echo renderPostThumbnail($row['thumbnail'], "Thumbnail for " . htmlEscape($row['title'])); ?>
                </div>

            <?php endif ?>

            <?php // This is already escaped, so doesn't need further escaping ?>
            <?php echo convertNewlinesToParagraphs($row['body']) ?>
        </div>

        <?php require 'templates/list-comments.php' ?>

        <?php require 'templates/comment-form.php' ?>
    </body>
</html>