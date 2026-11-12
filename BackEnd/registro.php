
<?php

session_start();

require_once __DIR__ . "/conexion.php";

// Comprobar que el formulario se haya enviado mediante POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../FrontEnd/registro.html");
    exit;
}

// Recibir los datos
$nombre = trim($_POST["nombre"] ?? "");
$apellido = trim($_POST["apellido"] ?? "");
$cedula = trim($_POST["cedula"] ?? "");
$edad = trim($_POST["edad"] ?? "");
$telefono = trim($_POST["telefono"] ?? "");
$correo = trim($_POST["correo"] ?? "");
$password = $_POST["password"] ?? "";

// Validar campos obligatorios
if (
    $nombre === "" ||
    $apellido === "" ||
    $cedula === "" ||
    $edad === "" ||
    $telefono === "" ||
    $correo === "" ||
    $password === ""
) {
    header("Location: ../FrontEnd/registro.html?error=campos");
    exit;
}

// Validar correo
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../FrontEnd/registro.html?error=correo");
    exit;
}

// Validar edad
if (!filter_var($edad, FILTER_VALIDATE_INT) || $edad < 0 || $edad > 120) {
    header("Location: ../FrontEnd/registro.html?error=edad");
    exit;
}

// Comprobar si el correo o la cédula ya existen
$consulta = $conexion->prepare(
    "SELECT id FROM usuarios WHERE correo = ? OR cedula = ?"
);

if (!$consulta) {
    die("Error al preparar la consulta.");
}

$consulta->bind_param("ss", $correo, $cedula);
$consulta->execute();

$resultado = $consulta->get_result();

if ($resultado->num_rows > 0) {
    $consulta->close();
    $conexion->close();

    header("Location: ../FrontEnd/registro.html?error=existente");
    exit;
}

$consulta->close();

// Encriptar contraseña
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Registrar paciente
$rol = "Paciente";

$insertar = $conexion->prepare(
    "INSERT INTO usuarios
    (nombre, apellido, cedula, edad, telefono, correo, password, rol)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);

if (!$insertar) {
    die("Error al preparar el registro.");
}

$edad = (int) $edad;

$insertar->bind_param(
    "sssissss",
    $nombre,
    $apellido,
    $cedula,
    $edad,
    $telefono,
    $correo,
    $passwordHash,
    $rol
);

if ($insertar->execute()) {

    $insertar->close();
    $conexion->close();

    header("Location: ../FrontEnd/login.html?registro=exitoso");
    exit;

} else {

    $insertar->close();
    $conexion->close();

    header("Location: ../FrontEnd/registro.html?error=registro");
    exit;
}

?>
