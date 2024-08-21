<?php

function updateProfile(PDO $pdo, $userID, $visibleName, $aboutMe, $website, $avatar, $deleteAvatar)
{
    $updateAvatar = true;
    if ($deleteAvatar) {
        $imageData = null;
    } elseif ($avatar) {
        $imageData = resizeAndCropImage($avatar, 200, 200);
    } else {
        $updateAvatar = false;  
    }

    $sql = "
        UPDATE profile
        SET
            visibleName = :visibleName,
            aboutMe = :aboutMe,
            website = :website
    ";

    if ($updateAvatar) {
        $sql .= ", avatar = :avatar";
    }

    $sql .= " WHERE user_id = :user_id";

    $stmt = $pdo->prepare($sql);
    if ($stmt === false) {
        throw new Exception('There was a problem preparing this query');
    }

    // Preparamos los parámetros
    $params = [
        'visibleName' => $visibleName,
        'aboutMe' => $aboutMe,
        'website' => $website,
        'user_id' => $userID
    ];

    if ($updateAvatar) {
        $params['avatar'] = $imageData;
    }

    $stmt->execute($params);

    return $stmt->rowCount() > 0;
}