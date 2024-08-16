<?php require_once 'lib/common.php'; ?>
<div class="top-menu">
    <div class="menu-options">
        <a href="index.php">Home</a>
        |
        <?php if (isLoggedIn()): ?>
            <a href="profile.php">Profile</a>
            |
            <?php if (getAuthUserRole() === 'admin'): ?>  <!-- Verifica si el usuario es admin -->
                <a href="list-users.php">All users</a>
                |
                <a href="list-posts.php">All posts</a>
                |
            <?php endif; ?>
            <a href="edit-post.php">New post</a>
            |
            Hello <?php echo htmlEscape(getAuthUser()) ?>.
            <a href="logout.php">Log out</a>
        <?php else: ?>
            <a href="login.php">Log in</a>
        <?php endif ?>
    </div>
</div>

