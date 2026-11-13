
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

        :root {
            --verde: #075c45;
            --verde-oscuro: #064735;
            --dorado: #d4af37;
            --fondo: #f4f8f6;
        }

        body {
            background: var(--fondo);
            min-height: 100vh;
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
            font-weight: 700;
            letter-spacing: 2px;
            font-size: 27px;
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
            max-width: 850px;
            margin: 45px auto;
        }

        .documento-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
        }

        .documento-header {
            background: linear-gradient(135deg, var(--verde), #0a7658);
            color: white;
            padding: 32px;
        }

        .documento-icono {
            font-size: 42px;
            color: var(--dorado);
        }

        .gold {
            color: var(--dorado);
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
            border-color: var(--verde);
            box-shadow: 0 0 0 0.2rem rgba(7, 92, 69, 0.12);
        }

        .btn-flem {
            background: var(--verde);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 13px;
            font-weight: 600;
        }

        .btn-flem:hover {
            background: var(--verde-oscuro);
            color: white;
        }

        .btn-volver {
            background: #e9efec;
            color: var(--verde);
            border: none;
            border-radius: 9px;
            padding: 12px 18px;
            font-weight: 600;
            text-decoration: none;
        }

        .btn-volver:hover {
            background: #dce8e2;
            color: var(--verde-oscuro);
        }

        .ayuda {
            color: #718078;
            font-size: 13px;
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
            font-weight: 700;
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

        @media (max-width: 576px) {

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

            .documento-header {
                padding: 23px;
            }

            .contenedor {
                margin: 25px auto;
            }

        }

    </style>

</head>

<body>

    <!-- BARRA SUPERIOR -->

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
                                placeholder="Ej: Documento médico"
                                required>

                        </div>


                        <!-- CATEGORÍAS MÉDICAS -->

                        <div class="mb-4">

                            <label class="form-label">
                                Tipo de documento médico
                            </label>

                            <select
                                name="categoria"
                                class="form-select"
                                required>

                                <option value="">
                                    Seleccionar tipo de documento
                                </option>

                                <option value="Indicaciones de interrupción voluntaria del embarazo">
                                    Indicaciones de interrupción voluntaria del embarazo
                                </option>

                                <option value="Prostatectomía radical (indicaciones e información para el paciente)">
                                    Prostatectomía radical (indicaciones e información para el paciente)
                                </option>

                                <option value="Preparación para estudios imagenológicos">
                                    Preparación para estudios imagenológicos
                                </option>

                                <option value="Estudios diagnósticos con pertecnetato">
                                    Estudios diagnósticos con pertecnetato
                                </option>

                                <option value="Centellograma de perfusión miocárdica">
                                    Centellograma de perfusión miocárdica
                                </option>

                                <option value="Indicaciones ecocardiograma con dobutamina">
                                    Indicaciones ecocardiograma con dobutamina
                                </option>

                                <option value="Indicaciones para pacientes en tratamiento con warfarina">
                                    Indicaciones para pacientes en tratamiento con warfarina
                                </option>

                                <option value="Indicaciones ecocardiograma transesofágico">
                                    Indicaciones ecocardiograma transesofágico
                                </option>

                                <option value="Indicaciones para ingreso a centro de nefrología y trasplante">
                                    Indicaciones para ingreso a centro de nefrología y trasplante
                                </option>

                                <option value="Plan de alta de enfermería, Nefrología">
                                    Plan de alta de enfermería, Nefrología
                                </option>

                                <option value="Indicaciones de enfermería para usuarios trasplantados">
                                    Indicaciones de enfermería para usuarios trasplantados
                                </option>

                                <option value="Prevención de infecciones">
                                    Prevención de infecciones
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
$resultado->free();
$conexion->close();
?>
