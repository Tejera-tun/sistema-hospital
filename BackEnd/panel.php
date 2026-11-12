
<?php

session_start();

if (!isset($_SESSION["id"]) || !isset($_SESSION["rol"])) {
    header("Location: ../FrontEnd/login.html");
    exit;
}

$rol = $_SESSION["rol"];

function limpiar($dato) {
    return htmlspecialchars((string)$dato, ENT_QUOTES, "UTF-8");
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>FLEM × Hospital de Clínicas | Panel principal</title>

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

        .brand-symbol {
            color: var(--dorado);
            font-size: 27px;
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

        /* COLORES */

        .gold {
            color: var(--dorado);
        }

        .welcome-card {
            background: linear-gradient(135deg, var(--verde), #0a7658);
            color: white;
            border: none;
            border-radius: 20px;
        }

        .role-badge {
            background: var(--dorado);
            color: #173d31;
            font-weight: 700;
        }

        /* TARJETAS */

        .option-card {
            border: none;
            border-radius: 18px;
            transition: transform .25s, box-shadow .25s;
        }

        .option-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,.10) !important;
        }

        .medical-icon {
            font-size: 38px;
            color: #b8962e;
        }

        .btn-flem {
            background: var(--verde);
            color: white;
            border: none;
            border-radius: 9px;
            padding: 9px 16px;
        }

        .btn-flem:hover {
            background: var(--verde-oscuro);
            color: white;
        }

        .info-card {
            border: none;
            border-radius: 18px;
        }

        .info-title {
            color: var(--verde);
        }

        footer {
            color: #6c757d;
        }

        .footer-brand {
            color: var(--dorado);
            font-weight: 800;
            letter-spacing: 2px;
        }

        @media (max-width: 500px) {

            .brand-flem {
                gap: 7px;
            }

            .brand-name {
                font-size: 18px;
            }

            .brand-symbol,
            .brand-x {
                font-size: 21px;
            }

            .logo-hospital {
                width: 105px;
                height: 40px;
            }

        }

    </style>

</head>

<body>

    <!-- ENCABEZADO FLEM × HOSPITAL DE CLÍNICAS -->

    <nav class="navbar navbar-flem">

        <div class="container">

            <a href="panel.php" class="brand-flem">

                <span class="brand-symbol">⚕</span>

                <span class="brand-name">FLEM</span>

                <span class="brand-x">×</span>

                <img
                    src="../FrontEnd/img/logo-hospital-clinicas.png"
                    alt="Hospital de Clínicas"
                    class="logo-hospital"
                >

            </a>

            <a href="logout.php" class="btn btn-light">
                Cerrar sesión
            </a>

        </div>

    </nav>


    <main class="container py-5">

        <!-- BIENVENIDA -->

        <div class="card welcome-card shadow mb-4">

            <div class="card-body p-4 p-md-5">

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">

                    <div>

                        <p class="mb-2 gold fw-semibold">
                            SISTEMA HOSPITALARIO
                        </p>

                        <h1 class="fw-bold">
                            Bienvenido,
                            <?= limpiar($_SESSION["nombre"]); ?>
                        </h1>

                        <p class="mb-0">
                            <?= limpiar($_SESSION["apellido"]); ?>
                        </p>

                    </div>

                    <div class="mt-3 mt-md-0">

                        <span class="badge role-badge fs-6 px-3 py-2">
                            <?= limpiar($rol); ?>
                        </span>

                    </div>

                </div>

            </div>

        </div>


        <!-- PANEL ADMINISTRADOR -->

        <?php if ($rol === "Administrador") { ?>

            <h2 class="fw-bold mb-4">
                Administración
            </h2>

            <div class="row g-4">

                <!-- GESTIÓN DE USUARIOS -->

                <div class="col-md-6 col-lg-4">

                    <div class="card option-card shadow-sm h-100">

                        <div class="card-body p-4">

                            <div class="medical-icon mb-3">⚕</div>

                            <h5 class="fw-bold">
                                Gestión de usuarios
                            </h5>

                            <p class="text-secondary">
                                Administrar los usuarios registrados en el sistema.
                            </p>

                            <a href="Admin/gestionUsuarios.php"
                               class="btn btn-flem">

                                Gestionar usuarios

                            </a>

                        </div>

                    </div>

                </div>


                <!-- INFORMACIÓN MÉDICA -->

                <div class="col-md-6 col-lg-4">

                    <div class="card option-card shadow-sm h-100">

                        <div class="card-body p-4">

                            <div class="medical-icon mb-3">✚</div>

                            <h5 class="fw-bold">
                                Gestión de información médica
                            </h5>

                            <p class="text-secondary">
                                Registrar y actualizar tratamientos,
                                información médica, prevención y especialidades.
                            </p>

                            <a href="Tratamientos/listarSeguimiento.php"
                               class="btn btn-flem">

                                Gestionar información

                            </a>

                        </div>

                    </div>

                </div>


                <!-- DOCUMENTACIÓN -->

                <div class="col-md-6 col-lg-4">

                    <div class="card option-card shadow-sm h-100">

                        <div class="card-body p-4">

                            <div class="medical-icon mb-3">▣</div>

                            <h5 class="fw-bold">
                                Documentación
                            </h5>

                            <p class="text-secondary">
                                Subir, clasificar y administrar
                                los documentos médicos de los pacientes.
                            </p>

                            <a href="../FrontEnd/DocumentosMedicos/documentosMedicos.php"
                               class="btn btn-flem">

                                Ver documentación

                            </a>

                        </div>

                    </div>

                </div>


                <!-- ENCUESTAS -->

                <div class="col-md-6 col-lg-4">

                    <div class="card option-card shadow-sm h-100">

                        <div class="card-body p-4">

                            <div class="medical-icon mb-3">✓</div>

                            <h5 class="fw-bold">
                                Encuestas
                            </h5>

                            <p class="text-secondary">
                                Administrar preguntas, opciones y encuestas de satisfacción.
                            </p>

                            <a href="Encuestas/gestionarEncuestas.php"
                               class="btn btn-flem">

                                Gestionar encuestas

                            </a>

                        </div>

                    </div>

                </div>

            </div>


        <!-- PANEL PACIENTE -->

        <?php } elseif ($rol === "Paciente") { ?>

            <h2 class="fw-bold mb-4">
                Mi espacio
            </h2>

            <div class="row g-4">


                <!-- MI INFORMACIÓN -->

                <div class="col-md-6 col-lg-4">

                    <div class="card option-card shadow-sm h-100">

                        <div class="card-body p-4">

                            <div class="medical-icon mb-3">⚕</div>

                            <h5 class="fw-bold">
                                Mi información
                            </h5>

                            <p class="text-secondary">
                                Consultar la información de tu cuenta
                                y tus datos personales.
                            </p>

                            <a href="#informacion-cuenta"
                               class="btn btn-flem">

                                Ver mi información

                            </a>

                        </div>

                    </div>

                </div>


                <!-- MIS DOCUMENTOS -->

                <div class="col-md-6 col-lg-4">

                    <div class="card option-card shadow-sm h-100">

                        <div class="card-body p-4">

                            <div class="medical-icon mb-3">▣</div>

                            <h5 class="fw-bold">
                                Mis documentos médicos
                            </h5>

                            <p class="text-secondary">
                                Consultar y descargar tus documentos
                                médicos personales.
                            </p>

                            <a href="../FrontEnd/DocumentosMedicos/misDocumentos.php"
                               class="btn btn-flem">

                                Ver mis documentos

                            </a>

                        </div>

                    </div>

                </div>


                <!-- SEGUIMIENTO -->

                <div class="col-md-6 col-lg-4">

                    <div class="card option-card shadow-sm h-100">

                        <div class="card-body p-4">

                            <div class="medical-icon mb-3">✚</div>

                            <h5 class="fw-bold">
                                Seguimiento de mi tratamiento
                            </h5>

                            <p class="text-secondary">
                                Consultar las actualizaciones y la evolución
                                de tus tratamientos médicos.
                            </p>

                            <a href="Tratamientos/listarSeguimiento.php"
                               class="btn btn-flem">

                                Ver seguimiento

                            </a>

                        </div>

                    </div>

                </div>


                <!-- ENCUESTA -->

                <div class="col-md-6 col-lg-4">

                    <div class="card option-card shadow-sm h-100">

                        <div class="card-body p-4">

                            <div class="medical-icon mb-3">✓</div>

                            <h5 class="fw-bold">
                                Encuesta de satisfacción
                            </h5>

                            <p class="text-secondary">
                                Completar una encuesta sobre tu experiencia.
                            </p>

                            <a href="../FrontEnd/encuesta.php"
                               class="btn btn-flem">

                                Completar encuesta

                            </a>

                        </div>

                    </div>

                </div>

            </div>

        <?php } ?>


        <!-- INFORMACIÓN DE LA CUENTA -->

        <div class="card info-card shadow-sm mt-5"
             id="informacion-cuenta">

            <div class="card-body p-4">

                <h5 class="fw-bold info-title mb-3">
                    Información de la cuenta
                </h5>

                <div class="row">

                    <div class="col-md-6 mb-2">

                        <strong>Nombre:</strong>
                        <?= limpiar($_SESSION["nombre"]); ?>

                    </div>

                    <div class="col-md-6 mb-2">

                        <strong>Apellido:</strong>
                        <?= limpiar($_SESSION["apellido"]); ?>

                    </div>

                    <div class="col-md-6 mb-2">

                        <strong>Correo:</strong>
                        <?= limpiar($_SESSION["correo"]); ?>

                    </div>

                    <div class="col-md-6 mb-2">

                        <strong>Rol:</strong>
                        <?= limpiar($rol); ?>

                    </div>

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
