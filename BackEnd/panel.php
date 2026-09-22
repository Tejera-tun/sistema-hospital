<?php

session_start();

if (!isset($_SESSION["id"]) || !isset($_SESSION["rol"])) {
    header("Location: login.html");
    exit;
}

$rol = $_SESSION["rol"];

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FLEM | Sistema Hospitalario</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

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
        <div class="container py-2">

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

        <div class="card welcome-card shadow mb-4">
            <div class="card-body p-4 p-md-5">

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">

                    <div>
                        <p class="mb-2 gold fw-semibold">
                            SISTEMA HOSPITALARIO
                        </p>

                        <h1 class="fw-bold">
                            Bienvenido,
                            <?php echo htmlspecialchars($_SESSION["nombre"]); ?>
                        </h1>

                        <p class="mb-0">
                            <?php echo htmlspecialchars($_SESSION["apellido"]); ?>
                        </p>
                    </div>

                    <div class="mt-3 mt-md-0">
                        <span class="badge role-badge fs-6 px-3 py-2">
                            <?php echo htmlspecialchars($rol); ?>
                        </span>
                    </div>

                </div>

            </div>
        </div>

        <?php if ($rol == "Administrador") { ?>

            <h2 class="fw-bold mb-4">Administración</h2>

            <div class="row g-4">

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

                            <button class="btn btn-flem">
                                Gestionar usuarios
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card option-card shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="medical-icon mb-3">▣</div>

                            <h5 class="fw-bold">
                                Documentación
                            </h5>

                            <p class="text-secondary">
                                Administrar y consultar la documentación del sistema.
                            </p>

                            <button class="btn btn-flem">
                                Ver documentación
                            </button>
                        </div>
                    </div>
                </div>

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

                            <button class="btn btn-flem">
                                Ver encuestas
                            </button>
                        </div>
                    </div>
                </div>

            </div>

        <?php } elseif ($rol == "Paciente") { ?>

            <h2 class="fw-bold mb-4">Mi espacio</h2>

            <div class="row g-4">

                <div class="col-md-6 col-lg-4">
                    <div class="card option-card shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="medical-icon mb-3">⚕</div>

                            <h5 class="fw-bold">
                                Mi información
                            </h5>

                            <p class="text-secondary">
                                Consultar la información de tu cuenta y tus datos personales.
                            </p>

                            <button class="btn btn-flem">
                                Ver mi información
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="card option-card shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="medical-icon mb-3">▣</div>

                            <h5 class="fw-bold">
                                Documentación
                            </h5>

                            <p class="text-secondary">
                                Acceder a la documentación disponible del sistema.
                            </p>

                            <button class="btn btn-flem">
                                Ver documentación
                            </button>
                        </div>
                    </div>
                </div>

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

                            <button class="btn btn-flem">
                                Completar encuesta
                            </button>
                        </div>
                    </div>
                </div>

            </div>

        <?php } ?>

        <div class="card info-card shadow-sm mt-5">
            <div class="card-body p-4">

                <h5 class="fw-bold mb-3">
                    Información de la cuenta
                </h5>

                <div class="row">

                    <div class="col-md-6 mb-2">
                        <strong>Nombre:</strong>
                        <?php echo htmlspecialchars($_SESSION["nombre"]); ?>
                    </div>

                    <div class="col-md-6 mb-2">
                        <strong>Apellido:</strong>
                        <?php echo htmlspecialchars($_SESSION["apellido"]); ?>
                    </div>

                    <div class="col-md-6 mb-2">
                        <strong>Correo:</strong>
                        <?php echo htmlspecialchars($_SESSION["correo"]); ?>
                    </div>

                    <div class="col-md-6 mb-2">
                        <strong>Rol:</strong>
                        <?php echo htmlspecialchars($rol); ?>
                    </div>

                </div>

            </div>
        </div>

    </main>

    <footer class="text-center py-4">
        <div class="fw-bold">⚕ FLEM</div>
        <div>Sistema Hospitalario</div>
    </footer>

</body>
</html>