<div class="comment-controls">
    <div id="edit-form-<?php echo $comment['id'] ?>" class="edit-form" style="display:none;">
        <form method="post" action="view-post.php?action=edit-comment&amp;post_id=<?php echo $postId ?>" enctype="multipart/form-data">
            <textarea name="edit-comment-text[<?php echo $comment['id'] ?>]"><?php echo htmlEscape($comment['body']) ?></textarea>
            
            <input
                type="file"
                name="edit-comment-image-<?php echo $comment['id'] ?>"
                accept="image/jpeg, image/png, image/gif"
            />
            
            <input
                type="submit"
                name="edit-comment[<?php echo $comment['id'] ?>]"
                value="Save"
            />
        </form>
    </div>
</div>