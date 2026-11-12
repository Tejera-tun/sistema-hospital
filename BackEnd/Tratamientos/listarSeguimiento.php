
<?php

session_start();

require_once __DIR__ . "/../conexion.php";

// ===================================
// VERIFICAR SESIÓN Y PERMISOS
// ===================================

if (
    !isset($_SESSION["id"]) ||
    !isset($_SESSION["rol"]) ||
    !in_array($_SESSION["rol"], ["Administrador", "Paciente"], true)
) {
    header("Location: ../login.php");
    exit;
}

$esAdministrador = $_SESSION["rol"] === "Administrador";

// TOKEN DE SEGURIDAD
if (empty($_SESSION["csrf"])) {
    $_SESSION["csrf"] = bin2hex(random_bytes(32));
}

function limpiar($dato) {
    return htmlspecialchars((string)$dato, ENT_QUOTES, "UTF-8");
}

// ===================================
// CONSULTAR SEGUIMIENTOS
// ===================================

if ($esAdministrador) {

    $consulta = $conexion->prepare(
        "SELECT s.*, u.nombre, u.apellido, u.cedula
         FROM seguimientos s
         INNER JOIN usuarios u ON s.usuario_id = u.id
         ORDER BY s.fecha DESC, s.id DESC"
    );

} else {

    $consulta = $conexion->prepare(
        "SELECT s.*, u.nombre, u.apellido, u.cedula
         FROM seguimientos s
         INNER JOIN usuarios u ON s.usuario_id = u.id
         WHERE s.usuario_id = ?
         ORDER BY s.fecha DESC, s.id DESC"
    );

    $consulta->bind_param("i", $_SESSION["id"]);
}

