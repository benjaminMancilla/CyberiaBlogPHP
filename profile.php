<?php
require_once 'lib/common.php';

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
    <h1>Profile of <?php echo htmlEscape($userProfile['visibleName']); ?></h1>

    <div class="profile">
        <p><strong>Username:</strong> <?php echo htmlEscape($userProfile['username']); ?></p>
        <p><strong>About Me:</strong> <?php echo nl2br(htmlEscape($userProfile['aboutMe'])); ?></p>
        <p><strong>Website:</strong> <a href="<?php echo htmlEscape($userProfile['website']); ?>"><?php echo htmlEscape($userProfile['website']); ?></a></p>
        <p><strong>Equipped Badge:</strong> <?php echo htmlEscape($userProfile['equippedBadge']); ?></p>
        <p><strong>Badges:</strong> <?php echo htmlEscape($userProfile['badges']); ?></p>
        <p><strong>Avatar:</strong></p>
        <?php if ($userProfile['avatar']): ?>
            <img src="data:image/jpeg;base64,<?php echo base64_encode($userProfile['avatar']); ?>" alt="User Avatar" />
        <?php else: ?>
            <p>No avatar uploaded.</p>
        <?php endif; ?>
    </div>

    <?php if ($isOwner): ?>
        <a href="edit-profile.php">Edit Profile</a>
    <?php endif; ?>

</body>
</html>





