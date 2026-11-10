
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

    <title>FLEM | Sistema Hospitalario</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>

        body {
            background: #f4f8f6;
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
            color: #b8962e;
        }

        .welcome-card {
            background: linear-gradient(135deg, #075c45, #0a7658);
            color: white;
            border: none;
            border-radius: 20px;
        }

        .role-badge {
            background: #d4af37;
            color: #173d31;
            font-weight: 600;
        }

        .option-card {
            border: none;
            border-radius: 18px;
            transition: 0.25s;
        }

        .option-card:hover {
            transform: translateY(-5px);
        }

        .medical-icon {
            font-size: 38px;
            color: #b8962e;
        }

        .btn-flem {
            background: #075c45;
            color: white;
            border: none;
        }

        .btn-flem:hover {
            background: #064936;
            color: white;
        }

        .info-card {
            border: none;
            border-radius: 18px;
        }

        footer {
            color: #6c757d;
        }

    </style>

</head>

<body>

    <nav class="navbar navbar-flem">

        <div class="container">

            <span class="navbar-brand mb-0">
                <span class="brand-symbol">⚕</span>
                <span class="brand-name">FLEM</span>
            </span>

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


                <!-- GESTIÓN DE INFORMACIÓN MÉDICA -->

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
                                Consultar las encuestas de satisfacción.
                            </p>

                            <a href="../FrontEnd/encuesta.php"
                               class="btn btn-flem">

                                Ver encuestas

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


                <!-- SEGUIMIENTO DEL TRATAMIENTO -->

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

                <h5 class="fw-bold mb-3">
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


    <!-- FOOTER -->

    <footer class="text-center py-4">

        <div class="fw-bold">⚕ FLEM</div>

        <div>Sistema Hospitalario</div>

    </footer>

</body>

</html>
