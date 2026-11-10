
<?php

session_start();

require_once __DIR__ . "/../conexion.php";

// =====================================
// 1. VERIFICAR SESIÓN Y PERMISOS
// =====================================

if (!isset($_SESSION["id"])) {
    die("Debes iniciar sesión.");
}

if (
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "Administrador"
) {
    die("Acceso denegado. Solo administradores.");
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Método no permitido.");
}

// =====================================
// 2. RECIBIR DATOS
// =====================================

$id_paciente = filter_input(
    INPUT_POST,
    "id_paciente",
    FILTER_VALIDATE_INT
);

$nombre = trim($_POST["nombre_documento"] ?? "");
$categoria = $_POST["categoria"] ?? "";
$descripcion = trim($_POST["descripcion"] ?? "");

$categoriasPermitidas = [
    "Analisis clinicos",
    "Estudios medicos",
    "Informes medicos",
    "Recetas medicas",
    "Otros"
];

if (
    !$id_paciente ||
    $nombre === "" ||
    !in_array($categoria, $categoriasPermitidas, true)
) {
    die("Completa correctamente todos los campos.");
}

if (strlen($nombre) > 200) {
    die("El nombre del documento es demasiado largo.");
}

// =====================================
// 3. VERIFICAR QUE EXISTA EL PACIENTE
// =====================================

$consulta = $conexion->prepare(
    "SELECT id FROM usuarios
     WHERE id = ? AND rol = 'Paciente'"
);

$consulta->bind_param("i", $id_paciente);
$consulta->execute();

$resultado = $consulta->get_result();

if ($resultado->num_rows !== 1) {
    die("El paciente seleccionado no existe.");
}

$consulta->close();

// =====================================
// 4. VALIDAR ARCHIVO
// =====================================

if (!isset($_FILES["documento"])) {
    die("No se recibió ningún archivo.");
}

$archivo = $_FILES["documento"];

if ($archivo["error"] !== UPLOAD_ERR_OK) {
    die("Error al recibir el archivo. Código: " . $archivo["error"]);
}

if ($archivo["size"] > 10 * 1024 * 1024) {
    die("El archivo supera el límite de 10 MB.");
}

if (
    $archivo["size"] <= 0 ||
    !is_uploaded_file($archivo["tmp_name"])
) {
    die("El archivo recibido no es válido.");
}

// =====================================
// 5. VALIDAR FORMATO Y CONTENIDO
// =====================================

$extension = strtolower(
    pathinfo($archivo["name"], PATHINFO_EXTENSION)
);

$tiposPermitidos = [
    "pdf" => ["application/pdf"],
    "jpg" => ["image/jpeg"],
    "jpeg" => ["image/jpeg"],
    "png" => ["image/png"],
    "docx" => [
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        "application/zip"
    ],
    "xlsx" => [
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        "application/zip"
    ]
];

if (!isset($tiposPermitidos[$extension])) {
    die("Formato no permitido.");
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($archivo["tmp_name"]);

if (!in_array($mime, $tiposPermitidos[$extension], true)) {
    die("El contenido del archivo no coincide con su extensión.");
}

// COMPROBAR DOCUMENTOS OFFICE

if (in_array($extension, ["docx", "xlsx"], true)) {

    if (!class_exists("ZipArchive")) {
        die("La extensión ZIP de PHP no está habilitada.");
    }

    $zip = new ZipArchive();

    if ($zip->open($archivo["tmp_name"]) !== true) {
        die("El documento Office no es válido.");
    }

    $archivoInterno = ($extension === "docx")
        ? "word/document.xml"
        : "xl/workbook.xml";

    $valido =
        $zip->locateName("[Content_Types].xml") !== false &&
        $zip->locateName($archivoInterno) !== false;

    $zip->close();

    if (!$valido) {
        die("La estructura del documento Office no es válida.");
    }
}

// =====================================
// 6. VERIFICAR ALMACENAMIENTO
// =====================================

$directorio = dirname(__DIR__, 2)
    . DIRECTORY_SEPARATOR
    . "Documentos Medicos"
    . DIRECTORY_SEPARATOR
    . "documentos_medicos";

if (!is_dir($directorio)) {
    die("No existe la carpeta de almacenamiento de documentos.");
}

if (!is_writable($directorio)) {
    die("No hay permisos para guardar documentos.");
}

// =====================================
// 7. GENERAR NOMBRE SEGURO
// =====================================

$nombreArchivo = bin2hex(random_bytes(24))
    . "." . $extension;

$rutaDestino = $directorio
    . DIRECTORY_SEPARATOR
    . $nombreArchivo;

if (!move_uploaded_file($archivo["tmp_name"], $rutaDestino)) {
    die("No se pudo guardar el archivo.");
}

// =====================================
// 8. GUARDAR INFORMACIÓN EN MYSQL
// =====================================

$rutaBD = "Documentos Medicos/documentos_medicos/" . $nombreArchivo;

$id_administrador = $_SESSION["id"];
$tipo = strtoupper($extension);

$consulta = $conexion->prepare(
    "INSERT INTO documentos_medicos
    (
        id_paciente,
        id_usuario_subio,
        nombre_documento,
        categoria,
        descripcion,
        tipo_documento,
        archivo
    )
    VALUES (?, ?, ?, ?, ?, ?, ?)"
);

if (!$consulta) {

    unlink($rutaDestino);

    die("Error al preparar la consulta: " . $conexion->error);
}

$consulta->bind_param(
    "iisssss",
    $id_paciente,
    $id_administrador,
    $nombre,
    $categoria,
    $descripcion,
    $tipo,
    $rutaBD
);

if ($consulta->execute()) {

    $consulta->close();
    $conexion->close();

    echo "
    <script>
        alert('Documento médico cargado correctamente.');
        window.location.href =
        '/FrontEnd/DocumentosMedicos/documentosMedicos.php';
    </script>
    ";

    exit;

} else {

    unlink($rutaDestino);

    die("Error al registrar el documento: " . $consulta->error);
}

?>
