
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
            body, id, user_name, created_at, website, image
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



function isLoggedIn()
{
    return isset($_SESSION['logged_in_username']);
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


function resizeAndCropImage($sourcePath, $targetWidth, $targetHeight) {
    $image = loadImage($sourcePath, $imageType);

    $thumb = resizeAndCropImageResource($image, $targetWidth, $targetHeight, $imageType);

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


function loadImage($source, &$imageType = null) {
    if (is_string($source)) {
        list($width, $height, $imageType) = getimagesize($source);
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($source);
            case IMAGETYPE_PNG:
                return imagecreatefrompng($source);
            case IMAGETYPE_GIF:
                return imagecreatefromgif($source);
            default:
                throw new Exception('Unsupported image type.');
        }
    } else {
        $image = imagecreatefromstring($source);
        if (!$image) {
            throw new Exception('No se pudo crear la imagen desde los datos binarios.');
        }
        return $image;
    }
}

function checkImageSize($imageSource, $maxSize = 1048576, $maxWidth = 2000, $maxHeight = 2000) {
    $imageSize = getimagesize($imageSource);
    $image_width = $imageSize[0];
    $image_height = $imageSize[1];
    $fileSize = filesize($imageSource);
    if ($fileSize > $maxSize) {
        return "Image is too large. Maximum size is " . $maxSize / 1024 . "KB.";
    }
    if ( $image_width > $maxWidth || $image_height > $maxHeight) {
        return "Image is too large. Maximum resolution is {$maxWidth}x{$maxHeight} pixels.";
    }
    return null;
    
}

function checkImageResolution($image, $minWidth = 20, $minHeight = 20, $maxWidth = 2000, $maxHeight = 2000) {
    $width = imagesx($image);
    $height = imagesy($image);

    if ($width < $minWidth || $height < $minHeight) {
        return "Image is too small. Minimum resolution is {$minWidth}x{$minHeight} pixels.";
    }

    if ($width > $maxWidth || $height > $maxHeight) {
        return "Image is too large. Maximum resolution is {$maxWidth}x{$maxHeight} pixels.";
    }

    // Si la resolución es válida, retornar null o un valor que indique éxito.
    return null;
}


function resizeAndCropImageResource($image, $targetWidth, $targetHeight, $imageType) {
    $width = imagesx($image);
    $height = imagesy($image);
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

    // Si la imagen es PNG o GIF, habilitar la transparencia
    if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
        $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127); // Transparencia
        imagefilledrectangle($thumb, 0, 0, $targetWidth, $targetHeight, $transparent);
    }

    imagecopyresampled($thumb, $image,
        0 - ($newWidth - $targetWidth) / 2,
        0 - ($newHeight - $targetHeight) / 2,
        0, 0,
        $newWidth, $newHeight,
        $width, $height);

    return $thumb;
}

function handleImageUpload($imageSource, $maxSize = 1048576, $minWidth = 100, $minHeight = 100 ,$maxWidth = 2000, $maxHeight = 2000) {
    
        $errors = [];
        $Image = loadImage($imageSource);
        if ($Image === null)
        {
            $errors[] = 'Please upload a valid image file';
        }
        if (checkImageResolution($Image, $minWidth, $minHeight, $maxWidth, $maxHeight) === false)
        {
            $errors[] = 'Image must be at least 100x100 pixels';
        }
        if (checkImageSize($imageSource, $maxSize, $maxWidth, $maxHeight) !== null)
        {
            $errors[] = 'Image must be no more than 1MB';
        }
        return $errors;
        
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























