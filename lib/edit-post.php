
<?php
function addPost(PDO $pdo, $title, $body, $userId, $imageSource = null)
{
    $imageData = null;
    $thumbnailData = null;
    if ($imageSource) {
        $imageData = file_get_contents($imageSource);
        $thumbnailData = makeThumbnail($imageSource);
    }


    $sql = "
        INSERT INTO post (title, body, image, thumbnail, user_id, created_at)
        VALUES (:title, :body, :image, :thumbnail,  :user_id, :created_at)
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
        ':title' => $title,
        ':body' => $body,
        ':image' => $imageData,
        ':thumbnail' => $thumbnailData,
        ':user_id' => $userId,
        ':created_at' => getSqlDateForNow(),
    ));
    return $pdo->lastInsertId();
}


function editPost(PDO $pdo, $title, $body, $postId, $imageSource = null, $updateImage = false)
{
    if ($updateImage && !$imageSource) {
        deleteImage($pdo, $postId);
        $sql = '
            UPDATE
                post
            SET
                title = :title,
                body = :body
            WHERE
                id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(
            ':title' => $title,
            ':body' => $body,
            ':id' => $postId,
        ));
        return $stmt->rowCount() === 1;
    }
    if ($updateImage) {
        $imageData = file_get_contents($imageSource);
        $thumbnailData = makeThumbnail($imageSource);
    }

    $sql = "
        UPDATE
            post
        SET
            title = :title,
            body = :body
    ";
    if ($updateImage) {
        $sql .= ",
            image = :image,
            thumbnail = :thumbnail
        ";
    }
    $sql .= '
        WHERE
            id = :id';
    $stmt = $pdo->prepare($sql);
    $params = array(
        ':title' => $title,
        ':body' => $body,
        ':id' => $postId,
    );
    if ($updateImage) {
        $params[':image'] = $imageData;
        $params[':thumbnail'] = $thumbnailData;
    }
    $stmt->execute($params);
    return $stmt->rowCount() === 1;
}

function deletePost(PDO $pdo, $postId)
{
    try {
        // Start a transaction
        $pdo->beginTransaction();

        // Delete comments associated with the post
        $sql = "
            DELETE FROM
                comment
            WHERE
                post_id = :post_id
        ";
        $stmt = $pdo->prepare($sql);
        if ($stmt === false) {
            throw new Exception('Could not prepare comment delete query');
        }
        $result = $stmt->execute(
            array('post_id' => $postId)
        );
        if ($result === false) {
            throw new Exception('Could not run comment delete query');
        }

        // Delete the post
        $sql = "
            DELETE FROM
                post
            WHERE
                id = :post_id
        ";
        $stmt = $pdo->prepare($sql);
        if ($stmt === false) {
            throw new Exception('Could not prepare post delete query');
        }
        $result = $stmt->execute(
            array('post_id' => $postId)
        );
        if ($result === false) {
            throw new Exception('Could not run post delete query');
        }

        // Commit the transaction
        $pdo->commit();

        return true;
    } catch (Exception $e) {
        // Rollback the transaction if something failed
        $pdo->rollBack();
        throw $e;
    }
}

function makeThumbnail($imageSource, $maxSize = 600) {
    $image = loadImage($imageSource, $imageType);
    $width = imagesx($image);
    $height = imagesy($image);

    $thumbnail = resizeAndCropImageResource($image, $width > $height ? $maxSize : ($width / $height) * $maxSize, $width > $height ? ($height / $width) * $maxSize : $maxSize, $imageType);
    
    ob_start();
    if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
        imagepng($thumbnail);
    } else {
        imagejpeg($thumbnail);
    }
    $thumbnailData = ob_get_clean();

    imagedestroy($image);
    imagedestroy($thumbnail);

    return $thumbnailData;
}



function deleteImage(PDO $pdo, $postId)
{

    $sql = "
        UPDATE
            post
        SET
            image = NULL,
            thumbnail = NULL
        WHERE
            id = :id
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':id' => $postId));
}

