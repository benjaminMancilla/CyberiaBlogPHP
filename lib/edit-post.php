
<?php
function addPost(PDO $pdo, $title, $body, $userId)
{
    // Prepare the insert query
    $sql = "
        INSERT INTO
            post
            (title, body, user_id, created_at)
            VALUES
            (:title, :body, :user_id, :created_at)
    ";
    $stmt = $pdo->prepare($sql);
    if ($stmt === false)
    {
        throw new Exception('Could not prepare post insert query');
    }
    // Now run the query, with these parameters
    $result = $stmt->execute(
        array(
            'title' => $title,
            'body' => $body,
            'user_id' => $userId,
            'created_at' => getSqlDateForNow(),
        )
    );
    if ($result === false)
    {
        throw new Exception('Could not run post insert query');
    }
    return $pdo->lastInsertId();
}


function editPost(PDO $pdo, $title, $body, $postId)
{
    // Prepare the insert query
    $sql = "
        UPDATE
            post
        SET
            title = :title,
            body = :body
        WHERE
            id = :post_id
    ";
    $stmt = $pdo->prepare($sql);
    if ($stmt === false)
    {
        throw new Exception('Could not prepare post update query');
    }
    // Now run the query, with these parameters
    $result = $stmt->execute(
        array(
            'title' => $title,
            'body' => $body,
            'post_id' => $postId,
        )
    );
    if ($result === false)
    {
        throw new Exception('Could not run post update query');
    }
    return true;
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