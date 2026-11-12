
<?php

session_start();

require_once __DIR__ . "/conexion.php";

// Comprobar que el formulario envió los datos
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../FrontEnd/login.html");
    exit;
}

if (!isset($_POST["correo"], $_POST["password"])) {
    header("Location: ../FrontEnd/login.html?error=datos");
    exit;
}

$correo = trim($_POST["correo"]);
$password = $_POST["password"];

if (
    $correo === "" ||
    $password === "" ||
    !filter_var($correo, FILTER_VALIDATE_EMAIL)
) {
    header("Location: ../FrontEnd/login.html?error=datos");
    exit;
}

// Buscar usuario
$consulta = $conexion->prepare(
    "SELECT id, nombre, apellido, correo, password, rol
     FROM usuarios
     WHERE correo = ?"
);

if (!$consulta) {
    header("Location: ../FrontEnd/login.html?error=servidor");
    exit;
}

$consulta->bind_param("s", $correo);
$consulta->execute();

$resultado = $consulta->get_result();

if ($resultado->num_rows === 0) {
    $consulta->close();
    header("Location: ../FrontEnd/login.html?error=credenciales");
    exit;
}

$usuario = $resultado->fetch_assoc();
$consulta->close();

// Verificar contraseña
if (!password_verify($password, $usuario["password"])) {
    header("Location: ../FrontEnd/login.html?error=credenciales");
    exit;
}

// Crear sesión
session_regenerate_id(true);

$_SESSION["id"] = $usuario["id"];
$_SESSION["nombre"] = $usuario["nombre"];
$_SESSION["apellido"] = $usuario["apellido"];
$_SESSION["correo"] = $usuario["correo"];
$_SESSION["rol"] = $usuario["rol"];

// Ir al panel
$conexion->close();

header("Location: panel.php");
exit;

?>
