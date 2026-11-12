
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

        :root {
            --verde: #075c45;
            --verde-oscuro: #064735;
            --dorado: #d4af37;
            --fondo: #f4f8f6;
        }

        body {
            background: var(--fondo);
            font-family: Arial, sans-serif;
            color: #26352f;
            min-height: 100vh;
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
            font-size: 27px;
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
            max-width: 1150px;
            margin: 45px auto;
            padding: 0 18px;
        }

        .encabezado {
            background: linear-gradient(120deg, var(--verde), #087b5b);
            color: white;
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 30px;
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
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.06);
        }

        .btn-volver {
            background: white;
            color: var(--verde);
            border: none;
            border-radius: 7px;
            padding: 9px 16px;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
        }

        .btn-volver:hover {
            background: #e8f1ed;
            color: var(--verde);
        }

        .btn-descargar {
            background: var(--verde);
            color: white;
            border: none;
            border-radius: 7px;
            padding: 8px 14px;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            white-space: nowrap;
        }

        .btn-descargar:hover {
            background: var(--verde-oscuro);
            color: white;
        }

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

        .tipo-documento {
            background: #f8edc9;
            color: #725814;
            padding: 6px 11px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }

        .titulo-seccion {
            color: var(--verde);
            font-weight: bold;
            margin-bottom: 20px;
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

        @media (max-width: 600px) {

            .encabezado {
                padding: 22px;
            }

            .brand-name {
                font-size: 20px;
            }

            .logo-hospital {
                width: 85px;
                height: 45px;
            }

            .marca {
                gap: 7px;
            }

        }

    </style>

</head>

<body>

    <!-- ENCABEZADO -->

    <nav class="navbar navbar-flem">

        <div class="container d-flex justify-content-between align-items-center">

            <a href="../../BackEnd/panel.php" class="marca">

                <span class="brand-name">
                    FLEM <span class="brand-x">×</span>
                </span>

                <img
                    src="../img/logo-hospital-clinicas.png"
                    alt="Logo Hospital de Clínicas"
                    class="logo-hospital"
                >

            </a>

            <a href="../../BackEnd/panel.php" class="btn-volver">
                Volver al panel
            </a>

        </div>

    </nav>


    <!-- CONTENIDO PRINCIPAL -->

    <main class="contenedor">

        <div class="encabezado">

            <small>SISTEMA HOSPITALARIO</small>

            <h1>Mis documentos médicos</h1>

            <p class="mb-0">
                Consultá y descargá tus documentos médicos personales.
            </p>

        </div>


        <!-- TABLA DE DOCUMENTOS -->

        <div class="tarjeta">

            <h4 class="titulo-seccion">
                Mis documentos
            </h4>

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

                    <div style="font-size: 45px; color: #c49b29;">
                        ▤
                    </div>

                    <h5 class="mt-3">
                        Todavía no tenés documentos médicos
                    </h5>

                    <p class="text-muted mb-0">
                        Cuando el hospital cargue tus documentos, aparecerán aquí.
                    </p>

                </div>

            <?php endif; ?>

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

<?php
$stmt->close();
$conexion->close();
?>
