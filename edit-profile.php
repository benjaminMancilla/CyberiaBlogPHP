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

// Check if the profile exists and if the logged-in user is the owner
if (!$userProfile || !($profileID === $loggedInUserID || isAdmin())) {
    echo "<p>Profile not found or you don't have permission to edit this profile.</p>";
    exit;
}

if ($_POST) {
    // Handle form submission
    $visibleName = $_POST['visibleName'];
    $aboutMe = $_POST['aboutMe'];
    $website = $_POST['website'];
    $avatar = $_FILES['avatar'];

    $result = updateProfile($pdo, $profileID, $visibleName, $aboutMe, $website, $avatar);
    if ($result) {
        echo "<p>Profile updated successfully.</p>";
        // Redirect to the profile view page of the updated profile
        header('Location: profile.php?profile_id=' . $profileID);
        exit;
    } else {
        echo "<p>There was an error updating the profile.</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
    <?php require 'templates/head.php'; ?>
</head>
<body>
    <?php require 'templates/top-menu.php'; ?>
    <h1>Edit Profile</h1>

    <form action="edit-profile.php?profile_id=<?php echo $profileID; ?>" method="post" enctype="multipart/form-data">
        <label for="visibleName">Visible Name:</label>
        <input type="text" id="visibleName" name="visibleName" value="<?php echo htmlEscape($userProfile['visibleName']); ?>" required>
        <br>
        <label for="aboutMe">About Me:</label>
        <textarea id="aboutMe" name="aboutMe"><?php echo htmlEscape($userProfile['aboutMe']); ?></textarea>
        <br>
        <label for="website">Website:</label>
        <input type="url" id="website" name="website" value="<?php echo htmlEscape($userProfile['website']); ?>">
        <br>
        <label for="avatar">Avatar:</label>
        <input type="file" id="avatar" name="avatar">
        <br>
        <button type="submit">Update Profile</button>
    </form>
</body>
</html>

