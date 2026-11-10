
<?php

session_start();

// CONEXIÓN A MYSQL
require_once __DIR__ . "/../../BackEnd/conexion.php";

// VERIFICAR PERMISOS
if (
    !isset($_SESSION["id"]) ||
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "Administrador"
) {
    die("Acceso denegado.");
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

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Documentos Médicos | FLEM</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <style>

        body {
            background-color: #f4f6f9;
        }

        .card {
            border: none;
            border-radius: 12px;
        }

        .card-header {
            background-color: #0d6efd;
            color: white;
            padding: 20px;
            border-radius: 12px 12px 0 0 !important;
        }

        .form-label {
            font-weight: 600;
        }

        .btn-primary {
            padding: 12px;
        }

    </style>

</head>

<body>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-md-8 col-lg-7">

            <div class="card shadow-sm">

                <div class="card-header">

                    <h4 class="mb-0">
                        Documentos Médicos
                    </h4>

                    <small>
                        Administración y carga de documentos de pacientes
                    </small>

                </div>

                <div class="card-body p-4">

                    <h5 class="mb-3">
                        Cargar nuevo documento
                    </h5>

                    <p class="text-muted">
                        Completá los datos para asociar un archivo
                        al paciente correspondiente.
                    </p>

                    <form
                        action="../../BackEnd/DocumentosMedicos/subirDocumentos.php"
                        method="POST"
                        enctype="multipart/form-data">

                        <!-- PACIENTE -->

                        <div class="mb-3">

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

                                        <?= htmlspecialchars(
                                            $paciente["apellido"] . ", " .
                                            $paciente["nombre"] .
                                            " - CI: " .
                                            $paciente["cedula"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ) ?>

                                    </option>

                                <?php } ?>

                            </select>

                        </div>

                        <!-- NOMBRE -->

                        <div class="mb-3">

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

                        <div class="mb-3">

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

                        <div class="mb-3">

                            <label class="form-label">
                                Descripción
                            </label>

                            <textarea
                                name="descripcion"
                                class="form-control"
                                rows="3"
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

                            <div class="form-text">
                                Formatos permitidos: PDF, JPG, PNG,
                                DOCX y XLSX. Tamaño máximo: 10 MB.
                            </div>

                        </div>

                        <!-- BOTON -->

                        <button
                            type="submit"
                            class="btn btn-primary w-100">

                            Subir documento médico

                        </button>

                    </form>

                </div>

            </div>

            <div class="text-center mt-3">

                <a href="../../BackEnd/panel.php"
                   class="text-decoration-none">

                    Volver al panel administrativo

                </a>

            </div>

        </div>

    </div>

</div>

</body>
</html>