$consulta->execute();
$resultados = $consulta->get_result();

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Seguimientos médicos | FLEM × Hospital de Clínicas</title>

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
            font-family: Arial, sans-serif;
            color: #26352f;
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
            max-width: 1300px;
            margin: 45px auto;
            padding: 0 18px;
        }

        .encabezado {
            background: linear-gradient(120deg, var(--verde), #0a7658);
            color: white;
            padding: 35px;
            border-radius: 18px;
            margin-bottom: 30px;
            box-shadow: 0 5px 14px rgba(0,0,0,.08);
        }

        .encabezado small {
            color: var(--dorado);
            font-weight: bold;
            letter-spacing: 1px;
        }

        .encabezado h1 {
            font-weight: bold;
            margin-top: 10px;
        }

        .tarjeta {
            background: white;
            padding: 25px;
            border-radius: 18px;
            box-shadow: 0 3px 12px rgba(0,0,0,.06);
        }

        /* BOTONES */

        .btn-flem {
            background: var(--verde);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 17px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-flem:hover {
            background: var(--verde-oscuro);
            color: white;
        }

        .btn-volver {
            background: white;
            color: var(--verde);
            border: none;
            border-radius: 8px;
            padding: 10px 17px;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
        }

        .btn-volver:hover {
            background: #e8f1ed;
            color: var(--verde);
        }

        .btn-nuevo {
            background: var(--dorado);
            color: #26352f;
            border: none;
            border-radius: 8px;
            padding: 10px 17px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }

        .btn-nuevo:hover {
            background: #c39d2e;
            color: #26352f;
        }

        /* TABLA */

        .table thead th {
            background: var(--verde);
            color: white;
            padding: 14px;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 14px;
            vertical-align: middle;
        }

        .estado {
            background: #f4e6b7;
            color: #624d0c;
            padding: 7px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }

        .titulo-seccion {
            color: var(--verde);
            font-weight: bold;
            margin-bottom: 22px;
        }

        .sin-resultados {
            color: var(--verde);
            font-size: 45px;
        }

        .acciones {
            min-width: 150px;
        }

        footer {
            color: #6c757d;
        }

        .footer-brand {
            color: var(--dorado);
            font-weight: 800;
            letter-spacing: 2px;
        }

        @media (max-width: 600px) {

            .encabezado {
                padding: 23px;
            }

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

        }

    </style>

</head>

<body>

    <!-- ENCABEZADO FLEM × HOSPITAL DE CLÍNICAS -->

    <nav class="navbar navbar-flem">

        <div class="container d-flex justify-content-between align-items-center">

            <a href="../panel.php" class="brand-flem">

                <span class="brand-name">FLEM</span>

                <span class="brand-x">×</span>

                <img
                    src="../../FrontEnd/img/logo-hospital-clinicas.png"
                    alt="Hospital de Clínicas"
                    class="logo-hospital"
                >

            </a>

            <a href="../panel.php" class="btn-volver">
                Volver al panel
            </a>

        </div>

    </nav>


    <main class="contenedor">

        <!-- PRESENTACIÓN -->

        <div class="encabezado">

            <small>SISTEMA HOSPITALARIO</small>

            <div class="d-flex justify-content-between align-items-center flex-wrap">

                <div>

                    <h1>Seguimientos médicos</h1>

                    <p class="mb-0">
                        Consultá las actualizaciones y la evolución de los tratamientos médicos.
                    </p>

                </div>

                <?php if ($esAdministrador): ?>

                    <div class="mt-3 mt-md-0">

                        <a href="crearTratamiento.php" class="btn-nuevo">
                            + Nuevo seguimiento
                        </a>

                    </div>

                <?php endif; ?>

            </div>

        </div>


        <!-- MENSAJES DE OPERACIONES -->

        <?php if (isset($_GET["eliminado"])): ?>

            <div class="alert alert-success">
                Seguimiento eliminado correctamente.
            </div>

        <?php endif; ?>

        <?php if (isset($_GET["actualizado"])): ?>

            <div class="alert alert-success">
                Seguimiento actualizado correctamente.
            </div>

        <?php endif; ?>

        <?php if (isset($_GET["error"])): ?>

            <div class="alert alert-danger">
                No se pudo completar la operación solicitada.
            </div>

        <?php endif; ?>


        <!-- HISTORIAL -->

        <div class="tarjeta">

            <h4 class="titulo-seccion">
                Historial de seguimientos
            </h4>

            <?php if ($resultados->num_rows > 0): ?>

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead>

                            <tr>

                                <th>Fecha</th>

                                <th>Título</th>

                                <th>Descripción</th>

                                <th>Estado</th>

                                <?php if ($esAdministrador): ?>

                                    <th>Paciente</th>

                                    <th>Cédula</th>

                                    <th>Acciones</th>

                                <?php endif; ?>

                            </tr>

                        </thead>

                        <tbody>

                            <?php while ($fila = $resultados->fetch_assoc()): ?>

                                <tr>

                                    <td>
                                        <?= limpiar($fila["fecha"]) ?>
                                    </td>

                                    <td class="fw-semibold">
                                        <?= limpiar($fila["titulo"]) ?>
                                    </td>

                                    <td>
                                        <?= nl2br(limpiar($fila["descripcion"])) ?>
                                    </td>

                                    <td>

                                        <span class="estado">
                                            <?= limpiar($fila["estado"]) ?>
                                        </span>

                                    </td>

                                    <?php if ($esAdministrador): ?>

                                        <td>
                                            <?= limpiar($fila["nombre"] . " " . $fila["apellido"]) ?>
                                        </td>

                                        <td>
                                            <?= limpiar($fila["cedula"]) ?>
                                        </td>

                                        <!-- ACCIONES DEL ADMINISTRADOR -->

                                        <td class="acciones">

                                            <div class="d-flex gap-2 flex-wrap">

                                                <!-- EDITAR -->

                                                <a
                                                    href="actualizarSeguimiento.php?id=<?= (int)$fila["id"] ?>"
                                                    class="btn btn-warning btn-sm"
                                                >
                                                    Editar
                                                </a>


                                                <!-- ELIMINAR -->

                                                <form
                                                    method="POST"
                                                    action="eliminarSeguimiento.php"
                                                    onsubmit="return confirm('¿Seguro que querés eliminar este seguimiento?');"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="id"
                                                        value="<?= (int)$fila["id"] ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="csrf"
                                                        value="<?= limpiar($_SESSION["csrf"]) ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="btn btn-danger btn-sm"
                                                    >
                                                        Eliminar
                                                    </button>

                                                </form>

                                            </div>

                                        </td>

                                    <?php endif; ?>

                                </tr>

                            <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="text-center py-5">

                    <div class="sin-resultados">✚</div>

                    <h5 class="mt-3">
                        No hay seguimientos registrados
                    </h5>

                    <p class="text-muted mb-0">
                        Cuando haya nuevas actualizaciones médicas, aparecerán aquí.
                    </p>

                </div>

            <?php endif; ?>

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

$consulta->close();
$conexion->close();

?>
