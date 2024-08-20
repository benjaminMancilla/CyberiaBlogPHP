<?php
function tryLogin(PDO $pdo, $username, $password)
{
    $sql = "
        SELECT
            password
        FROM
            user
        WHERE
            username = :username
            AND is_enabled = 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(
        array('username' => $username, )
    );
    // Get the hash from this row, and use the third-party hashing library to check it
    $hash = $stmt->fetchColumn();
    $success = password_verify($password, $hash);
    if ($username === 'anonymous')
    {
        $success = false;
    }
    return $success;
}

/**
 * Logs the user in
 *
 * For safety, we ask PHP to regenerate the cookie, so if a user logs onto a site that a cracker
 * has prepared for him/her (e.g. on a public computer) the cracker's copy of the cookie ID will be
 * useless.
 *
 * @param string $username
 */
function login($username)
{
    session_regenerate_id();
    $_SESSION['logged_in_username'] = $username;
    
    // Usar la función auxiliar para obtener el rol
    $_SESSION['role'] = getUserRole($username);
}

/**
 * Logs the user out
 */
function logout()
{
    unset($_SESSION['logged_in_username']);
}

/**
 * Gets a single post by ID
 *
 * @param PDO $pdo
 * @param integer $postId
 * @return bool
 */
function trySignup(PDO $pdo, $username, $password)
{
    $sql = "
        SELECT COUNT(*) 
        FROM user 
        WHERE username = :username
    ";
    $stmt = $pdo->prepare($sql);
    if ($stmt === false)
    {
        throw new Exception('There was a problem preparing this query');
    }
    $stmt->execute(['username' => $username]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        return false; 
    }

    $sql = "
        INSERT INTO user
        (username, password, created_at, is_enabled)
        VALUES
        (:username, :password, :created_at, :is_enabled)
    ";
    $stmt = $pdo->prepare($sql);
    if ($stmt === false)
    {
        throw new Exception('There was a problem preparing this query');
    }
    
    try {
        $pdo->beginTransaction();
        
        $stmt->execute([
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s'),
            'is_enabled' => true
        ]);
        
        // Obtener el ID del usuario recién insertado
        $userId = $pdo->lastInsertId();
        
        // Crear el perfil del usuario
        if (!createProfile($pdo, $userId, $username)) {
            $pdo->rollBack();
            return false;
        }

        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        // Muestra el error para depuración
        echo "Error during sign-up: " . $e->getMessage();
        return false;
    }
}

function createProfile(PDO $pdo, $userId, $username)
{
    $sql = "
        INSERT INTO profile
        (user_id, username, visibleName)
        VALUES
        (:user_id, :username, :visibleName)
    ";
    
    $stmt = $pdo->prepare($sql);
    if ($stmt === false)
    {
        throw new Exception('There was a problem preparing this query');
    }
    
    try {
        $stmt->execute([
            'user_id' => $userId,
            'username' => $username,
            'visibleName' => $username // Por defecto, el nombre visible es el mismo que el username
        ]);
    } catch (PDOException $e) {
        // Mostrar el error para depuración
        echo "Error creating profile: " . $e->getMessage();
        return false;
    }
    
    return true;
}