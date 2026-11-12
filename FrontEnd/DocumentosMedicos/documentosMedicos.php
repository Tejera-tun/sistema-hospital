
<?php

session_start();

require_once __DIR__ . "/../../BackEnd/conexion.php";

// ===================================
// VERIFICAR PERMISOS
// ===================================

if (
    !isset($_SESSION["id"]) ||
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "Administrador"
) {
    header("Location: ../../BackEnd/login.php");
    exit;
}

// ===================================
// GENERAR TOKEN CSRF
// ===================================

if (empty($_SESSION["csrf"])) {
    $_SESSION["csrf"] = bin2hex(random_bytes(32));
}

// ===================================
// FUNCIÓN PARA LIMPIAR DATOS
// ===================================

function limpiar($dato) {
    return htmlspecialchars((string)$dato, ENT_QUOTES, "UTF-8");
}

// ===================================
// OBTENER PACIENTES
// ===================================

$sql = "SELECT id, nombre, apellido, cedula
        FROM usuarios
        WHERE rol = 'Paciente'
        ORDER BY apellido, nombre";

$resultado = $conexion->query($sql);

if (!$resultado) {
    die("Error al obtener pacientes.");
}

// ===================================
// OBTENER DOCUMENTOS REGISTRADOS
// ===================================

$sqlDocumentos = "
    SELECT
        d.id_documento,
        d.id_paciente,
        d.nombre_documento,
        d.categoria,
        d.descripcion,
        d.tipo_documento,
        d.fecha_subida,
        u.nombre,
        u.apellido,
        u.cedula
    FROM documentos_medicos d
    INNER JOIN usuarios u ON d.id_paciente = u.id
    ORDER BY d.fecha_subida DESC
";

$resultadoDocumentos = $conexion->query($sqlDocumentos);

if (!$resultadoDocumentos) {
    die("Error al obtener los documentos médicos.");
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>FLEM × Hospital de Clínicas | Documentos médicos</title>

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
            min-height: 100vh;
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

        .documento-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
        }

        .documento-header {
            background: linear-gradient(135deg, var(--verde), #0a7658);
            color: white;
            padding: 28px;
        }

        .documento-icono {
            font-size: 42px;
            color: var(--dorado);
        }

        .gold {
            color: var(--dorado);
        }

        /* FORMULARIOS */

        .form-label {
            font-weight: 600;
            color: #263b33;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            padding: 12px;
            border: 1px solid #dce5e0;
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
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
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
            padding: 10px 16px;
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
            padding: 10px 18px;
            font-weight: 600;
            text-decoration: none;
        }

        .btn-volver:hover {
            background: #dce8e2;
            color: var(--verde);
        }

        /* TABLA */

        .tabla-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
        }

        .tabla-titulo {
            color: var(--verde);
            font-weight: bold;
        }

        .tabla-documentos thead th {
            background: var(--verde);
            color: white;
            white-space: nowrap;
            padding: 14px;
        }

        .tabla-documentos tbody td {
            padding: 13px;
            vertical-align: middle;
        }

        .categoria {
            background: #f4e6b7;
            color: #624d0c;
            padding: 7px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }

        .ayuda {
            color: #718078;
            font-size: 13px;
        }

        .sin-documentos {
            color: var(--verde);
            font-size: 42px;
        }

        footer {
            color: #718078;
        }

        .footer-brand {
            color: var(--dorado);
            font-weight: 800;
            letter-spacing: 2px;
        }

        @media(max-width: 600px) {

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

            .documento-header {
                padding: 22px;
            }

        }

    </style>

</head>

