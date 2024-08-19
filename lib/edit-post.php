
<?php
function addPost(PDO $pdo, $title, $body, $userId, $imageSource = null)
{
    $imageData = null;
    $thumbnailData = null;
    if ($imageSource) {
        $imageData = file_get_contents($imageSource);
        $thumbnailData = makeThumbnail($imageData);
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

    if ($updateImage) {
        $imageData = file_get_contents($imageSource);
        $thumbnailData = makeThumbnail($imageData);
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
    // Crear imagen a partir de los datos binarios
    $image = imagecreatefromstring($imageSource);
    if (!$image) {
        throw new Exception('No se pudo crear la imagen desde los datos binarios.');
    }

    // Obtener dimensiones originales
    $width = imagesx($image);
    $height = imagesy($image);

    // Calcular la nueva dimensión manteniendo la relación de aspecto
    if ($width > $height) {
        $newWidth = $maxSize;
        $newHeight = ($height / $width) * $maxSize;
    } else {
        $newHeight = $maxSize;
        $newWidth = ($width / $height) * $maxSize;
    }

    // Crear la imagen de la miniatura
    $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
    $bgColor = imagecolorallocate($thumbnail, 255, 255, 255); // Fondo blanco
    imagefill($thumbnail, 0, 0, $bgColor);

    // Redimensionar la imagen
    imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // Guardar la miniatura en un blob
    ob_start();
    imagejpeg($thumbnail);
    $thumbnailData = ob_get_clean();

    // Liberar memoria
    imagedestroy($image);
    imagedestroy($thumbnail);

    return $thumbnailData;
}

