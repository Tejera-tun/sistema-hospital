
<?php
session_start();

require_once __DIR__ . "/../conexion.php";

if (!isset($_SESSION["id"])) {
    header("Location: ../login.php");
    exit;
}

$esAdministrador = $_SESSION["rol"] === "Administrador";

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

    <title>Seguimientos médicos | FLEM</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f3f7f5;
            font-family: Arial, sans-serif;
            color: #26352f;
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
            max-width: 1150px;
            margin: 45px auto;
            padding: 0 18px;
        }

        .encabezado {
            background: linear-gradient(120deg, #075b46, #087b5b);
            color: white;
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 30px;
            box-shadow: 0 5px 14px rgba(0,0,0,0.10);
        }

        .encabezado small {
            color: #e8b832;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .encabezado h1 {
            font-weight: bold;
            margin-top: 8px;
        }

        .tarjeta {
            background: white;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.06);
        }

        .btn-flem {
            background: #075b46;
            color: white;
            border: none;
            border-radius: 7px;
            padding: 9px 16px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-flem:hover {
            background: #064735;
            color: white;
        }

        .btn-volver {
            background: white;
            color: #075b46;
            border: none;
            border-radius: 7px;
            padding: 9px 16px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-volver:hover {
            background: #e8f1ed;
            color: #075b46;
        }

        .table thead th {
            background: #075b46;
            color: white;
            padding: 14px;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 14px;
            vertical-align: middle;
        }

        .estado {
            background: #e8b832;
            color: #26352f;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .titulo-seccion {
            font-weight: bold;
            margin-bottom: 20px;
        }

        @media (max-width: 600px) {
            .encabezado {
                padding: 22px;
            }

            .acciones {
                display: flex;
                flex-direction: column;
                gap: 10px;
                margin-top: 20px;
            }
        }
    </style>
</head>

<body>

<nav class="navbar-flem">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="../panel.php" class="logo-flem">
            ⚕ FLEM
        </a>

        <a href="../panel.php" class="btn-volver">
            Volver al panel
        </a>
    </div>
</nav>

<div class="contenedor">

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
                <div class="acciones">
                    <a href="crearTratamiento.php" class="btn btn-warning fw-bold">
                        + Nuevo seguimiento
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="tarjeta">

        <h4 class="titulo-seccion">Historial de seguimientos</h4>

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
                            <?php endif; ?>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while ($fila = $resultados->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($fila["fecha"]) ?>
                                </td>

                                <td class="fw-semibold">
                                    <?= htmlspecialchars($fila["titulo"]) ?>
                                </td>

                                <td>
                                    <?= nl2br(htmlspecialchars($fila["descripcion"])) ?>
                                </td>

                                <td>
                                    <span class="estado">
                                        <?= htmlspecialchars($fila["estado"]) ?>
                                    </span>
                                </td>

                                <?php if ($esAdministrador): ?>
                                    <td>
                                        <?= htmlspecialchars($fila["nombre"] . " " . $fila["apellido"]) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($fila["cedula"]) ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>

                </table>
            </div>

        <?php else: ?>

            <div class="text-center py-5">
                <div style="font-size: 45px; color: #c49b29;">✚</div>
                <h5 class="mt-3">No hay seguimientos registrados</h5>
                <p class="text-muted mb-0">
                    Cuando haya nuevas actualizaciones médicas, aparecerán aquí.
                </p>
            </div>

        <?php endif; ?>

    </div>

</div>

</body>
</html>

<?php
$consulta->close();
$conexion->close();
?>
