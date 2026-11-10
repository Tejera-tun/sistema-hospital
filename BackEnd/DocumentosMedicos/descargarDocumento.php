
<?php
session_start();

require_once __DIR__ . "/../conexion.php";

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION["id"]) || !isset($_SESSION["rol"])) {
    http_response_code(403);
    exit("No tenés permiso para descargar este documento.");
}

$id_documento = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id_documento) {
    http_response_code(400);
    exit("Documento no válido.");
}

// Buscar el documento
$sql = "SELECT id_documento, id_paciente, nombre_documento, archivo
        FROM documentos_medicos
        WHERE id_documento = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_documento);
$stmt->execute();

$resultado = $stmt->get_result();
$documento = $resultado->fetch_assoc();

$stmt->close();

if (!$documento) {
    http_response_code(404);
    exit("El documento no existe.");
}

// Los pacientes solo pueden descargar sus propios documentos
if (
    $_SESSION["rol"] === "Paciente" &&
    (int) $documento["id_paciente"] !== (int) $_SESSION["id"]
) {
    http_response_code(403);
    exit("No tenés permiso para descargar este documento.");
}

// Solo administradores y pacientes pueden descargar
if (
    $_SESSION["rol"] !== "Administrador" &&
    $_SESSION["rol"] !== "Paciente"
) {
    http_response_code(403);
    exit("No tenés permiso para descargar este documento.");
}

// Obtener únicamente el nombre del archivo almacenado
$nombre_archivo = basename(str_replace("\\", "/", $documento["archivo"]));

// Carpeta privada donde se guardaron los documentos
$directorio = dirname(__DIR__, 2)
    . DIRECTORY_SEPARATOR
    . "Documentos Medicos"
    . DIRECTORY_SEPARATOR
    . "documentos_medicos";

$ruta_archivo = $directorio . DIRECTORY_SEPARATOR . $nombre_archivo;

// Comprobar que el archivo exista
if (!is_file($ruta_archivo) || !is_readable($ruta_archivo)) {
    http_response_code(404);
    exit("No se encontró el archivo almacenado.");
}

// Detectar el tipo de archivo
$finfo = new finfo(FILEINFO_MIME_TYPE);
$tipo_mime = $finfo->file($ruta_archivo);

if ($tipo_mime === false) {
    $tipo_mime = "application/octet-stream";
}

// Preparar la descarga
$nombre_descarga = preg_replace(
    '/[^A-Za-z0-9._-]/',
    '_',
    $documento["nombre_documento"]
);

$extension = pathinfo($nombre_archivo, PATHINFO_EXTENSION);

if (
    $extension &&
    strtolower(pathinfo($nombre_descarga, PATHINFO_EXTENSION)) !== strtolower($extension)
) {
    $nombre_descarga .= "." . $extension;
}

$conexion->close();

header("Content-Description: File Transfer");
header("Content-Type: " . $tipo_mime);
header('Content-Disposition: attachment; filename="' . $nombre_descarga . '"');
header("Content-Length: " . filesize($ruta_archivo));
header("Cache-Control: private, no-store");
header("X-Content-Type-Options: nosniff");

readfile($ruta_archivo);
exit;
