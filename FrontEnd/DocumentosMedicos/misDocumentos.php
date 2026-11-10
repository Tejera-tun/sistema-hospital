
<?php
session_start();

require_once __DIR__ . "/../../BackEnd/conexion.php";

if (!isset($_SESSION["id"]) || $_SESSION["rol"] !== "Paciente") {
    header("Location: ../../BackEnd/login.php");
    exit;
}

$id_paciente = (int) $_SESSION["id"];

$sql = "SELECT id_documento, nombre_documento, categoria, descripcion,
               tipo_documento, fecha_subida
        FROM documentos_medicos
        WHERE id_paciente = ?
        ORDER BY fecha_subida DESC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_paciente);
$stmt->execute();

$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis documentos médicos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Mis documentos médicos</h2>
        <a href="../../BackEnd/panel.php" class="btn btn-secondary">
            Volver al panel
        </a>
    </div>

    <?php if ($resultado->num_rows > 0): ?>

        <div class="table-responsive">
            <table class="table table-bordered table-striped bg-white">
                <thead class="table-dark">
                    <tr>
                        <th>Documento</th>
                        <th>Categoría</th>
                        <th>Descripción</th>
                        <th>Tipo</th>
                        <th>Fecha</th>
                        <th>Acción</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while ($documento = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($documento["nombre_documento"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($documento["categoria"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($documento["descripcion"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($documento["tipo_documento"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($documento["fecha_subida"]) ?>
                            </td>

                            <td>
                                <a
                                    href="../../BackEnd/DocumentosMedicos/descargarDocumento.php?id=<?= (int) $documento["id_documento"] ?>"
                                    class="btn btn-primary btn-sm">
                                    Descargar
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>

        <div class="alert alert-info">
            Todavía no tenés documentos médicos cargados.
        </div>

    <?php endif; ?>

</div>

</body>
</html>

<?php
$stmt->close();
$conexion->close();
?>