<body>

    <!-- ENCABEZADO FLEM × HOSPITAL DE CLÍNICAS -->

    <nav class="navbar navbar-flem">

        <div class="container d-flex justify-content-between align-items-center">

            <a href="../../BackEnd/panel.php" class="brand-flem">

                <span class="brand-name">FLEM</span>

                <span class="brand-x">×</span>

                <img
                    src="../img/logo-hospital-clinicas.png"
                    alt="Hospital de Clínicas"
                    class="logo-hospital"
                >

            </a>

            <a href="../../BackEnd/logout.php" class="btn btn-light">
                Cerrar sesión
            </a>

        </div>

    </nav>


    <main class="container">

        <div class="contenedor">

            <!-- MENSAJES -->

            <?php if (isset($_GET["eliminado"])): ?>

                <div class="alert alert-success">
                    Documento eliminado correctamente.
                </div>

            <?php endif; ?>

            <?php if (isset($_GET["error"])): ?>

                <div class="alert alert-danger">
                    No se pudo completar la operación solicitada.
                </div>

            <?php endif; ?>


            <!-- FORMULARIO PARA SUBIR DOCUMENTOS -->

            <div class="card documento-card shadow mb-5">

                <div class="documento-header">

                    <div class="documento-icono mb-2">
                        ▣
                    </div>

                    <p class="gold fw-semibold mb-2">
                        SISTEMA HOSPITALARIO
                    </p>

                    <h2 class="fw-bold mb-1">
                        Documentos médicos
                    </h2>

                    <p class="mb-0">
                        Administración y carga de documentos de pacientes.
                    </p>

                </div>


                <div class="card-body p-4 p-md-5">

                    <h4 class="fw-bold mb-2">
                        Cargar nuevo documento
                    </h4>

                    <p class="text-secondary mb-4">
                        Completá los datos para asociar un archivo
                        al paciente correspondiente.
                    </p>


                    <form
                        action="../../BackEnd/DocumentosMedicos/subirDocumentos.php"
                        method="POST"
                        enctype="multipart/form-data"
                    >

                        <!-- PACIENTE -->

                        <div class="mb-4">

                            <label class="form-label">
                                Seleccionar paciente
                            </label>

                            <select
                                name="id_paciente"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Seleccionar paciente
                                </option>

                                <?php while ($paciente = $resultado->fetch_assoc()): ?>

                                    <option value="<?= (int)$paciente["id"] ?>">

                                        <?= limpiar(
                                            $paciente["apellido"] . ", " .
                                            $paciente["nombre"] .
                                            " - CI: " .
                                            $paciente["cedula"]
                                        ) ?>

                                    </option>

                                <?php endwhile; ?>

                            </select>

                        </div>


                        <!-- NOMBRE -->

                        <div class="mb-4">

                            <label class="form-label">
                                Nombre del documento
                            </label>

                            <input
                                type="text"
                                name="nombre_documento"
                                class="form-control"
                                maxlength="200"
                                placeholder="Ej: Análisis de sangre"
                                required
                            >

                        </div>


                        <!-- CATEGORÍA -->

                        <div class="mb-4">

                            <label class="form-label">
                                Categoría del documento
                            </label>

                            <select
                                name="categoria"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Seleccionar categoría
                                </option>

                                <option value="Analisis clinicos">
                                    Análisis clínicos
                                </option>

                                <option value="Estudios medicos">
                                    Estudios médicos
                                </option>

                                <option value="Informes medicos">
                                    Informes médicos
                                </option>

                                <option value="Recetas medicas">
                                    Recetas médicas
                                </option>

                                <option value="Otros">
                                    Otros documentos
                                </option>

                            </select>

                        </div>


                        <!-- DESCRIPCIÓN -->

                        <div class="mb-4">

                            <label class="form-label">
                                Descripción
                            </label>

                            <textarea
                                name="descripcion"
                                class="form-control"
                                rows="4"
                                placeholder="Agregá información sobre el documento..."
                            ></textarea>

                        </div>


                        <!-- ARCHIVO -->

                        <div class="mb-4">

                            <label class="form-label">
                                Seleccionar archivo
                            </label>

                            <input
                                type="file"
                                name="documento"
                                class="form-control"
                                accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx"
                                required
                            >

                            <div class="ayuda mt-2">
                                Formatos permitidos: PDF, JPG, PNG,
                                DOCX y XLSX. Tamaño máximo: 10 MB.
                            </div>

                        </div>


                        <!-- BOTONES -->

                        <div class="d-grid gap-3">

                            <button
                                type="submit"
                                class="btn btn-flem"
                            >
                                Subir documento médico
                            </button>

                            <a
                                href="../../BackEnd/panel.php"
                                class="btn btn-volver text-center"
                            >
                                ← Volver al panel administrativo
                            </a>

                        </div>

                    </form>

                </div>

            </div>


            <!-- LISTADO DE DOCUMENTOS -->

            <div class="card tabla-card shadow">

                <div class="card-body p-4">

                    <h4 class="tabla-titulo mb-4">
                        Documentos médicos registrados
                    </h4>

                    <?php if ($resultadoDocumentos->num_rows > 0): ?>

                        <div class="table-responsive">

                            <table class="table table-hover tabla-documentos">

                                <thead>

                                    <tr>

                                        <th>Documento</th>

                                        <th>Paciente</th>

                                        <th>Cédula</th>

                                        <th>Categoría</th>

                                        <th>Fecha</th>

                                        <th>Formato</th>

                                        <th>Acciones</th>

                                    </tr>

                                </thead>

                                <tbody>

                                    <?php while ($documento = $resultadoDocumentos->fetch_assoc()): ?>

                                        <tr>

                                            <td class="fw-semibold">

                                                <?= limpiar($documento["nombre_documento"]) ?>

                                            </td>

                                            <td>

                                                <?= limpiar(
                                                    $documento["nombre"] . " " .
                                                    $documento["apellido"]
                                                ) ?>

                                            </td>

                                            <td>

                                                <?= limpiar($documento["cedula"]) ?>

                                            </td>

                                            <td>

                                                <span class="categoria">
                                                    <?= limpiar($documento["categoria"]) ?>
                                                </span>

                                            </td>

                                            <td>

                                                <?= limpiar($documento["fecha_subida"]) ?>

                                            </td>

                                            <td>

                                                <?= limpiar($documento["tipo_documento"]) ?>

                                            </td>

                                            <td>

                                                <div class="d-flex gap-2 flex-wrap">

                                                    <!-- DESCARGAR -->

                                                    <a
                                                        href="../../BackEnd/DocumentosMedicos/descargarDocumento.php?id=<?= (int)$documento["id_documento"] ?>"
                                                        class="btn btn-flem btn-sm"
                                                    >
                                                        Descargar
                                                    </a>


                                                    <!-- ELIMINAR -->

                                                    <form
                                                        method="POST"
                                                        action="../../BackEnd/DocumentosMedicos/eliminarDocumento.php"
                                                        onsubmit="return confirm('¿Seguro que querés eliminar este documento médico?');"
                                                    >

                                                        <input
                                                            type="hidden"
                                                            name="id_documento"
                                                            value="<?= (int)$documento["id_documento"] ?>"
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

                                        </tr>

                                    <?php endwhile; ?>

                                </tbody>

                            </table>

                        </div>

                    <?php else: ?>

                        <div class="text-center py-5">

                            <div class="sin-documentos">
                                ▣
                            </div>

                            <h5 class="mt-3">
                                No hay documentos registrados
                            </h5>

                            <p class="text-secondary mb-0">
                                Cuando se carguen documentos médicos,
                                aparecerán en esta sección.
                            </p>

                        </div>

                    <?php endif; ?>

                </div>

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

$resultado->free();
$resultadoDocumentos->free();
$conexion->close();

?>
