
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
    die("Acceso denegado. Solo administradores.");
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../../FrontEnd/DocumentosMedicos/documentosMedicos.php");
    exit;
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
    die("Completá correctamente todos los campos.");
}

if (strlen($nombre) > 200) {
    die("El nombre del documento es demasiado largo.");
}

// =====================================
// 3. VERIFICAR QUE EXISTA EL PACIENTE
// =====================================

$consulta = $conexion->prepare(
    "SELECT id
     FROM usuarios
     WHERE id = ? AND rol = 'Paciente'"
);

if (!$consulta) {
    die("No se pudo preparar la consulta del paciente.");
}

$consulta->bind_param("i", $id_paciente);
$consulta->execute();

$resultado = $consulta->get_result();

if ($resultado->num_rows !== 1) {
    $consulta->close();
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
    die("Error al recibir el archivo. Código: " . (int)$archivo["error"]);
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
    die("Formato de archivo no permitido.");
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($archivo["tmp_name"]);

if (!in_array($mime, $tiposPermitidos[$extension], true)) {
    die("El contenido del archivo no coincide con su extensión.");
}

// =====================================
// 6. COMPROBAR DOCUMENTOS OFFICE
// =====================================

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
// 7. CREAR Y VERIFICAR ALMACENAMIENTO
// =====================================

// Obtener la carpeta principal del proyecto.
$directorioPrincipal = dirname(__DIR__, 2);

// Definir la carpeta de almacenamiento.
$directorio = $directorioPrincipal
    . DIRECTORY_SEPARATOR
    . "Documentos Medicos"
    . DIRECTORY_SEPARATOR
    . "documentos_medicos";

// CREAR LA CARPETA AUTOMÁTICAMENTE SI NO EXISTE.

if (!is_dir($directorio)) {

    if (!mkdir($directorio, 0755, true) && !is_dir($directorio)) {
        die("No se pudo crear la carpeta de almacenamiento. Verificá los permisos del servidor.");
    }

}

// Verificar que realmente exista.
if (!is_dir($directorio)) {
    die("No se pudo encontrar la carpeta de almacenamiento.");
}

// Verificar permisos de escritura.
if (!is_writable($directorio)) {
    die("No hay permisos para guardar documentos en esta carpeta.");
}

// =====================================
// 8. GENERAR NOMBRE SEGURO
// =====================================

$nombreArchivo = bin2hex(random_bytes(24))
    . "." . $extension;

$rutaDestino = $directorio
    . DIRECTORY_SEPARATOR
    . $nombreArchivo;

// Guardar archivo físicamente.
if (!move_uploaded_file($archivo["tmp_name"], $rutaDestino)) {
    die("No se pudo guardar el archivo.");
}

// =====================================
// 9. GUARDAR INFORMACIÓN EN MYSQL
// =====================================

// Ruta relativa que se almacena en la base de datos.
$rutaBD = "Documentos Medicos/documentos_medicos/" . $nombreArchivo;

$id_administrador = (int)$_SESSION["id"];
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

    if (is_file($rutaDestino)) {
        unlink($rutaDestino);
    }

    die("No se pudo preparar el registro del documento.");
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

// =====================================
// 10. EJECUTAR REGISTRO
// =====================================

if ($consulta->execute()) {

    $consulta->close();
    $conexion->close();

    ?>

    <!DOCTYPE html>
    <html lang="es">

    <head>

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Documento cargado</title>

        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
            rel="stylesheet"
        >

        <style>

            body {
                background: #f4f8f6;
                font-family: Arial, sans-serif;
            }

            .mensaje {
                max-width: 500px;
                margin: 100px auto;
                padding: 35px;
                background: white;
                border-radius: 18px;
                text-align: center;
                box-shadow: 0 4px 15px rgba(0,0,0,.08);
            }

            .marca {
                color: #d4af37;
                font-size: 23px;
                font-weight: bold;
                letter-spacing: 2px;
            }

            .btn-flem {
                background: #075c45;
                color: white;
            }

            .btn-flem:hover {
                background: #064936;
                color: white;
            }

        </style>

    </head>

    <body>

        <div class="mensaje">

            <div class="marca">
                FLEM × Hospital de Clínicas
            </div>

            <h3 class="mt-4 text-success">
                ¡Documento cargado!
            </h3>

            <p>
                El documento médico se registró correctamente.
            </p>

            <a
                href="../../FrontEnd/DocumentosMedicos/documentosMedicos.php"
                class="btn btn-flem mt-2"
            >
                Volver a documentos médicos
            </a>

        </div>

    </body>

    </html>

    <?php

    exit;

} else {

    // Si falla MySQL, eliminar el archivo que acabamos de subir.
    if (is_file($rutaDestino)) {
        unlink($rutaDestino);
    }

    $consulta->close();
    $conexion->close();

    die("No se pudo registrar el documento en la base de datos.");

}

?>
