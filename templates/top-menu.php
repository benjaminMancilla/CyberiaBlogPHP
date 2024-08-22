<div class="top-menu">
    <div class="left-menu">
        <div class=menu-logo>
            <a href="index.php" class="no-class">
                <img src="assets/images/logo.png" alt="Background Image">
            </a>
        </div>
        <div class="menu-gif">
            <img src="assets/gifs/top-lain.gif" alt="GIF">
        </div>
    </div>
    <div class="menu-options">
        <a href="index.php">Home</a>
        |
        <?php if (isLoggedIn()): ?>
            <?php if (getAuthUserRole() === 'admin'): ?>  <!-- Verifica si el usuario es admin -->
                <a href="list-users.php">All users</a>
                |
                <a href="list-posts.php">All posts</a>
                |
            <?php endif; ?>
            <a href="edit-post.php">New post</a>
            |
            Hello <?php echo htmlEscape(getAuthUser()) ?>.
            <a href="profile.php">
                <?php echo renderProfileImage($profile['avatar'], 'small'); ?>
            </a>
            <a href="logout.php">Log out</a>
        <?php else: ?>
            <a href="login.php">Log in</a>
        <?php endif ?>
    </div>
</div>



