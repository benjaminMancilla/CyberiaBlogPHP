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

<div class="pill-list">
    <?php foreach (getCommentsForPost($pdo, $postId) as $comment): ?>
        <?php 
        $commentUserID = 0;
        if ($comment['user_name'] != "anonymous") {
            $commentUserID = getUserID($pdo, $comment['user_name']);
        }
        ?>

        <div class="pill">
            <div class="subpill">
                <!-- Sección de auth-info -->
                <div class="auth-info">
                    <!-- Ícono del usuario -->
                    <?php $authAvatar = getProfileAvatar($pdo, $commentUserID); ?>
                    <a href="profile.php?profile_id=<?php echo $commentUserID ?>" class="post-author-avatar">
                        <?php echo renderProfileImage($authAvatar, 'small'); ?>
                    </a>
                    <div class="comment-meta">
                        <span class="comment-user">
                            <?php if ($commentUserID == 0): ?>
                                <?php echo htmlEscape($comment['user_name']) ?>
                            <?php else: ?>
                                <a href="profile.php?profile_id=<?php echo $commentUserID ?>">
                                    <?php echo htmlEscape($comment['user_name']) ?>
                                </a>
                            <?php endif ?>
                        </span>
                        
                        <span class="comment-date">
                            <?php echo convertSqlDate($comment['created_at']) ?>
                        </span>
                    </div>
                </div>

                <!-- Sección de pill-text -->
                <div class="pill-text">
                    <!-- Cuerpo del comentario -->
                    <div class="comment-body">
                        <?php echo convertNewlinesToParagraphs($comment['body']) ?>
                    </div>

                    <!-- Si hay una imagen -->
                    <?php if ($comment['image']): ?>
                        <div class="comment-image">
                            <?php echo renderPostThumbnail($comment['image'], "Thumbnail for "); ?>
                        </div>
                    <?php endif ?>
                </div>

                <!-- Sección de pill-options -->
                <div class="pill-options">
                    <!-- Botón de eliminar comentario -->
                    <?php if (isLoggedIn() && ($comment['user_name'] == getAuthUser() || getAuthUser() == 'admin')): ?>
                        <form
                            action="view-post.php?action=delete-comment&amp;post_id=<?php echo $postId?>"
                            method="post"
                            class="comment-delete"
                        >
                            <input
                                type="submit"
                                name="delete-comment[<?php echo $comment['id'] ?>]"
                                value="Delete"
                                class="pill-option-btn"
                            />
                        </form>

                        <button
                            type="button"
                            class="pill-option-btn"
                            onclick="toggleEditForm(<?php echo $comment['id'] ?>)"
                        >
                            Edit
                        </button>
                    <?php endif ?>

                    <!-- Formulario de edición -->
                    <?php if ($comment['user_name'] == getAuthUser() || getAuthUser() == 'admin'): ?>
                        <?php include 'edit-comment-form.php'; ?>
                    <?php endif ?>
                </div>
            </div>
        </div>
    <?php endforeach ?>
</div>

<script>
function toggleEditForm(commentId) {
    var form = document.getElementById('edit-form-' + commentId);
    if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'block';
    } else {
        form.style.display = 'none';
    }
}
</script>
