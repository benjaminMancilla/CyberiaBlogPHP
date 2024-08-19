
<?php
/**
 * Gets the root path of the project
 *
 * @return string
 */
function getRootPath()
{
    return realpath(__DIR__ . '/..');
}
/**
 * Gets the full path for the database file
 *
 * @return string
 */
function getDatabasePath()
{
    return getRootPath() . '/data/data.sqlite';
}
/**
 * Gets the DSN for the SQLite connection
 *
 * @return string
 */
function getDsn()
{
    return 'sqlite:' . getDatabasePath();
}
/**
 * Gets the PDO object for database access
 *
 * @return \PDO
 */
function getPDO()
{
    $pdo = new PDO(getDsn());
    $result = $pdo->query('PRAGMA foreign_keys = ON');
    if ($result === false)
    {
        throw new Exception('Could not turn on foreign key constraints');
    }
    return $pdo;
}
/**
 * Escapes HTML so it is safe to output
 *
 * @param string $html
 * @return string
 */
function htmlEscape($html)
{
    return htmlspecialchars($html, ENT_HTML5, 'UTF-8');
}

function convertSqlDate($sqlDate)
{
    /* @var $date DateTime */
    $date = DateTime::createFromFormat('Y-m-d H:i:s', $sqlDate);
    return $date->format('d M Y, H:i');
}

/**
 * Returns all the comments for the specified post
 *
 * @param PDO $pdo
 * @param integer $postId
 * @return array
 */
