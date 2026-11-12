
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

        .btn-flem {
            background: var(--verde);
            color: white;
            border: none;
            border-radius: 7px;
            padding: 9px 16px;
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
            border-radius: 7px;
            padding: 9px 16px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-volver:hover {
            background: #e8f1ed;
            color: var(--verde);
        }

        .btn-nuevo {
            background: var(--dorado);
            color: #26352f;
            border: none;
        }

        .btn-nuevo:hover {
            background: #c19d2e;
            color: #26352f;
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

        .estado {
            background: #f5e9bd;
            color: #604b0b;
            padding: 6px 12px;
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

            .acciones {
                display: flex;
                flex-direction: column;
                gap: 10px;
                margin-top: 20px;
            }

            .brand-name {
                font-size: 19px;
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

        <a href="../panel.php" class="marca">
            <span class="brand-name">FLEM <span class="brand-x">×</span></span>

            <img
                src="../../FrontEnd/img/logo-hospital-clinicas.png"
                alt="Logo Hospital de Clínicas"
                class="logo-hospital"
            >
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
                    <a href="crearTratamiento.php" class="btn btn-nuevo fw-bold">
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
$consulta->close();
$conexion->close();
?>
