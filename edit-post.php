<?php
require_once 'lib/common.php';
require_once 'lib/edit-post.php';
require_once 'lib/view-post.php';
session_start();


// Empty defaults
$title = $body = '';
// Init database and get handle
$pdo = getPDO();

$postId = null;
if (isset($_GET['post_id']))
{
    $post = getPostRow($pdo, $_GET['post_id']);
    if ($post)
    {
        $postId = $_GET['post_id'];
        $title = $post['title'];
        $body = $post['body'];
    }
}

if ($postId)
{
    if (!isLoggedIn() || !(isAdmin() || isOwner($postId)))
    {
        redirectAndExit('index.php');
    }
}
else
{
    #Create post
    if (!isLoggedIn())
    {
        redirectAndExit('index.php');
    }
}


// Handle the post operation here
$errors = array();
if ($_POST)
{
    // Validate these first
    $title = $_POST['post-title'];
    if (!$title)
    {
        $errors[] = 'The post must have a title';
    }
    $body = $_POST['post-body'];
    if (!$body)
    {
        $errors[] = 'The post must have a body';
    }
    
    // Handle image upload if a file was submitted
    if (isset($_FILES['post-image']) && $_FILES['post-image']['error'] === UPLOAD_ERR_OK)
    {
        
        $imageSource = $_FILES['post-image']['tmp_name'];
        $imageData = file_get_contents($_FILES['post-image']['tmp_name']);
        $imageError = saveImage($imageData);
        if ($imageError) {
            $errors[] = $imageError;
        }

    }
    else
    {
        $imageSource = null; // No image was uploaded
    }


    if (!$errors)
    {
        // Decide if we are editing or adding
        if ($postId)
        {
            if ($imageSource && !(isset($_POST['delete-image'])))
            {
                $result = editPost($pdo, $title, $body, $postId, $imageSource, true);
            }
            else if (isset($_POST['delete-image']))
            {
                
                $result = editPost($pdo, $title, $body, $postId, null, true);
            }
            else
            {
                $result = editPost($pdo, $title, $body, $postId);
            }

            if ($result === false)
            {
                $errors[] = 'Post operation failed';
            }
        }
        else
        {
            $userId = getAuthUserId($pdo);
            $postId = addPost($pdo, $title, $body, $userId, $imageSource);
            if ($postId === false)
            {
                $errors[] = 'Post operation failed';
            }
        }
    }
    
    if (!$errors)
    {
        redirectAndExit('view-post.php?post_id=' . $postId);
        exit;
    }
}

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Cyberia | New post</title>
        <?php require 'templates/head.php' ?>
    </head>
    <body>
        <?php require 'templates/top-menu.php' ?>
        <?php if(isset($_GET['post_id'])): ?>
            <h1>Edit post</h1>
        <?php else: ?>
            <h1>New post</h1>
        <?php endif ?>
        
        <?php if ($errors): ?>
            <div class="error box">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error ?></li>
                 <?php endforeach ?>
                </ul>
            </div>
        <?php endif ?>

        <form method="post" class="post-form user-form" enctype="multipart/form-data">
        <div>
            <label for="post-title">Title:</label>
            <input
                id="post-title"
                name="post-title"
                type="text"
                value="<?php echo htmlEscape($title) ?>"
            />
        </div>
        <div>
            <label for="post-body">Body:</label>
            <textarea
                id="post-body"
                name="post-body"
                rows="12"
                cols="70"
            ><?php echo htmlEscape($body) ?></textarea>
        </div>
        <div>
            <label for="post-image">Image:</label>
            <input
                id="post-image"
                name="post-image"
                type="file"
                accept="image/jpeg, image/png"
            />
            <input
                    type="checkbox"
                    name="delete-image"
                    id="delete-image"
                    
                />
                Delete Image
            
        </div>

        
        <div>
            <input
                type="submit"
                value="Save post"
            />
            <a href="index.php">Cancel</a>
        </div>
    </form>
    </body>
</html>



