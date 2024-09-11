<?php
// Configuración
define('MAX_FILE_SIZE', 1 * 1024 * 1024); // 1MB
define('TARGET_WIDTH', 300);
define('UPLOAD_DIR', 'uploads/');

// Función para comprobar si el archivo es una imagen válida
function esImagenValida($tmpName) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $fileInfo = @getimagesize($tmpName);
    
    if (!$fileInfo) {
        return false;
    }
    
    return in_array($fileInfo['mime'], $allowedTypes);
}

// Función para redimensionar la imagen
function redimensionarImagen($sourcePath, $targetPath, $targetWidth) {
    list($width, $height, $type) = getimagesize($sourcePath);
    $ratio = $targetWidth / $width;
    $targetHeight = $height * $ratio;

    $sourceImage = imagecreatefromstring(file_get_contents($sourcePath));
    $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);

    imagecopyresampled($targetImage, $sourceImage, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($targetImage, $targetPath, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($targetImage, $targetPath, 9);
            break;
        case IMAGETYPE_GIF:
            imagegif($targetImage, $targetPath);
            break;
    }

    imagedestroy($sourceImage);
    imagedestroy($targetImage);
}

// Procesar la subida del archivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['imagen'])) {
    $file = $_FILES['imagen'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("Error al subir el archivo.");
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        die("El archivo es demasiado grande. Máximo 1MB permitido.");
    }

    if (!esImagenValida($file['tmp_name'])) {
        die("El archivo no es una imagen válida.");
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $nombreArchivo = 'image_' . date('YmdHis') . '.' . $extension;
    $rutaDestino = UPLOAD_DIR . $nombreArchivo;

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    redimensionarImagen($file['tmp_name'], $rutaDestino, TARGET_WIDTH);

    echo "Imagen subida y procesada con éxito: " . $nombreArchivo;
} else {
    // Formulario HTML para subir la imagen
    echo '
    <h1>Subir archivos</h1>
    <form action="" method="post" enctype="multipart/form-data">
        <input type="file" name="imagen" accept="image/*" required>
        <input type="submit" value="Subir imagen">
    </form>
    ';
}


/**
 * Configuración en php.ini
* ; Maximum size of POST data that PHP will accept.
* ; Its value may be 0 to disable the limit. It is ignored if POST data reading
* ; is disabled through enable_post_data_reading.
* ; http://php.net/post-max-size
* post_max_size=40M
* ; Maximum allowed size for uploaded files.
* ; http://php.net/upload-max-filesize
* upload_max_filesize=40M

* ; Maximum number of files that can be uploaded via a single request
* max_file_uploads=20


* ; Defines the default timezone used by the date functions
* ; http://php.net/date.timezone
* ;date.timezone =
 */
?>