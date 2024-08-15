<div class="top-menu">
    <div class="menu-options">
        <a href="index.php">Home</a>
        |
        <?php if (isLoggedIn()): ?>
            <a href="list-users.php">All users</a>
            |
            <a href="edit-post.php">New post</a>
            |
            <a href="list-posts.php">All posts</a>
            |
            Hello <?php echo htmlEscape(getAuthUser()) ?>.
            <a href="logout.php">Log out</a>
        <?php else: ?>
            <a href="login.php">Log in</a>
        <?php endif ?>
    </div>
</div>