function getCommentsForPost($pdo, $postId)
{
    $sql = "
        SELECT
            body, id, user_name, created_at, website
        FROM
            comment
        WHERE
            post_id = :post_id
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(
        array('post_id' => $postId, )
    );
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function redirectAndExit($script)
{
    // Get the domain-relative URL (e.g. /blog/whatever.php or /whatever.php) and work
    // out the folder (e.g. /blog/ or /).
    $relativeUrl = $_SERVER['PHP_SELF'];
    $urlFolder = substr($relativeUrl, 0, strrpos($relativeUrl, '/') + 1);
    // Redirect to the full URL (http://myhost/blog/script.php)
    $host = $_SERVER['HTTP_HOST'];
    $fullUrl = 'http://' . $host . $urlFolder . $script;
    header('Location: ' . $fullUrl);
    exit();
}

/**
 * Converts unsafe text to safe, paragraphed, HTML
 *
 * @param string $text
 * @return string
 */
function convertNewlinesToParagraphs($text)
{
    $escaped = htmlEscape($text);
    return '<p>' . str_replace("\\n", "</p><p>", $escaped) . '</p>';
}


function getSqlDateForNow()
{
    return date('Y-m-d H:i:s');
}


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


function isLoggedIn()
{
    return isset($_SESSION['logged_in_username']);
}


/**
 * Logs the user out
 */
function logout()
{
    unset($_SESSION['logged_in_username']);
}
function getAuthUser()
{
    return isLoggedIn() ? $_SESSION['logged_in_username'] : null;
}


/**
 * Looks up the user_id for the current auth user
 */
function getAuthUserId(PDO $pdo)
{
    // Reply with null if there is no logged-in user
    if (!isLoggedIn())
    {
        return null;
    }
    $sql = "
        SELECT
            id
        FROM
            user
        WHERE
            username = :username
            AND is_enabled = 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(
        array(
            'username' => getAuthUser()
        )
    );
    return $stmt->fetchColumn();
}

/**
 * Gets a list of posts in reverse order
 *
 * @param PDO $pdo
 * @return array
 */
function getAllPosts(PDO $pdo)
{
    $stmt = $pdo->query(
        'SELECT
            id, title, created_at, body,
            (SELECT COUNT(*) FROM comment WHERE comment.post_id = post.id) comment_count
        FROM
            post
        ORDER BY
            created_at DESC'
    );
    if ($stmt === false)
    {
        throw new Exception('There was a problem running this query');
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
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


function deleteUser(PDO $pdo, $userId)
{
    $sql = "
        DELETE FROM
            user
        WHERE
            id = :id
    ";
    $stmt = $pdo->prepare($sql);
    if ($stmt === false)
    {
        throw new Exception('There was a problem preparing this query');
    }
    $result = $stmt->execute(array('id' => $userId));
    return $result !== false;
}

function getAllUsers(PDO $pdo)
{
    $stmt = $pdo->query(
        'SELECT
            id, username, created_at, role
        FROM
            user
        ORDER BY
            created_at DESC'
    );
    if ($stmt === false)
    {
        throw new Exception('There was a problem running this query');
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserRole($username)
{
    $pdo = getPDO();
    $sql = "SELECT role FROM user WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $username]);
    $role = $stmt->fetchColumn();

    return $role ?: 'user'; // Devuelve el rol, o 'user' por defecto si no se encuentra
}

function getAuthUserRole()
{
    return $_SESSION['role'] ?? 'user'; // Devuelve el rol almacenado en la sesión o 'user' por defecto
}

function isAdmin()
{
    return getAuthUserRole() === 'admin';
}

function isOwner($postId)
{
    $pdo = getPDO();
    $userId = getAuthUserId($pdo);
    if ($userId === null)
    {
        return false;
    }
    $sql = "
        SELECT
            COUNT(*)
        FROM
            post
        WHERE
            id = :post_id
            AND user_id = :user_id
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(
        array(
            'post_id' => $postId,
            'user_id' => $userId
        )
    );
    return $stmt->fetchColumn() > 0;
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

function getAuthProfile(PDO $pdo)
{
    $userId = getAuthUserId($pdo);
    if ($userId === null)
    {
        return null;
    }
    return getProfileById($pdo, $userId);
}

function getProfileById(PDO $pdo, $userID)
{
    $sql = "SELECT * FROM profile WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $userID]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateProfile(PDO $pdo, $userID, $visibleName, $aboutMe, $website, $avatar, $deleteAvatar)
{
    $updateAvatar = true;
    if ($deleteAvatar) {
        $imageData = null;
    } elseif ($avatar['tmp_name']) {
        $imageData = resizeAndCropImage($avatar['tmp_name'], 200, 200);
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


function resizeAndCropImage($sourcePath, $targetWidth, $targetHeight)
{
    list($width, $height, $imageType) = getimagesize($sourcePath);

    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($sourcePath);
            break;
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($sourcePath);
            break;
        default:
            throw new Exception('Unsupported image type.');
    }

    $originalAspect = $width / $height;
    $thumbAspect = $targetWidth / $targetHeight;

    if ($originalAspect >= $thumbAspect) {
        $newHeight = $targetHeight;
        $newWidth = $width / ($height / $targetHeight);
    } else {
        $newWidth = $targetWidth;
        $newHeight = $height / ($width / $targetWidth);
    }

    $thumb = imagecreatetruecolor($targetWidth, $targetHeight);

    // Si es PNG o GIF, mantener la transparencia
    if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
        $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127); // Fondo blanco transparente
        imagefilledrectangle($thumb, 0, 0, $targetWidth, $targetHeight, $transparent);
        imagecolortransparent($thumb, $transparent);
    }

    imagecopyresampled($thumb, $image,
        0 - ($newWidth - $targetWidth) / 2,
        0 - ($newHeight - $targetHeight) / 2,
        0, 0,
        $newWidth, $newHeight,
        $width, $height);

    ob_start();
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            imagejpeg($thumb, null, 80);
            break;
        case IMAGETYPE_PNG:
            imagepng($thumb);
            break;
        case IMAGETYPE_GIF:
            imagegif($thumb);
            break;
    }
    $imageData = ob_get_clean();
    imagedestroy($thumb);

    return $imageData;
}

function renderProfileImage($avatarData, $size, $altText = "Profile Image")
{
    // Define rutas y configuraciones
    $defaultAvatar = '/blog/assets/images/default-avatar.png'; // Ruta relativa desde la raíz del servidor
    $imageSrc = $defaultAvatar;
    $className = 'profile-image-' . $size;

    // Si hay datos de avatar, se genera la imagen
    if ($avatarData) {
        // Crear la URL de la imagen desde los datos binarios
        $imageSrc = 'data:image/jpeg;base64,' . base64_encode($avatarData);
    }

    // Generar la etiqueta HTML de la imagen
    return '<img src="' . htmlspecialchars($imageSrc) . '" class="' . htmlspecialchars($className) . '" alt="' . htmlspecialchars($altText) . '">';
}

function getUserID(PDO $pdo, $username)
{

    $sql = "
        SELECT
            id
        FROM
            user
        WHERE
            username = :username
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $username]);
    
    $userId = $stmt->fetchColumn();

    if ($userId === false) {
        throw new Exception('User not found: ' . $username);
    }

    return $userId;
}

function savePostImage(PDO $pdo, $postID, $imagePath, $minWidth = 200, $minHeight = 200, $maxWidth = 1920, $maxHeight = 1080)
{
    // Obtener información de la imagen
    list($width, $height, $imageType) = getimagesize($imagePath);

    // Verificar si la resolución cumple con los requisitos
    if ($width < $minWidth || $height < $minHeight || $width > $maxWidth || $height > $maxHeight) {
        throw new Exception('La imagen no cumple con los requisitos de resolución.');
    }

    // Crear la imagen en base al tipo
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($imagePath);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($imagePath);
            break;
        default:
            throw new Exception('Tipo de imagen no soportado.');
    }

    // Mantener la transparencia para PNG
    if ($imageType == IMAGETYPE_PNG) {
        imagealphablending($image, false);
        imagesavealpha($image, true);
    }

    // Convertir la imagen a datos binarios
    ob_start();
    switch ($imageType) {
        case IMAGETYPE_JPEG:
            imagejpeg($image, null, 80);
            break;
        case IMAGETYPE_PNG:
            imagepng($image);
            break;
    }
    $imageData = ob_get_clean();

    // Destruir el recurso de la imagen
    imagedestroy($image);

    // Guardar la imagen en la base de datos
    $sql = "
        UPDATE post
        SET
            image = :image
        WHERE
            id = :post_id
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'image' => $imageData,
        'post_id' => $postID
    ]);

    return $stmt->rowCount() > 0;
}

