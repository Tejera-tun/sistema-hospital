
<?php

session_start();

require_once __DIR__ . "/../../BackEnd/conexion.php";

// VERIFICAR PERMISOS
if (
    !isset($_SESSION["id"]) ||
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "Administrador"
) {
    header("Location: ../../BackEnd/login.php");
    exit;
}

// OBTENER PACIENTES
$sql = "SELECT id, nombre, apellido, cedula
        FROM usuarios
        WHERE rol = 'Paciente'
        ORDER BY apellido, nombre";

$resultado = $conexion->query($sql);

if (!$resultado) {
    die("Error al obtener pacientes: " . $conexion->error);
}

function limpiar($dato) {
    return htmlspecialchars((string)$dato, ENT_QUOTES, "UTF-8");
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>FLEM | Documentos médicos</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>

        body {
            background: #f4f8f6;
            min-height: 100vh;
        }

        .navbar-flem {
            background: #075c45;
        }

        .brand-symbol {
            color: #d4af37;
            font-size: 28px;
            margin-right: 8px;
        }

        .brand-name {
            color: white;
            font-weight: 700;
            letter-spacing: 2px;
        }

        .gold {
            color: #d4af37;
        }

        .contenedor {
            max-width: 850px;
            margin: 45px auto;
        }

        .documento-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
        }

        .documento-header {
            background: linear-gradient(135deg, #075c45, #0a7658);
            color: white;
            padding: 28px;
        }

        .documento-icono {
            font-size: 42px;
            color: #d4af37;
        }

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
            border-color: #0a7658;
            box-shadow: 0 0 0 0.2rem rgba(7, 92, 69, 0.12);
        }

        .btn-flem {
            background: #075c45;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 13px;
            font-weight: 600;
        }

        .btn-flem:hover {
            background: #064936;
            color: white;
        }

        .btn-volver {
            background: #e9efec;
            color: #075c45;
            border: none;
            border-radius: 9px;
            padding: 10px 18px;
            font-weight: 600;
            text-decoration: none;
        }

        .btn-volver:hover {
            background: #dce8e2;
            color: #064936;
        }

        .ayuda {
            color: #718078;
            font-size: 13px;
        }

        footer {
            color: #718078;
        }

    </style>

</head>

<body>

    <!-- BARRA SUPERIOR -->

    <nav class="navbar navbar-flem">

        <div class="container">

            <span class="navbar-brand mb-0">
                <span class="brand-symbol">⚕</span>
                <span class="brand-name">FLEM</span>
            </span>

            <a href="../../BackEnd/logout.php" class="btn btn-light">
                Cerrar sesión
            </a>

        </div>

    </nav>


    <!-- CONTENIDO -->

    <main class="container">

        <div class="contenedor">

            <div class="card documento-card shadow">

                <!-- ENCABEZADO -->

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


                <!-- FORMULARIO -->

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
                        enctype="multipart/form-data">


                        <!-- PACIENTE -->

                        <div class="mb-4">

                            <label class="form-label">
                                Seleccionar paciente
                            </label>

                            <select
                                name="id_paciente"
                                class="form-select"
                                required>

                                <option value="">
                                    Seleccionar paciente
                                </option>

                                <?php while ($paciente = $resultado->fetch_assoc()) { ?>

                                    <option value="<?= (int)$paciente["id"] ?>">

                                        <?= limpiar(
                                            $paciente["apellido"] . ", " .
                                            $paciente["nombre"] .
                                            " - CI: " .
                                            $paciente["cedula"]
                                        ) ?>

                                    </option>

                                <?php } ?>

                            </select>

                        </div>


                        <!-- NOMBRE DEL DOCUMENTO -->

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
                                required>

                        </div>


                        <!-- CATEGORIA -->

                        <div class="mb-4">

                            <label class="form-label">
                                Categoría del documento
                            </label>

                            <select
                                name="categoria"
                                class="form-select"
                                required>

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


                        <!-- DESCRIPCION -->

                        <div class="mb-4">

                            <label class="form-label">
                                Descripción
                            </label>

                            <textarea
                                name="descripcion"
                                class="form-control"
                                rows="4"
                                placeholder="Agregá información sobre el documento..."></textarea>

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
                                required>

                            <div class="ayuda mt-2">
                                Formatos permitidos: PDF, JPG, PNG,
                                DOCX y XLSX. Tamaño máximo: 10 MB.
                            </div>

                        </div>


                        <!-- BOTONES -->

                        <div class="d-grid gap-3">

                            <button
                                type="submit"
                                class="btn btn-flem">

                                Subir documento médico

                            </button>

                            <a
                                href="../../BackEnd/panel.php"
                                class="btn btn-volver text-center">

                                ← Volver al panel administrativo

                            </a>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </main>


    <!-- PIE DE PAGINA -->

    <footer class="text-center py-4">

        <div class="fw-bold">⚕ FLEM</div>
        <div>Sistema Hospitalario</div>

    </footer>

</body>
</html>

<?php
$resultado->free();
$conexion->close();
?>
