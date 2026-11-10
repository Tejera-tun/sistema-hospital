
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

    <title>Mis documentos médicos | FLEM</title>

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

        .btn-descargar {
            background: #075b46;
            color: white;
            border: none;
            border-radius: 7px;
            padding: 8px 14px;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }

        .btn-descargar:hover {
            background: #064735;
            color: white;
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

        .tipo-documento {
            background: #f8edc9;
            color: #725814;
            padding: 6px 11px;
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
        }
    </style>
</head>

<body>

<nav class="navbar-flem">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="../../BackEnd/panel.php" class="logo-flem">
            ⚕ FLEM
        </a>

        <a href="../../BackEnd/panel.php" class="btn-volver">
            Volver al panel
        </a>
    </div>
</nav>

<div class="contenedor">

    <div class="encabezado">
        <small>SISTEMA HOSPITALARIO</small>

        <h1>Mis documentos médicos</h1>

        <p class="mb-0">
            Consultá y descargá tus documentos médicos personales.
        </p>
    </div>

    <div class="tarjeta">

        <h4 class="titulo-seccion">Mis documentos</h4>

        <?php if ($resultado->num_rows > 0): ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">

                    <thead>
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
                                <td class="fw-semibold">
                                    <?= htmlspecialchars($documento["nombre_documento"]) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($documento["categoria"]) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($documento["descripcion"] ?? "") ?>
                                </td>

                                <td>
                                    <span class="tipo-documento">
                                        <?= htmlspecialchars($documento["tipo_documento"]) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= htmlspecialchars($documento["fecha_subida"]) ?>
                                </td>

                                <td>
                                    <a
                                        href="../../BackEnd/DocumentosMedicos/descargarDocumento.php?id=<?= (int) $documento["id_documento"] ?>"
                                        class="btn-descargar">
                                        ↓ Descargar
                                    </a>
                                </td>
                            </tr>

                        <?php endwhile; ?>
                    </tbody>

                </table>
            </div>

        <?php else: ?>

            <div class="text-center py-5">
                <div style="font-size: 45px; color: #c49b29;">▤</div>

                <h5 class="mt-3">Todavía no tenés documentos médicos</h5>

                <p class="text-muted mb-0">
                    Cuando el hospital cargue tus documentos, aparecerán aquí.
                </p>
            </div>

        <?php endif; ?>

    </div>

</div>

</body>
</html>

<?php
$stmt->close();
$conexion->close();
?>