function renderPostThumbnail($imageData, $altText = "Post Image Thumbnail")
{
    // Define a default image path for when no image is provided
    $defaultImage = '/blog/assets/images/default-post-thumbnail.png';
    $imageSrc = $defaultImage;
    $className = 'post-thumbnail';

    // If image data exists, encode it to display the image
    if ($imageData) {
        $imageSrc = 'data:image/jpeg;base64,' . base64_encode($imageData);
    }

    // Return the HTML for the image
    return '<img src="' . htmlspecialchars($imageSrc) . '" class="' . htmlspecialchars($className) . '" alt="' . htmlspecialchars($altText) . '">';
}



function renderPostImageFull($imageData, $altText = "Full Resolution Post Image")
{
    // Define a default image path for when no image is provided
    $defaultImage = '/blog/assets/images/default-post-image.png';
    $imageSrc = $defaultImage;
    $className = 'post-image-full';

    // If image data exists, encode it to display the image
    if ($imageData) {
        $imageSrc = 'data:image/jpeg;base64,' . base64_encode($imageData);
    }

    // Return the HTML for the image
    return '<img src="' . htmlspecialchars($imageSrc) . '" class="' . htmlspecialchars($className) . '" alt="' . htmlspecialchars($altText) . '">';
}

function saveImage($imageData)
{
    
    // Define minimum and maximum resolution
    $minWidth = 20;
    $minHeight = 20;
    $maxWidth = 2000;
    $maxHeight = 2000;

    // Create an image resource from the binary data
    $image = imagecreatefromstring($imageData);
    if (!$image) {
        return 'Invalid image format. Please upload a JPEG or PNG image.';
    }

    // Get the dimensions of the image
    $width = imagesx($image);
    $height = imagesy($image);

    // Check if the image meets the resolution requirements
    if ($width < $minWidth || $height < $minHeight) {
        return "Image is too small. Minimum resolution is {$minWidth}x{$minHeight} pixels.";
    }
    if ($width > $maxWidth || $height > $maxHeight) {
        return "Image is too large. Maximum resolution is {$maxWidth}x{$maxHeight} pixels.";
    }

    // The image meets the requirements, so return the binary data
    return false;
}




















