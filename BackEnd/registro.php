
<?php

session_start();

require_once __DIR__ . "/conexion.php";

// Mostrar mensajes de error con el diseño de FLEM
function mostrarError($mensaje)
{
    http_response_code(400);
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error de registro | FLEM</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
              rel="stylesheet">

        <style>
            body {
                background: #f4f8f6;
                font-family: Arial, sans-serif;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }

            .tarjeta {
                background: white;
                max-width: 480px;
                width: 100%;
                padding: 35px;
                border-radius: 16px;
                box-shadow: 0 5px 20px rgba(0,0,0,.08);
                text-align: center;
            }

            .marca {
                color: #075c45;
                font-size: 25px;
                font-weight: bold;
                letter-spacing: 3px;
            }

            .marca span {
                color: #d4af37;
            }

            .btn-flem {
                background: #075c45;
                color: white;
                border: none;
                padding: 11px 20px;
                border-radius: 8px;
                text-decoration: none;
                display: inline-block;
                margin-top: 15px;
            }

            .btn-flem:hover {
                background: #064735;
                color: white;
            }
        </style>
    </head>

    <body>

        <div class="tarjeta">

            <div class="marca">
                <span>FLEM</span> ×
            </div>

            <h4 class="mt-4 mb-3">No se pudo completar el registro</h4>

            <div class="alert alert-danger">
                <?= htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8") ?>
            </div>

            <a href="../FrontEnd/registro.html" class="btn-flem">
                Volver al registro
            </a>

            <div class="mt-3">
                <a href="../FrontEnd/login.html" class="text-secondary">
                    Ir al inicio de sesión
                </a>
            </div>

        </div>

    </body>
    </html>
    <?php
    exit;
}

// Comprobar que el formulario se haya enviado mediante POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    mostrarError("No se han recibido datos del formulario.");
}

// Comprobar que todos los campos existan
$campos = [
    "nombre",
    "apellido",
    "cedula",
    "edad",
    "telefono",
    "correo",
    "password"
];

foreach ($campos as $campo) {
    if (!isset($_POST[$campo])) {
        mostrarError("Faltan datos del formulario.");
    }
}

// Obtener y limpiar los datos
$nombre = trim($_POST["nombre"]);
$apellido = trim($_POST["apellido"]);
$cedula = trim($_POST["cedula"]);
$edad = trim($_POST["edad"]);
$telefono = trim($_POST["telefono"]);
$correo = trim($_POST["correo"]);
$password = $_POST["password"];

// Comprobar campos vacíos
if (
    $nombre === "" ||
    $apellido === "" ||
    $cedula === "" ||
    $edad === "" ||
    $telefono === "" ||
    $correo === "" ||
    $password === ""
) {
    mostrarError("Todos los campos son obligatorios.");
}

// Validar nombre
if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/u", $nombre)) {
    mostrarError("El nombre solo puede contener letras.");
}

// Validar apellido
if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/u", $apellido)) {
    mostrarError("El apellido solo puede contener letras.");
}

// Validar edad
$edad_validada = filter_var($edad, FILTER_VALIDATE_INT);

if (
    $edad_validada === false ||
    $edad_validada < 0 ||
    $edad_validada > 120
) {
    mostrarError("La edad ingresada no es válida.");
}

// Validar correo electrónico
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    mostrarError("El correo electrónico no es válido.");
}

// Comprobar si la cédula o el correo ya existen
$consulta = $conexion->prepare(
    "SELECT id FROM usuarios WHERE cedula = ? OR correo = ?"
);

if (!$consulta) {
    mostrarError("No se pudo realizar la consulta. Intentá nuevamente.");
}

$consulta->bind_param("ss", $cedula, $correo);
$consulta->execute();

$resultado = $consulta->get_result();

if ($resultado->num_rows > 0) {
    $consulta->close();
    mostrarError("La cédula o el correo electrónico ya están registrados.");
}

$consulta->close();

// Cifrar la contraseña antes de guardarla
$password_segura = password_hash($password, PASSWORD_DEFAULT);

// Asignar automáticamente el rol de paciente
$rol = "Paciente";

// Insertar el nuevo usuario
$insertar = $conexion->prepare(
    "INSERT INTO usuarios
    (nombre, apellido, cedula, correo, edad, password, rol, telefono)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);

if (!$insertar) {
    mostrarError("No se pudo preparar el registro. Intentá nuevamente.");
}

$insertar->bind_param(
    "ssssisss",
    $nombre,
    $apellido,
    $cedula,
    $correo,
    $edad_validada,
    $password_segura,
    $rol,
    $telefono
);

// Ejecutar el registro
if ($insertar->execute()) {

    $id_usuario = $conexion->insert_id;

    // Iniciar sesión automáticamente
    session_regenerate_id(true);

    $_SESSION["id"] = $id_usuario;
    $_SESSION["nombre"] = $nombre;
    $_SESSION["apellido"] = $apellido;
    $_SESSION["correo"] = $correo;
    $_SESSION["rol"] = $rol;

    $insertar->close();
    $conexion->close();

    // Redirigir al panel
    header("Location: panel.php");
    exit;

} else {

    $insertar->close();
    mostrarError("No se pudo registrar el paciente. Verificá los datos e intentá nuevamente.");
}

?>
