
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
    <title>Seguimientos médicos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Seguimientos médicos</h2>

        <div>
            <?php if ($esAdministrador): ?>
                <a href="crearTratamiento.php" class="btn btn-primary">
                    Nuevo seguimiento
                </a>
            <?php endif; ?>

            <a href="../panel.php" class="btn btn-secondary">
                Volver al panel
            </a>
        </div>
    </div>

    <?php if ($resultados->num_rows > 0): ?>

        <div class="table-responsive">
            <table class="table table-bordered table-striped bg-white">

                <thead class="table-dark">
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
                            <td><?= htmlspecialchars($fila["fecha"]) ?></td>

                            <td><?= htmlspecialchars($fila["titulo"]) ?></td>

                            <td><?= nl2br(htmlspecialchars($fila["descripcion"])) ?></td>

                            <td>
                                <span class="badge bg-info text-dark">
                                    <?= htmlspecialchars($fila["estado"]) ?>
                                </span>
                            </td>

                            <?php if ($esAdministrador): ?>
                                <td>
                                    <?= htmlspecialchars($fila["nombre"] . " " . $fila["apellido"]) ?>
                                </td>

                                <td><?= htmlspecialchars($fila["cedula"]) ?></td>
                            <?php endif; ?>

                        </tr>

                    <?php endwhile; ?>

                </tbody>

            </table>
        </div>

    <?php else: ?>

        <div class="alert alert-info">
            Todavía no hay seguimientos registrados.
        </div>

    <?php endif; ?>

</div>

</body>
</html>
