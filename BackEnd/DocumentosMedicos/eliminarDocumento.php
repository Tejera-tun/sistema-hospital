
<?php

session_start();

require_once __DIR__ . "/../conexion.php";

// =====================================
// 1. VERIFICAR SESIÓN Y PERMISOS
// =====================================

if (
    !isset($_SESSION["id"]) ||
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "Administrador"
) {
    http_response_code(403);
    exit("Acceso denegado.");
}

// =====================================
// 2. VERIFICAR MÉTODO POST
// =====================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../../FrontEnd/DocumentosMedicos/documentosMedicos.php");
    exit;
}

// =====================================
// 3. VERIFICAR TOKEN CSRF
// =====================================

if (
    !isset($_POST["csrf"]) ||
    !isset($_SESSION["csrf"]) ||
    !hash_equals($_SESSION["csrf"], $_POST["csrf"])
) {
    http_response_code(403);
    exit("Solicitud no válida.");
}

// =====================================
// 4. VALIDAR IDENTIFICADOR
// =====================================

$id_documento = filter_input(INPUT_POST, "id_documento", FILTER_VALIDATE_INT);

if (!$id_documento || $id_documento <= 0) {
    header("Location: ../../FrontEnd/DocumentosMedicos/documentosMedicos.php?error=id");
    exit;
}

// =====================================
// 5. BUSCAR DOCUMENTO
// =====================================

$consulta = $conexion->prepare(
    "SELECT id_documento, archivo
     FROM documentos_medicos
     WHERE id_documento = ?"
);

if (!$consulta) {
    http_response_code(500);
    exit("No se pudo realizar la consulta.");
}

$consulta->bind_param("i", $id_documento);
$consulta->execute();

$documento = $consulta->get_result()->fetch_assoc();

$consulta->close();

if (!$documento) {
    $conexion->close();

    header("Location: ../../FrontEnd/DocumentosMedicos/documentosMedicos.php?error=no_encontrado");
    exit;
}

// =====================================
// 6. LOCALIZAR ARCHIVO
// =====================================

$directorio = dirname(__DIR__, 2)
    . DIRECTORY_SEPARATOR
    . "Documentos Medicos"
    . DIRECTORY_SEPARATOR
    . "documentos_medicos";

$directorioReal = realpath($directorio);

$rutaReal = false;

if ($directorioReal !== false && is_dir($directorioReal)) {

    $nombreArchivo = basename(
        str_replace("\\", "/", $documento["archivo"])
    );

    if ($nombreArchivo !== "" && $nombreArchivo !== "." && $nombreArchivo !== "..") {

        $rutaReal = realpath(
            $directorioReal . DIRECTORY_SEPARATOR . $nombreArchivo
        );
    }
}

// =====================================
// 7. ELIMINAR REGISTRO DE MYSQL
// =====================================

$eliminar = $conexion->prepare(
    "DELETE FROM documentos_medicos
     WHERE id_documento = ?"
);

if (!$eliminar) {
    $conexion->close();
    http_response_code(500);
    exit("No se pudo preparar la eliminación.");
}

$eliminar->bind_param("i", $id_documento);

if (!$eliminar->execute() || $eliminar->affected_rows !== 1) {

    $eliminar->close();
    $conexion->close();

    header("Location: ../../FrontEnd/DocumentosMedicos/documentosMedicos.php?error=eliminacion");
    exit;
}

$eliminar->close();

// =====================================
// 8. ELIMINAR ARCHIVO FÍSICO
// =====================================

if (
    $rutaReal !== false &&
    is_file($rutaReal) &&
    dirname($rutaReal) === $directorioReal
) {

    if (!unlink($rutaReal)) {

        $conexion->close();

        header("Location: ../../FrontEnd/DocumentosMedicos/documentosMedicos.php?error=archivo");
        exit;
    }
}

$conexion->close();

// =====================================
// 9. VOLVER A DOCUMENTOS MÉDICOS
// =====================================

header("Location: ../../FrontEnd/DocumentosMedicos/documentosMedicos.php?eliminado=1");
exit;

?>
