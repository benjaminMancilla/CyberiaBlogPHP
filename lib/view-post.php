 <?php

/**
 * Retrieves a single post
 * 
 * @param PDO $pdo
 * @param integer $postId
 * @throws Exception
 */
function getPostRow(PDO $pdo, $postId)
{
    $stmt = $pdo->prepare(
        'SELECT
            p.title, 
            p.created_at, 
            p.body,
            p.image,
            p.thumbnail,
            u.username AS author,
            u.id AS user_id,
            (SELECT COUNT(*) FROM comment WHERE comment.post_id = p.id) comment_count
        FROM
            post p
        JOIN
            user u ON p.user_id = u.id
        WHERE
            p.id = :id'
    );
    if ($stmt === false)
    {
        throw new Exception('There was a problem preparing this query');
    }
    $result = $stmt->execute(
        array('id' => $postId)
    );
    if ($result === false)
    {
        throw new Exception('There was a problem running this query');    
    }

    // Let's get a row
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row;
}


/**
 * Writes a comment to a particular post
 * 
 * @param PDO $pdo
 * @param integer $postId
 * @param array $commentData
 * @param string $imageData
 * @return array
 */
function addCommentToPost(PDO $pdo, $postId, array $commentData, $imageSource = null)
{
    $errors = array();

    // Do some validation
    if (empty($commentData['user_name']))
    {
        $errors['user_name'] = 'A name is required';
    }
    if (empty($commentData['body']))
    {
        $errors['body'] = 'A comment is required';
    }

    // If we are error free, try writing the comment
    if (!$errors)
    {
        $sql = "
            INSERT INTO
                comment
            (user_name, website, body, created_at, post_id, image)
            VALUES(:user_name, :website, :body, :created_at, :post_id, :image)
        ";
        $stmt = $pdo->prepare($sql);
        if ($stmt === false)
        {
            throw new Exception('Cannot prepare statement to insert comment');
        }
        if ($imageSource) {
            $imageData = makeThumbnail($imageSource, 300);
        } else {
            $imageData = null;
        }

        $result = $stmt->execute(
            array_merge(
                $commentData,
                array(
                    'post_id' => $postId,
                    'created_at' => getSqlDateForNow(),
                    'image' => $imageData
                )
            )
        );

        if ($result === false)
        {
            // @todo This renders a database-level message to the user, fix this
            $errorInfo = $stmt->errorInfo();
            if ($errorInfo)
            {
                $errors[] = $errorInfo[2];
            }
        }
    }

    return $errors;
}


/**
 * Called to handle the comment form, redirects upon success
 *
 * @param PDO $pdo
 * @param integer $postId
 * @param array $commentData
 */
function handleAddComment(PDO $pdo, $postId, array $commentData, $imageSource = null)
{
    $errors = addCommentToPost(
        $pdo,
        $postId,
        $commentData,
        $imageSource
    );
    // If there are no errors, redirect back to self and redisplay
    if (!$errors)
    {
        redirectAndExit('view-post.php?post_id=' . $postId);
    }
    return $errors;
}


/**
 * Delete the specified comment on the specified post
 *
 * @param PDO $pdo
 * @param integer $postId
 * @param integer $commentId
 * @return boolean True if the command executed without errors
 * @throws Exception
 */
function deleteComment(PDO $pdo, $postId, $commentId)
{
    // The comment id on its own would suffice, but post_id is a nice extra safety check
    $sql = "
        DELETE FROM
            comment
        WHERE
            post_id = :post_id
            AND id = :comment_id
    ";
    $stmt = $pdo->prepare($sql);
    if ($stmt === false)
    {
        throw new Exception('There was a problem preparing this query');
    }
    $result = $stmt->execute(
        array(
            'post_id' => $postId,
            'comment_id' => $commentId,
        )
    );
    return $result !== false;
}


/**
 * Called to handle the delete comment form, redirects afterwards
 *
 * The $deleteResponse array is expected to be in the form:
 *
 *	Array ( [6] => Delete )
 *
 * which comes directly from input elements of this form:
 *
 *	name="delete-comment[6]"
 *
 * @param PDO $pdo
 * @param integer $postId
 * @param array $deleteResponse
 */
function handleDeleteComment(PDO $pdo, $postId, array $deleteResponse)
{
    if (isLoggedIn())
    {
        $keys = array_keys($deleteResponse);
        $deleteCommentId = $keys[0];

        if ($deleteCommentId)
        {
            // Obtener información del comentario
            $commentInfo = getCommentById($pdo, $deleteCommentId);
            if (!$commentInfo) {
                // Si no se encuentra el comentario, redirigir
                redirectAndExit('view-post.php?post_id=' . $postId);
            }

            // Obtener rol del usuario
            $currentUser = getAuthUser();
            $currentUserRole = getUserRole($currentUser);

            // Verificar si el usuario puede borrar el comentario
            $isAuthor = $currentUser === $commentInfo['user_name'];
            $isPostOwner = $currentUser === $commentInfo['post_owner'];
            $isAdmin = $currentUserRole === 'admin';

            if ($isAuthor || $isPostOwner || $isAdmin)
            {
                deleteComment($pdo, $postId, $deleteCommentId);
            }
        }
    }
    redirectAndExit('view-post.php?post_id=' . $postId);
}


function getCommentById(PDO $pdo, $commentId)
{
    $sql = "
        SELECT 
            c.user_name, 
            u.username as post_owner 
        FROM comment c
        JOIN post p ON c.post_id = p.id
        JOIN user u ON p.user_id = u.id
        WHERE c.id = :commentId
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['commentId' => $commentId]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

//Refractor to this (add editComment function)
function handleEditComment(PDO $pdo, $commentId, $editText, $imageSource = null)
{
    // Start building the SQL query
    $sql = "
        UPDATE
            comment
        SET
            body = :body";
    
    if ($imageSource) {
        $sql .= ",
            image = :image"; // Add image field if imageSource is provided
    }
    
    $sql .= "
        WHERE
            id = :comment_id";

    $stmt = $pdo->prepare($sql);
    if ($stmt === false) {
        throw new Exception('Could not prepare comment update query');
    }

    // Prepare parameters for the query
    $params = array(
        ':body' => $editText,
        ':comment_id' => $commentId,
    );

    if ($imageSource) {
        $imageData = makeThumbnail($imageSource, 300);
        $params[':image'] = $imageData; // Bind image data if available
    }

    // Execute the query with bound parameters
    $result = $stmt->execute($params);
    if ($result === false) {
        throw new Exception('Could not run comment update query');
    }

    return true;
}






