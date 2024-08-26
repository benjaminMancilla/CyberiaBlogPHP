<div class="sidebar-left">
    <div class="leftbar-main-options">
        <a href="index.php">Home</a>
        <?php if (isLoggedIn()): ?>
            <a href="edit-post.php">New post</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Log out</a>
        <?php else: ?>
            <a href="login.php">Log in</a>
            <a href="register.php">Register</a>
        <?php endif ?>
    </div>
</div>

