<?php
require_once 'lib/common.php';
require_once 'lib/profile.php';

session_start();
if (!isLoggedIn()) {
    redirectAndExit('login.php');
}

$pdo = getPDO();
$loggedInUserID = getAuthUserId($pdo);

// Get the profile_id from the URL or default to the logged-in user
$profileID = isset($_GET['profile_id']) ? (int) $_GET['profile_id'] : $loggedInUserID;

// Fetch the profile data
$userProfile = getProfileById($pdo, $profileID);

// Check if the profile exists
if (!$userProfile) {
    echo "<p>Profile not found. Please complete your profile information.</p>";
    exit;
}

// Check if the logged-in user is the owner of the profile being viewed
$isOwner = ($profileID === $loggedInUserID) || isAdmin();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profile of <?php echo htmlEscape($userProfile['visibleName']); ?></title>
    <?php require 'templates/head.php'; ?>
</head>
<body>
    <?php require 'templates/top-menu.php'; ?>
    <?php require 'templates/sidebar-left.php'; ?>
    <div class = "main-container">
        <div class="content-container">
            <div class="profile">
                
                
                <h1>Profile of <?php echo htmlEscape($userProfile['visibleName']); ?></h1>

                <p><strong>Username:</strong> <?php echo htmlEscape($userProfile['username']); ?></p>
                <p><strong>About Me:</strong> <?php echo nl2br(htmlEscape($userProfile['aboutMe'])); ?></p>
                <p><strong>Website:</strong> <a href="<?php echo htmlEscape($userProfile['website']); ?>"><?php echo htmlEscape($userProfile['website']); ?></a></p>
                <p><strong>Equipped Badge:</strong> <?php echo htmlEscape($userProfile['equippedBadge']); ?></p>
                <p><strong>Badges:</strong> <?php echo htmlEscape($userProfile['badges']); ?></p>
                <p><strong>Avatar:</strong></p>
                <?php echo renderProfileImage($userProfile['avatar'], 'large'); ?>
                <br>
                <?php if ($isOwner): ?>
                    <a href="edit-profile.php">Edit Profile</a>
                <?php endif; ?>

            </div>

        </div>
    </div>
</body>
</html>





