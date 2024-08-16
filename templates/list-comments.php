<?php
/**
 * @var $pdo PDO
 * @var $postId integer
 * @var $commentCount integer
 */
require_once 'lib/common.php';
require_once 'lib/edit-post.php';
?>

<form
    action="view-post.php?action=delete-comment&amp;post_id=<?php echo $postId?>"
    method="post"
    class="comment-list"
>
    <h3><?php echo $commentCount ?> comments</h3>

    <?php foreach (getCommentsForPost($pdo, $postId) as $comment): ?>
        <?php $commentUserID = 0;
        if ($comment['user_name']!= "anonymous") {
            $commentUserID = getUserID($pdo, $comment['user_name']);
        }
        ?>
        
        <div class="comment">
            <div class="comment-meta">
                Comment from
                <?php if ($commentUserID == 0): ?>
                    <?php echo htmlEscape($comment['user_name']) ?>
                <?php else: ?>
                    <a href="profile.php?profile_id=<?php echo $commentUserID ?>">
                        <?php echo htmlEscape($comment['user_name']) ?></a>
                <?php endif ?>
                on
                <?php echo convertSqlDate($comment['created_at']) ?>
                <?php if (isLoggedIn()): ?>
                    <input
                        type="submit"
                        name="delete-comment[<?php echo $comment['id'] ?>]"
                        value="Delete"
                    />
                <?php endif ?>
            </div>
            <div class="comment-body">
                <?php // This is already escaped ?>
                <?php echo convertNewlinesToParagraphs($comment['body']) ?>
            </div>
        </div>
    <?php endforeach ?>
</form>


