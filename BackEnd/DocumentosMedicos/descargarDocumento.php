
<?php

session_start();

// ===================================
// CONEXIÓN A LA BASE DE DATOS
// ===================================

require_once __DIR__ . "/../conexion.php";

// ===================================
// VERIFICAR SESIÓN
// ===================================

if (!isset($_SESSION["id"]) || !isset($_SESSION["rol"])) {
    http_response_code(401);
    exit("Debés iniciar sesión para descargar documentos.");
}

// SOLO PACIENTES
if ($_SESSION["rol"] !== "Paciente") {
    http_response_code(403);
    exit("No tenés permisos para descargar este documento.");
}

$id_paciente = (int) $_SESSION["id"];

// ===================================
// VERIFICAR ID DEL DOCUMENTO
// ===================================

if (!isset($_GET["id"]) || !ctype_digit((string)$_GET["id"])) {
    http_response_code(400);
    exit("El documento solicitado no es válido.");
}

$id_documento = (int) $_GET["id"];

// ===================================
// BUSCAR DOCUMENTO EN MYSQL
// ===================================

$consulta = $conexion->prepare(
    "SELECT nombre_documento, tipo_documento, archivo
     FROM documentos_medicos
     WHERE id_documento = ?
     AND id_paciente = ?
     LIMIT 1"
);

if (!$consulta) {
    http_response_code(500);
    exit("Error al consultar el documento.");
}

$consulta->bind_param("ii", $id_documento, $id_paciente);
$consulta->execute();

$consulta->bind_result(
    $nombre_documento,
    $tipo_documento,
    $archivo
);

if (!$consulta->fetch()) {
    $consulta->close();
    http_response_code(404);
    exit("No se encontró el documento o no tenés acceso a él.");
}

$consulta->close();

// ===================================
// LOCALIZAR ARCHIVO
// ===================================

// Directorio principal del proyecto
$directorio_principal = dirname(__DIR__, 2);

// Carpeta donde se guardan los documentos
$carpeta_documentos = realpath(
    $directorio_principal .
    DIRECTORY_SEPARATOR .
    "Documentos Medicos" .
    DIRECTORY_SEPARATOR .
    "documentos_medicos"
);

if ($carpeta_documentos === false) {
    http_response_code(500);
    exit("No se encontró la carpeta de documentos médicos.");
}

// Normalizar ruta guardada en MySQL
$ruta_relativa = str_replace("\\", "/", (string)$archivo);

// Evitar rutas externas o intentos de salir de la carpeta
if (
    $ruta_relativa === "" ||
    str_starts_with($ruta_relativa, "/") ||
    preg_match('/^[A-Za-z]:/', $ruta_relativa) ||
    in_array("..", explode("/", $ruta_relativa), true)
) {
    http_response_code(400);
    exit("La ruta del documento no es válida.");
}

// Obtener solamente el nombre del archivo
$nombre_archivo = basename($ruta_relativa);

// Buscar el archivo dentro de la carpeta permitida
$ruta_archivo = realpath(
    $carpeta_documentos .
    DIRECTORY_SEPARATOR .
    $nombre_archivo
);

// Verificar que exista y esté dentro de la carpeta
if (
    $ruta_archivo === false ||
    !is_file($ruta_archivo) ||
    !is_readable($ruta_archivo) ||
    dirname($ruta_archivo) !== $carpeta_documentos
) {
    http_response_code(404);
    exit("El archivo no existe o no se puede leer.");
}

// ===================================
// PREPARAR DESCARGA
// ===================================

$extension = strtolower(pathinfo($ruta_archivo, PATHINFO_EXTENSION));

$mime = "application/octet-stream";

if (function_exists("finfo_open")) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);

    if ($finfo) {
        $mime_detectado = finfo_file($finfo, $ruta_archivo);

        if ($mime_detectado) {
            $mime = $mime_detectado;
        }

        finfo_close($finfo);
    }
}

// Nombre que verá el paciente al descargar
$nombre_descarga = trim((string)$nombre_documento);

if ($nombre_descarga === "") {
    $nombre_descarga = "documento_medico";
}

$nombre_descarga = basename(
    str_replace("\\", "/", $nombre_descarga)
);

if (pathinfo($nombre_descarga, PATHINFO_EXTENSION) === "" && $extension !== "") {
    $nombre_descarga .= "." . $extension;
}

// Evitar caracteres problemáticos en los encabezados
$nombre_ascii = preg_replace(
    '/[^A-Za-z0-9._-]/',
    "_",
    $nombre_descarga
);

if ($nombre_ascii === "") {
    $nombre_ascii = "documento_medico";
}

// ===================================
// ENVIAR ARCHIVO AL NAVEGADOR
// ===================================

if (ob_get_level()) {
    while (ob_get_level()) {
        ob_end_clean();
    }
}

header("Content-Description: File Transfer");
header("Content-Type: " . $mime);
header(
    'Content-Disposition: attachment; filename="' .
    $nombre_ascii .
    '"; filename*=UTF-8\'\'' .
    rawurlencode($nombre_descarga)
);
header("Content-Length: " . filesize($ruta_archivo));
header("Cache-Control: private, no-store, no-cache, must-revalidate");
header("Pragma: public");
header("X-Content-Type-Options: nosniff");

readfile($ruta_archivo);

$conexion->close();
exit;

?>
