
<?php

session_start();

require_once __DIR__ . "/../conexion.php";

// ===================================
// VERIFICAR SESIÓN
// ===================================

if (!isset($_SESSION["id"]) || !isset($_SESSION["rol"])) {
    header("Location: ../login.php");
    exit;
}

// SOLO PACIENTES
if ($_SESSION["rol"] !== "Paciente") {
    header("Location: ../panel.php");
    exit;
}

$id_paciente = (int)$_SESSION["id"];

// ===================================
// CONSULTAR DOCUMENTOS DEL PACIENTE
// ===================================

$consulta = $conexion->prepare(
    "SELECT id_documento, nombre_documento, categoria,
            descripcion, tipo_documento, fecha_subida
     FROM documentos_medicos
     WHERE id_paciente = ?
     ORDER BY fecha_subida DESC"
);

if (!$consulta) {
    die("No se pudo preparar la consulta.");
}

$consulta->bind_param("i", $id_paciente);
$consulta->execute();

$resultado = $consulta->get_result();

function limpiar($dato) {
    return htmlspecialchars((string)$dato, ENT_QUOTES, "UTF-8");
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Mis documentos | FLEM × Hospital de Clínicas</title>

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
            max-width: 1200px;
            margin: 45px auto;
            padding: 0 18px;
        }

        .encabezado {
            background: linear-gradient(120deg, var(--verde), #0a7658);
            color: white;
            padding: 35px;
            border-radius: 18px;
            margin-bottom: 30px;
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

        .titulo-seccion {
            color: var(--verde);
            font-weight: bold;
            margin-bottom: 22px;
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

        /* BOTONES */

        .btn-flem {
            background: var(--verde);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 9px 15px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-flem:hover {
            background: var(--verde-oscuro);
            color: white;
        }

        .btn-volver {
            background: #e9efec;
            color: var(--verde);
            border: none;
            border-radius: 8px;
            padding: 10px 17px;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
        }

        .btn-volver:hover {
            background: #dce9e2;
            color: var(--verde);
        }

        .categoria {
            background: #f4e6b7;
            color: #624d0c;
            padding: 7px 11px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }

        .sin-documentos {
            color: var(--verde);
            font-size: 45px;
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
                padding: 23px;
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

            <a href="../logout.php" class="btn btn-light">
                Cerrar sesión
            </a>

        </div>

    </nav>


    <main class="contenedor">

        <!-- PRESENTACIÓN -->

        <div class="encabezado">

            <small>SISTEMA HOSPITALARIO</small>

            <h1>Mis documentos médicos</h1>

            <p class="mb-0">
                Consultá y descargá tus documentos médicos registrados en FLEM.
            </p>

        </div>


        <!-- DOCUMENTOS -->

        <div class="tarjeta">

            <h4 class="titulo-seccion">
                Documentación personal
            </h4>

            <?php if ($resultado->num_rows > 0): ?>

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead>

                            <tr>

                                <th>Documento</th>

                                <th>Categoría</th>

                                <th>Descripción</th>

                                <th>Fecha</th>

                                <th>Formato</th>

                                <th>Archivo</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php while ($documento = $resultado->fetch_assoc()): ?>

                                <tr>

                                    <td class="fw-semibold">
                                        <?= limpiar($documento["nombre_documento"]) ?>
                                    </td>

                                    <td>

                                        <span class="categoria">
                                            <?= limpiar($documento["categoria"]) ?>
                                        </span>

                                    </td>

                                    <td>
                                        <?= limpiar($documento["descripcion"] ?? "") ?>
                                    </td>

                                    <td>
                                        <?= limpiar($documento["fecha_subida"]) ?>
                                    </td>

                                    <td>
                                        <?= limpiar($documento["tipo_documento"]) ?>
                                    </td>

                                    <td>

                                        <a
                                            class="btn btn-flem btn-sm"
                                            href="../../BackEnd/DocumentosMedicos/descargarDocumento.php?id=<?= (int)$documento["id_documento"] ?>"
                                        >
                                            Descargar
                                        </a>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="text-center py-5">

                    <div class="sin-documentos">▣</div>

                    <h5 class="mt-3">
                        Todavía no tenés documentos médicos
                    </h5>

                    <p class="text-muted mb-0">
                        Cuando el administrador registre documentos,
                        aparecerán en esta sección.
                    </p>

                </div>

            <?php endif; ?>

            <div class="mt-4">

                <a href="../panel.php" class="btn-volver">
                    ← Volver al panel
                </a>

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

$consulta->close();
$conexion->close();

?>
