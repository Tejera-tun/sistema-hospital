
<?php
session_start();

require_once __DIR__ . "/../conexion.php";

if (!isset($_SESSION["id"]) || $_SESSION["rol"] !== "Administrador") {
    header("Location: ../login.php");
    exit;
}

$mensaje = "";
$error = "";

// Obtener pacientes
$consultaPacientes = $conexion->query(
    "SELECT id, nombre, apellido, cedula
     FROM usuarios
     WHERE rol = 'Paciente'
     ORDER BY nombre, apellido"
);

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $usuario_id = filter_input(INPUT_POST, "usuario_id", FILTER_VALIDATE_INT);
    $titulo = trim($_POST["titulo"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");
    $fecha = $_POST["fecha"] ?? "";
    $estado = $_POST["estado"] ?? "Pendiente";

    $estadosPermitidos = ["Pendiente", "En proceso", "Finalizado"];

    if (!$usuario_id || $titulo === "" || $descripcion === "" || $fecha === "") {
        $error = "Completá todos los campos.";
    } elseif (!in_array($estado, $estadosPermitidos, true)) {
        $error = "El estado seleccionado no es válido.";
    } else {

        // Verificar que el usuario seleccionado sea paciente
        $verificar = $conexion->prepare(
            "SELECT id FROM usuarios WHERE id = ? AND rol = 'Paciente'"
        );

        $verificar->bind_param("i", $usuario_id);
        $verificar->execute();
        $resultado = $verificar->get_result();

        if ($resultado->num_rows === 0) {
            $error = "El paciente seleccionado no existe.";
        } else {

            $creado_por = $_SESSION["id"];

            $insertar = $conexion->prepare(
                "INSERT INTO seguimientos
                (usuario_id, titulo, descripcion, fecha, estado, creado_por)
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            $insertar->bind_param(
                "issssi",
                $usuario_id,
                $titulo,
                $descripcion,
                $fecha,
                $estado,
                $creado_por
            );

            if ($insertar->execute()) {
                $mensaje = "Seguimiento registrado correctamente.";
            } else {
                $error = "No se pudo registrar el seguimiento.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Crear seguimiento | FLEM</title>

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
            color: #26352f;
            font-family: Arial, sans-serif;
        }

        .navbar-flem {
            background: var(--verde);
            padding: 14px 0;
        }

        .marca {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .brand-name {
            color: var(--dorado);
            font-size: 25px;
            font-weight: bold;
            letter-spacing: 2px;
            white-space: nowrap;
        }

        .brand-x {
            color: var(--dorado);
        }

        .logo-hospital {
            width: 115px;
            height: 55px;
            object-fit: contain;
            background: transparent;
            border: none;
            padding: 0;
        }

        .contenedor {
            max-width: 850px;
            margin: 45px auto;
            padding: 0 18px;
        }

        .encabezado {
            background: linear-gradient(120deg, var(--verde), #087b5b);
            color: white;
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 25px;
            box-shadow: 0 5px 14px rgba(0,0,0,0.10);
        }

        .encabezado small {
            color: var(--dorado);
            font-weight: bold;
            letter-spacing: 1px;
        }

        .encabezado h1 {
            font-weight: bold;
            margin-top: 8px;
        }

        .tarjeta {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.06);
        }

        .titulo-seccion {
            color: var(--verde);
            font-weight: bold;
        }

        .form-label {
            font-weight: 600;
            color: #34483e;
        }

        .form-control,
        .form-select {
            padding: 11px 13px;
            border-radius: 9px;
            border: 1px solid #d9e3de;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--verde);
            box-shadow: 0 0 0 .2rem rgba(7,92,69,.12);
        }

        .btn-flem {
            background: var(--verde);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
        }

        .btn-flem:hover {
            background: var(--verde-oscuro);
            color: white;
        }

        .btn-volver {
            background: #e9efec;
            color: var(--verde);
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-volver:hover {
            background: #dce8e1;
            color: var(--verde);
        }

        .footer-flem {
            background: white;
            border-top: 3px solid var(--dorado);
            margin-top: 55px;
            padding: 30px 18px;
        }

        .footer-title {
            color: var(--verde);
            font-size: 19px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .footer-description {
            max-width: 800px;
            margin: auto;
            color: #66736c;
            font-size: 14px;
            line-height: 1.7;
        }

        .footer-copy {
            color: #89938e;
            font-size: 12px;
            margin-top: 15px;
        }

        @media (max-width: 576px) {

            .brand-name {
                font-size: 19px;
            }

            .logo-hospital {
                width: 85px;
                height: 45px;
            }

            .tarjeta {
                padding: 20px;
            }

            .encabezado {
                padding: 23px;
            }

        }

    </style>

</head>

<body>

    <!-- ENCABEZADO -->

    <nav class="navbar navbar-flem">

        <div class="container d-flex justify-content-between align-items-center">

            <a href="../panel.php" class="marca">

                <span class="brand-name">
                    FLEM <span class="brand-x">×</span>
                </span>

                <img
                    src="../../FrontEnd/img/logo-hospital-clinicas.png"
                    alt="Logo Hospital de Clínicas"
                    class="logo-hospital"
                >

            </a>

            <a href="../logout.php" class="btn btn-light">
                Cerrar sesión
            </a>

        </div>

    </nav>

    <main class="contenedor">

        <!-- TÍTULO -->

        <div class="encabezado">

            <small>SISTEMA HOSPITALARIO</small>

            <h1>Registrar seguimiento médico</h1>

            <p class="mb-0">
                Ingresá la información correspondiente al seguimiento del paciente.
            </p>

        </div>

        <!-- FORMULARIO -->

        <div class="tarjeta">

            <h4 class="titulo-seccion mb-4">
                Información del seguimiento
            </h4>

            <?php if ($mensaje): ?>

                <div class="alert alert-success">
                    <?= htmlspecialchars($mensaje) ?>
                </div>

            <?php endif; ?>

            <?php if ($error): ?>

                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>

            <?php endif; ?>

            <form method="POST">

                <div class="mb-3">

                    <label class="form-label">
                        Seleccionar paciente
                    </label>

                    <select name="usuario_id" class="form-select" required>

                        <option value="">
                            Elegir paciente
                        </option>

                        <?php while ($paciente = $consultaPacientes->fetch_assoc()): ?>

                            <option value="<?= $paciente["id"] ?>"
                                <?= (($_POST["usuario_id"] ?? "") == $paciente["id"]) ? "selected" : "" ?>>

                                <?= htmlspecialchars(
                                    $paciente["nombre"] . " " .
                                    $paciente["apellido"] . " - CI: " .
                                    $paciente["cedula"]
                                ) ?>

                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Título
                    </label>

                    <input
                        type="text"
                        name="titulo"
                        class="form-control"
                        maxlength="150"
                        required
                        value="<?= htmlspecialchars($_POST["titulo"] ?? "") ?>"
                    >

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Descripción
                    </label>

                    <textarea
                        name="descripcion"
                        class="form-control"
                        rows="4"
                        required
                    ><?= htmlspecialchars($_POST["descripcion"] ?? "") ?></textarea>

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Fecha
                    </label>

                    <input
                        type="date"
                        name="fecha"
                        class="form-control"
                        required
                        value="<?= htmlspecialchars($_POST["fecha"] ?? date("Y-m-d")) ?>"
                    >

                </div>

                <div class="mb-4">

                    <label class="form-label">
                        Estado
                    </label>

                    <select name="estado" class="form-select" required>

                        <option value="Pendiente">Pendiente</option>

                        <option value="En proceso">En proceso</option>

                        <option value="Finalizado">Finalizado</option>

                    </select>

                </div>

                <div class="d-flex flex-wrap gap-2">

                    <button type="submit" class="btn btn-flem">
                        Registrar seguimiento
                    </button>

                    <a href="listarSeguimiento.php" class="btn-volver">
                        Volver
                    </a>

                </div>

            </form>

        </div>

    </main>

    <!-- PIE DE PÁGINA -->

    <footer class="footer-flem text-center">

        <div class="footer-title">
            FLEM × Hospital de Clínicas
        </div>

        <p class="footer-description">
            Somos una empresa especializada en la digitalización de servicios mediante el desarrollo de páginas web y soluciones tecnológicas. Nuestro objetivo es optimizar procesos, facilitar el acceso a la información y mejorar la gestión de las organizaciones mediante herramientas modernas, accesibles y eficientes.
        </p>

        <div class="footer-copy">
            © <?= date("Y"); ?> FLEM. Todos los derechos reservados.
        </div>

    </footer>

</body>
</html>
