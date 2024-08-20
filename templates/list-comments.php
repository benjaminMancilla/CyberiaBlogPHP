<?php
/**
 * @var $pdo PDO
 * @var $postId integer
 * @var $commentCount integer
 */
require_once 'lib/common.php';
require_once 'lib/edit-post.php';
?>


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
                <?php if (isLoggedIn() && ($comment['user_name'] == getAuthUser() ||
            getAuthUser() == 'admin')): ?>
                    <form
                        action="view-post.php?action=delete-comment&amp;post_id=<?php echo $postId?>"
                        method="post"
                        class="comment-list"
                    >
                        <input
                            type="submit"
                            name="delete-comment[<?php echo $comment['id'] ?>]"
                            value="Delete"
                        />
                    </form>
                <?php endif ?>
            </div>
            <div class="comment-image">
                <?php if ($comment['image']): ?>
        
                    
                <?php endif ?>
            <div class="comment-body">
                <?php echo convertNewlinesToParagraphs($comment['body']) ?>
            </div>
            <div class = "comment-image">
                <?php if ($comment['image']): ?>
                    HOLAA
                    <?php echo renderPostThumbnail($comment['image'], "Thumbnail for "); ?>
                <?php endif ?>
            <?php if ($comment['user_name'] == getAuthUser() ||
            getAuthUser() == 'admin'): ?>

                <form method="post" action="view-post.php?action=edit-comment&amp;post_id=<?php echo $postId ?>" enctype="multipart/form-data">
                    <div class="comment-controls">
                        <?php // Botón de editar que despliega el bloque de texto ?>
                        <input
                            type="submit"
                            formaction="view-post.php?action=edit-comment&amp;post_id=<?php echo $postId ?>"
                            name="edit-comment[<?php echo $comment['id'] ?>]"
                            value="Save"
                        />
                        <input
                            type="text"
                            name="edit-comment-text[<?php echo $comment['id'] ?>]"
                            value="<?php echo htmlEscape($comment['body']) ?>"
                        />
                        <input
                            type="file"
                            name="edit-comment-image[<?php echo $comment['id'] ?>]"
                            accept="image/jpeg, image/png, image/gif"
                        />

                    </div>
                </form>

            <?php endif ?>
        </div>
    <?php endforeach ?>
</form>


