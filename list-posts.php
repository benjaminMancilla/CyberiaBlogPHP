<?php
require_once 'lib/common.php';
require_once 'lib/edit-post.php';
session_start();

// Don't let non-auth users see this screen
if (!isLoggedIn() || !isAdmin())
{
    redirectAndExit('index.php');
}

if ($_POST)
{
    if (isset($_POST['delete-post']))
    {
        $deleteResponse = $_POST['delete-post'];
        $deletePostId = key($deleteResponse);  // Get the first key from the array

        if ($deletePostId!=null)
        {
            try {
                echo "<p>Deleting post with ID $deletePostId</p>";
                $deleted = deletePost(getPDO(), $deletePostId);
                echo "<p>Deleting post with ID $deletePostId</p>";
                if ($deleted)
                {
                    redirectAndExit('list-posts.php');
                }
                else
                {
                    echo "<p>Error: Could not delete the post.</p>";
                }
            } catch (Exception $e) {
                echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    }
}

// Connect to the database, run a query
$pdo = getPDO();
$posts = getAllPosts($pdo);

?>
<!DOCTYPE html>
<html>
    <head>
        <title>Cyberia | Blog posts</title>
        <?php require 'templates/head.php' ?>
    </head>
    <body>
        <?php require 'templates/top-menu.php' ?>
        <?php require 'templates/sidebar-left.php' ?>
        <div class="main-container">
            <div class="content-container">
                <h1>Post list</h1>
                <p>You have <?php echo count($posts) ?> posts.</p>
                <form method="post">
                    <table id="post-list">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Creation date</th>
                                <th>Comments</th>
                                <th />
                                <th />
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td>
                                        <a
                                            href="view-post.php?post_id=<?php echo $post['id']?>"
                                        ><?php echo htmlEscape($post['title']) ?></a>
                                    </td>
                                    <td>
                                        <?php echo convertSqlDate($post['created_at']) ?>
                                    </td>
                                    <td>
                                        <?php echo $post['comment_count'] ?>
                                    </td>
                                    <td>
                                        <a href="edit-post.php?post_id=<?php echo $post['id']?>">Edit</a>
                                    </td>
                                    <td>
                                        <input
                                            type="submit"
                                            name="delete-post[<?php echo $post['id']?>]"
                                            value="Delete"
                                        />
                                    </td>

                                </tr>
                            <?php endforeach ?>
                            
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </body>
</html>

