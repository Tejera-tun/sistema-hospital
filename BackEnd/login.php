
<?php

session_start();

require_once __DIR__ . "/conexion.php";

// Mostrar errores con el diseño de FLEM
function mostrarError($mensaje)
{
    ?>
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Error de inicio de sesión | FLEM</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
              rel="stylesheet">

        <style>
            :root {
                --verde: #075c45;
                --verde-oscuro: #064735;
                --dorado: #d4af37;
                --fondo: #f4f8f6;
            }

            body {
                background: var(--fondo);
                font-family: Arial, sans-serif;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
            }

            .navbar-flem {
                background: var(--verde);
                padding: 12px 0;
            }

            .brand {
                display: flex;
                align-items: center;
                gap: 12px;
                text-decoration: none;
            }

            .logo-hospital {
                width: 48px;
                height: 48px;
                object-fit: contain;
            }

            .brand-text {
                color: white;
                font-size: 23px;
                font-weight: bold;
                letter-spacing: 2px;
            }

            .brand-text .flem,
            .brand-text .cruz {
                color: var(--dorado);
            }

            .brand-text small {
                display: block;
                font-size: 11px;
                font-weight: normal;
                letter-spacing: 1px;
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
                max-width: 460px;
                padding: 40px 30px;
                border-radius: 17px;
                text-align: center;
                box-shadow: 0 5px 20px rgba(0,0,0,.08);
            }

            .icono {
                width: 75px;
                height: 75px;
                background: #f8edc9;
                color: #a98519;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 38px;
                font-weight: bold;
                margin: 0 auto 20px;
            }

            h2 {
                color: var(--verde);
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
                background: var(--verde);
                color: white;
                padding: 12px;
                border-radius: 8px;
                text-decoration: none;
                font-weight: bold;
                border: none;
                cursor: pointer;
            }

            .boton:hover {
                background: var(--verde-oscuro);
                color: white;
            }

            .enlace {
                display: inline-block;
                margin-top: 18px;
                color: var(--verde);
                text-decoration: none;
                font-weight: bold;
            }

            .enlace:hover {
                text-decoration: underline;
            }

            .pie {
                text-align: center;
                color: #82918a;
                padding: 15px;
                font-size: 13px;
            }

            @media (max-width: 500px) {
                .logo-hospital {
                    width: 38px;
                    height: 38px;
                }

                .brand-text {
                    font-size: 19px;
                }

                .brand-text small {
                    font-size: 9px;
                }

                .tarjeta {
                    padding: 30px 22px;
                }
            }
        </style>
    </head>

    <body>

        <nav class="navbar-flem">
            <div class="container">

                <a href="../FrontEnd/login.html" class="brand">

                    <img
                        src="../FrontEnd/img/logo-hospital-clinicas.png"
                        alt="Logo Hospital de Clínicas"
                        class="logo-hospital"
                    >

                    <span class="brand-text">
                        <span class="flem">FLEM</span><span class="cruz"> ×</span>
                        <small>HOSPITAL DE CLÍNICAS</small>
                    </span>

                </a>

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

                <a href="../FrontEnd/login.html" class="enlace">
                    Volver al inicio de sesión
                </a>

            </div>

        </main>

        <footer class="pie">
            FLEM × Hospital de Clínicas
            <br>
            Sistema de gestión hospitalaria
        </footer>

    </body>
    </html>
    <?php
    exit;
}

// Comprobar que se recibieron datos mediante POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    mostrarError("Debés ingresar desde el formulario de inicio de sesión.");
}

if (!isset($_POST["correo"]) || !isset($_POST["password"])) {
    mostrarError("Faltan datos. Completá el correo y la contraseña.");
}

// Obtener datos
$correo = trim($_POST["correo"]);
$password = $_POST["password"];

// Validar campos vacíos
if ($correo === "" || $password === "") {
    mostrarError("El correo y la contraseña son obligatorios.");
}

// Validar formato del correo
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    mostrarError("El formato del correo electrónico no es válido.");
}

// Buscar usuario en la base de datos
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

// Comprobar si existe el usuario
if ($resultado->num_rows === 0) {
    $consulta->close();
    mostrarError("El correo o la contraseña son incorrectos.");
}

$usuario = $resultado->fetch_assoc();
$consulta->close();

// Verificar contraseña cifrada
if (!password_verify($password, $usuario["password"])) {
    mostrarError("El correo o la contraseña son incorrectos.");
}

// Regenerar identificador de sesión
session_regenerate_id(true);

// Guardar información del usuario en la sesión
$_SESSION["id"] = $usuario["id"];
$_SESSION["nombre"] = $usuario["nombre"];
$_SESSION["apellido"] = $usuario["apellido"];
$_SESSION["correo"] = $usuario["correo"];
$_SESSION["rol"] = $usuario["rol"];

// Cerrar conexión
$conexion->close();

// Redirigir al panel
header("Location: panel.php");
exit;

?>
