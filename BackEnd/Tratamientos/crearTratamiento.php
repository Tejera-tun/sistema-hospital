
<?php

session_start();

require_once __DIR__ . "/../conexion.php";

if (
    !isset($_SESSION["id"]) ||
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "Administrador"
) {
    header("Location: ../login.php");
    exit;
}

function limpiar($dato) {
    return htmlspecialchars((string)$dato, ENT_QUOTES, "UTF-8");
}

$mensaje = "";
$error = "";

// ===================================
// OBTENER PACIENTES
// ===================================

$consultaPacientes = $conexion->query(
    "SELECT id, nombre, apellido, cedula
     FROM usuarios
     WHERE rol = 'Paciente'
     ORDER BY nombre, apellido"
);

// ===================================
// REGISTRAR SEGUIMIENTO
// ===================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $usuario_id = filter_input(INPUT_POST, "usuario_id", FILTER_VALIDATE_INT);
    $titulo = trim($_POST["titulo"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");
    $fecha = $_POST["fecha"] ?? "";
    $estado = $_POST["estado"] ?? "Pendiente";

    $estadosPermitidos = [
        "Pendiente",
        "En proceso",
        "Finalizado"
    ];

    if (
        !$usuario_id ||
        $titulo === "" ||
        $descripcion === "" ||
        $fecha === ""
    ) {

        $error = "Completá todos los campos.";

    } elseif (!in_array($estado, $estadosPermitidos, true)) {

        $error = "El estado seleccionado no es válido.";

    } elseif (
        !DateTime::createFromFormat("Y-m-d", $fecha) ||
        DateTime::createFromFormat("Y-m-d", $fecha)->format("Y-m-d") !== $fecha
    ) {

        $error = "La fecha ingresada no es válida.";

    } else {

        // VERIFICAR QUE SEA UN PACIENTE

        $verificar = $conexion->prepare(
            "SELECT id
             FROM usuarios
             WHERE id = ? AND rol = 'Paciente'"
        );

        $verificar->bind_param("i", $usuario_id);
        $verificar->execute();

        $resultado = $verificar->get_result();

        if ($resultado->num_rows === 0) {

            $error = "El paciente seleccionado no existe.";

        } else {

            $creado_por = (int)$_SESSION["id"];

            // INSERTAR SEGUIMIENTO

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

                // LIMPIAR LOS CAMPOS DESPUÉS DEL REGISTRO
                $_POST = [];

            } else {

                $error = "No se pudo registrar el seguimiento.";

            }

            $insertar->close();

        }

        $verificar->close();

    }

}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Crear seguimiento | FLEM × Hospital de Clínicas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>

        :root {
            --verde: #075c45;
            --verde-oscuro: #064936;
            --dorado: #d4af37;
            --fondo: #f4f8f6;
        }

        body {
            background: var(--fondo);
            color: #26352f;
            font-family: Arial, sans-serif;
        }

        /* ENCABEZADO */

        .navbar-flem {
            background: var(--verde);
            padding: 14px 0;
        }

        .brand-flem {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .brand-name {
            color: var(--dorado);
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 2px;
        }

        .brand-x {
            color: var(--dorado);
            font-size: 27px;
            font-weight: bold;
        }

        .logo-hospital {
            width: 150px;
            height: 48px;
            object-fit: contain;
            background: transparent;
            padding: 0;
        }

        /* CONTENIDO */

        .contenedor {
            max-width: 850px;
            margin: 45px auto;
            padding: 0 18px;
        }

        .encabezado {
            background: linear-gradient(120deg, var(--verde), #0a7658);
            color: white;
            padding: 30px;
            border-radius: 18px;
            margin-bottom: 25px;
        }

        .encabezado small {
            color: var(--dorado);
            font-weight: bold;
            letter-spacing: 1px;
        }

        .formulario-card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 4px 15px rgba(0,0,0,.06);
        }

        .titulo-formulario {
            color: var(--verde);
            font-weight: bold;
        }

        /* CAMPOS */

        .form-label {
            font-weight: 600;
            color: #344b40;
        }

        .form-control,
        .form-select {
            padding: 11px 13px;
            border-radius: 9px;
            border: 1px solid #dce5df;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--verde);
            box-shadow: 0 0 0 .2rem rgba(7,92,69,.12);
        }

        /* BOTONES */

        .btn-flem {
            background: var(--verde);
            color: white;
            border: none;
            border-radius: 9px;
            padding: 11px 20px;
        }

        .btn-flem:hover {
            background: var(--verde-oscuro);
            color: white;
        }

        .btn-dorado {
            background: var(--dorado);
            color: #26352f;
            border: none;
            border-radius: 9px;
            padding: 11px 20px;
            font-weight: bold;
        }

        .btn-dorado:hover {
            background: #c39d2e;
            color: #26352f;
        }

        .btn-volver {
            background: #e9efec;
            color: var(--verde);
            border: none;
            border-radius: 9px;
            padding: 11px 20px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-volver:hover {
            background: #dce9e2;
            color: var(--verde);
        }

        footer {
            color: #6c757d;
        }

        .footer-brand {
            color: var(--dorado);
            font-weight: 800;
            letter-spacing: 2px;
        }

        @media(max-width: 500px) {

            .brand-flem {
                gap: 7px;
            }

            .brand-name {
                font-size: 18px;
            }

            .brand-x {
                font-size: 21px;
            }

            .logo-hospital {
                width: 105px;
                height: 40px;
            }

            .encabezado {
                padding: 22px;
            }

        }

    </style>

</head>

<body>

    <!-- ENCABEZADO FLEM × HOSPITAL DE CLÍNICAS -->

    <nav class="navbar navbar-flem">

        <div class="container">

            <a href="../panel.php" class="brand-flem">

                <span class="brand-name">FLEM</span>

                <span class="brand-x">×</span>

                <img
                    src="../../FrontEnd/img/logo-hospital-clinicas.png"
                    alt="Hospital de Clínicas"
                    class="logo-hospital"
                >

            </a>

            <a href="../logout.php" class="btn btn-light">
                Cerrar sesión
            </a>

        </div>

    </nav>


    <main class="contenedor">

        <!-- PRESENTACIÓN -->

        <div class="encabezado">

            <small>SISTEMA HOSPITALARIO</small>

            <h1 class="fw-bold mt-2">
                Registrar seguimiento médico
            </h1>

            <p class="mb-0">
                Registrá y actualizá la información médica de los pacientes.
            </p>

        </div>


        <!-- FORMULARIO -->

        <div class="card formulario-card">

            <div class="card-body p-4 p-md-5">

                <h4 class="titulo-formulario mb-4">
                    Información del seguimiento
                </h4>


                <!-- MENSAJES -->

                <?php if ($mensaje !== ""): ?>

                    <div class="alert alert-success">
                        <?= limpiar($mensaje) ?>
                    </div>

                <?php endif; ?>


                <?php if ($error !== ""): ?>

                    <div class="alert alert-danger">
                        <?= limpiar($error) ?>
                    </div>

                <?php endif; ?>


                <form method="POST" action="crearTratamiento.php">

                    <!-- PACIENTE -->

                    <div class="mb-3">

                        <label class="form-label">
                            Seleccionar paciente
                        </label>

                        <select name="usuario_id"
                                class="form-select"
                                required>

                            <option value="">
                                Elegir paciente
                            </option>

                            <?php while ($paciente = $consultaPacientes->fetch_assoc()): ?>

                                <option
                                    value="<?= (int)$paciente["id"] ?>"
                                    <?= (($_POST["usuario_id"] ?? "") == $paciente["id"]) ? "selected" : "" ?>
                                >

                                    <?= limpiar(
                                        $paciente["nombre"] . " " .
                                        $paciente["apellido"] . " - CI: " .
                                        $paciente["cedula"]
                                    ) ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>


                    <!-- TÍTULO -->

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
                            placeholder="Ejemplo: Control médico"
                            value="<?= limpiar($_POST["titulo"] ?? "") ?>"
                        >

                    </div>


                    <!-- DESCRIPCIÓN -->

                    <div class="mb-3">

                        <label class="form-label">
                            Descripción
                        </label>

                        <textarea
                            name="descripcion"
                            class="form-control"
                            rows="5"
                            required
                            placeholder="Ingresar los detalles del seguimiento..."
                        ><?= limpiar($_POST["descripcion"] ?? "") ?></textarea>

                    </div>


                    <!-- FECHA -->

                    <div class="mb-3">

                        <label class="form-label">
                            Fecha
                        </label>

                        <input
                            type="date"
                            name="fecha"
                            class="form-control"
                            required
                            value="<?= limpiar($_POST["fecha"] ?? date("Y-m-d")) ?>"
                        >

                    </div>


                    <!-- ESTADO -->

                    <div class="mb-4">

                        <label class="form-label">
                            Estado del seguimiento
                        </label>

                        <select name="estado"
                                class="form-select"
                                required>

                            <option value="Pendiente"
                                <?= ($_POST["estado"] ?? "Pendiente") === "Pendiente" ? "selected" : "" ?>>
                                Pendiente
                            </option>

                            <option value="En proceso"
                                <?= ($_POST["estado"] ?? "") === "En proceso" ? "selected" : "" ?>>
                                En proceso
                            </option>

                            <option value="Finalizado"
                                <?= ($_POST["estado"] ?? "") === "Finalizado" ? "selected" : "" ?>>
                                Finalizado
                            </option>

                        </select>

                    </div>


                    <!-- BOTONES -->

                    <div class="d-flex flex-wrap gap-2">

                        <button type="submit" class="btn btn-dorado">
                            Registrar seguimiento
                        </button>

                        <a href="listarSeguimiento.php" class="btn-volver">
                            Volver a seguimientos
                        </a>

                    </div>

                </form>

            </div>

        </div>

    </main>


    <!-- PIE DE PÁGINA -->

    <footer class="text-center py-4">

        <div class="footer-brand">
            FLEM × Hospital de Clínicas
        </div>

        <div>Sistema Hospitalario</div>

    </footer>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>

<?php

$consultaPacientes->free();
$conexion->close();

?>
