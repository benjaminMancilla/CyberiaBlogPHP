<?php
require_once 'lib/common.php';
require_once 'lib/edit-post.php';
require_once 'lib/view-post.php';

session_start();


// Empty defaults
$title = $body = '';
// Init database and get handle
$pdo = getPDO();
$profile = getAuthProfile($pdo);

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
        $errors = handleImageUpload($imageSource);
        if (count($errors) > 0)
        {
            $imageSource = null;
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
            //Update image
            if ($imageSource && !(isset($_POST['delete-image'])))
            {
                $result = editPost($pdo, $title, $body, $postId, $imageSource, true);
            }
            //Delete image
            else if (isset($_POST['delete-image']))
            {
                
                $result = editPost($pdo, $title, $body, $postId, null, true);
            }
            //No image change
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
<link rel="stylesheet" type="text/css" href="assets/post.css" />
<html>
    <head>
        <title>Cyberia | New post</title>
        <?php require 'templates/head.php' ?>
        <script>
            function toggleForm(type) {
                document.getElementById('text-form').style.display = type === 'text' ? 'block' : 'none';
                document.getElementById('media-form').style.display = type === 'media' ? 'block' : 'none';
            }
        </script>
    </head>
    <body>
        <?php require 'templates/top-menu.php' ?>
        <?php require 'templates/sidebar-left.php' ?>
        <?php require 'templates/bg-logo.php' ?>
        <div class="main-container">
            <div class="content-container">
                <div class="principal-column">
                    <?php if(isset($_GET['post_id'])): ?>
                        <h1>Edit post</h1>
                    <?php else: ?>
                        <h1>Create Post</h1>
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

                    <div class="toggle-type-post">
                        <button onclick="toggleForm('text')" >Text</button>
                        <button onclick="toggleForm('media')" >Media</button>
                    </div>

                    <form id="text-form" method="post" class="post-form" enctype="multipart/form-data" style="display: block">
                        <div id="edit-post-title">
                            <input
                                id="post-title"
                                name="post-title"
                                type="text"
                                value="<?php echo htmlEscape($title) ?>"
                                placeholder="Title"
                            />
                        </div>

                        <div id="edit-post-body">
                            <textarea
                                id="post-body"
                                name="post-body"
                                rows="12"
                                cols="70"
                                placeholder="Body"
                            ><?php echo htmlEscape($body) ?></textarea>
                        </div>

                        <div>
                            <a href="index.php">Cancel</a>
                            <input type="submit" value="Save post" />
                        </div>
                    </form>

                    <form id="media-form" method="post" class="post-form" enctype="multipart/form-data" style="display: none;">
                        <div id="edit-post-title">
                            <input
                                id="post-title"
                                name="post-title"
                                type="text"
                                value="<?php echo htmlEscape($title) ?>"
                                placeholder="Title"
                            />
                        </div>

                        <div class="image-upload">
                            <input
                                id="post-image"
                                name="post-image"
                                type="file"
                                accept="image/jpeg, image/png"
                            />
                            <?php if ($postId): ?>
                                <input
                                    type="checkbox"
                                    name="delete-image"
                                    id="delete-image"
                                />
                                Delete Image
                            <?php endif ?>
                        </div>

                        <div>
                            <a href="index.php">Cancel</a>
                            <input type="submit" value="Save post" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
    </body>
</html>




