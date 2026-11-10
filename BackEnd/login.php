
<?php

session_start();

require_once __DIR__ . "/conexion.php";

// Mostrar errores con el diseño de FLEM
function mostrarError($mensaje) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error de inicio de sesión | FLEM</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

        <style>
            body {
                background: #f3f7f5;
                font-family: Arial, sans-serif;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
            }

            .navbar-flem {
                background: #075b46;
                padding: 17px 0;
            }

            .logo-flem {
                color: white;
                font-size: 22px;
                font-weight: bold;
                letter-spacing: 2px;
                text-decoration: none;
            }

            .contenedor {
                flex: 1;
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 25px;
            }

            .tarjeta {
                background: white;
                width: 100%;
                max-width: 440px;
                padding: 40px 30px;
                border-radius: 16px;
                text-align: center;
                box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            }

            .icono {
                width: 75px;
                height: 75px;
                background: #fff3d5;
                color: #c49b29;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 38px;
                margin: 0 auto 20px;
            }

            h2 {
                color: #075b46;
                font-weight: bold;
                margin-bottom: 12px;
            }

            .mensaje {
                color: #68756e;
                margin-bottom: 25px;
                line-height: 1.6;
            }

            .boton {
                display: block;
                width: 100%;
                background: #075b46;
                color: white;
                padding: 12px;
                border-radius: 8px;
                text-decoration: none;
                font-weight: bold;
                border: none;
                cursor: pointer;
            }

            .boton:hover {
                background: #064735;
                color: white;
            }

            .pie {
                text-align: center;
                color: #82918a;
                padding: 15px;
                font-size: 13px;
            }
        </style>
    </head>

    <body>

        <nav class="navbar-flem">
            <div class="container">
                <a href="../index.html" class="logo-flem">⚕ FLEM</a>
            </div>
        </nav>

        <main class="contenedor">
            <div class="tarjeta">

                <div class="icono">!</div>

                <h2>Datos incorrectos</h2>

                <p class="mensaje">
                    <?= htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8") ?>
                </p>

                <button class="boton" onclick="history.back()">
                    Volver a intentar
                </button>

            </div>
        </main>

        <footer class="pie">
            FLEM - Sistema Hospitalario
        </footer>

    </body>
    </html>
    <?php
    exit;
}

// Comprobar que se recibieron datos
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    mostrarError("Debés ingresar desde el formulario de inicio de sesión.");
}

if (!isset($_POST["correo"]) || !isset($_POST["password"])) {
    mostrarError("Faltan datos. Completá el correo y la contraseña.");
}

$correo = trim($_POST["correo"]);
$password = $_POST["password"];

if (empty($correo) || empty($password)) {
    mostrarError("El correo y la contraseña son obligatorios.");
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    mostrarError("El formato del correo electrónico no es válido.");
}

// Buscar usuario
$consulta = $conexion->prepare(
    "SELECT id, nombre, apellido, correo, password, rol
     FROM usuarios
     WHERE correo = ?"
);

if (!$consulta) {
    mostrarError("Ocurrió un problema al iniciar sesión. Intentá nuevamente.");
}

$consulta->bind_param("s", $correo);
$consulta->execute();

$resultado = $consulta->get_result();

if ($resultado->num_rows === 0) {
    $consulta->close();
    mostrarError("El correo o la contraseña son incorrectos.");
}

$usuario = $resultado->fetch_assoc();
$consulta->close();

// Verificar contraseña
if (!password_verify($password, $usuario["password"])) {
    mostrarError("El correo o la contraseña son incorrectos.");
}

// Crear sesión
session_regenerate_id(true);

$_SESSION["id"] = $usuario["id"];
$_SESSION["nombre"] = $usuario["nombre"];
$_SESSION["apellido"] = $usuario["apellido"];
$_SESSION["correo"] = $usuario["correo"];
$_SESSION["rol"] = $usuario["rol"];

// Ingresar al panel
header("Location: panel.php");
exit;

?>
