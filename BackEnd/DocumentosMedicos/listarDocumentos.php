
<?php

session_start();

require_once __DIR__ . "/../conexion.php";

// Verificar que haya iniciado sesión
if (!isset($_SESSION["id"]) || !isset($_SESSION["rol"])) {
    die("Debes iniciar sesión.");
}

// Solo pacientes
if ($_SESSION["rol"] !== "Paciente") {
    die("Acceso denegado.");
}

$id_paciente = (int) $_SESSION["id"];

// Obtener solamente los documentos del paciente conectado
$consulta = $conexion->prepare(
    "SELECT id_documento, nombre_documento, categoria,
            descripcion, tipo_documento, fecha_subida
     FROM documentos_medicos
     WHERE id_paciente = ?
     ORDER BY fecha_subida DESC"
);

if (!$consulta) {
    die("Error en la consulta: " . $conexion->error);
}

$consulta->bind_param("i", $id_paciente);
$consulta->execute();

$resultado = $consulta->get_result();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mis documentos | FLEM</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-5">

    <div class="card shadow-sm border-0">
        <div class="card-header bg-success text-white p-4">
            <h3 class="mb-1">Mis documentos médicos</h3>
            <p class="mb-0">Consultá tus documentos registrados en FLEM.</p>
        </div>

        <div class="card-body p-4">

            <?php if ($resultado->num_rows > 0) { ?>

                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">

                        <thead>
                            <tr>
                                <th>Documento</th>
                                <th>Categoría</th>
                                <th>Descripción</th>
                                <th>Fecha</th>
                                <th>Archivo</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php while ($documento = $resultado->fetch_assoc()) { ?>

                            <tr>
                                <td>
                                    <?= htmlspecialchars($documento["nombre_documento"], ENT_QUOTES, "UTF-8") ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($documento["categoria"], ENT_QUOTES, "UTF-8") ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($documento["descripcion"] ?? "", ENT_QUOTES, "UTF-8") ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($documento["fecha_subida"], ENT_QUOTES, "UTF-8") ?>
                                </td>

                                <td>
                                    <a class="btn btn-success btn-sm"
                                       href="../../BackEnd/DocumentosMedicos/descargarDocumento.php?id=<?= (int)$documento["id_documento"] ?>">
                                        Descargar
                                    </a>
                                </td>
                            </tr>

                        <?php } ?>

                        </tbody>
                    </table>
                </div>

            <?php } else { ?>

                <div class="alert alert-info mb-0">
                    Todavía no tenés documentos médicos registrados.
                </div>

            <?php } ?>

            <div class="mt-4">
                <a href="../BackEnd/panel.php" class="btn btn-secondary">
                    Volver al panel
                </a>
            </div>

        </div>
    </div>

</div>

</body>
</html>

<?php
$consulta->close();
$conexion->close();
?>
