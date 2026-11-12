
<?php

session_start();

require_once __DIR__ . "/../conexion.php";

// ===================================
// VERIFICAR SESIÓN Y PERMISOS
// ===================================

if (
    !isset($_SESSION["id"]) ||
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "Administrador"
) {
    header("Location: ../login.php");
    exit;
}

// ===================================
// VERIFICAR MÉTODO POST
// ===================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: listarSeguimiento.php");
    exit;
}

// ===================================
// VERIFICAR TOKEN DE SEGURIDAD
// ===================================

if (
    !isset($_POST["csrf"]) ||
    !isset($_SESSION["csrf"]) ||
    !hash_equals($_SESSION["csrf"], $_POST["csrf"])
) {
    die("Solicitud no válida.");
}

// ===================================
// VALIDAR IDENTIFICADOR
// ===================================

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header("Location: listarSeguimiento.php?error=id");
    exit;
}

// ===================================
// ELIMINAR SEGUIMIENTO
// ===================================

try {

    $consulta = $conexion->prepare(
        "DELETE FROM seguimientos WHERE id = ?"
    );

    $consulta->bind_param("i", $id);
    $consulta->execute();

    if ($consulta->affected_rows > 0) {

        $consulta->close();
        $conexion->close();

        header("Location: listarSeguimiento.php?eliminado=1");
        exit;

    }

    $consulta->close();
    $conexion->close();

    header("Location: listarSeguimiento.php?error=no_encontrado");
    exit;

} catch (mysqli_sql_exception $e) {

    $conexion->close();

    header("Location: listarSeguimiento.php?error=eliminacion");
    exit;

}

?>
