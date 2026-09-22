<?php

session_start();

if (!isset($_SESSION["id"]) || $_SESSION["rol"] != "Administrador") {
    die("Acceso denegado.");
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de administración</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <span class="navbar-brand mb-0 h1">Sistema Hospitalario</span>

            <a href="Backend/logout.php" class="btn btn-light">
                Cerrar sesión
            </a>
        </div>
    </nav>

    <main class="container py-5">

        <div class="mb-4">
            <h1 class="fw-bold">Panel de administración</h1>
            <p class="text-secondary">
                Bienvenido, <?php echo htmlspecialchars($_SESSION["nombre"]); ?>.
            </p>
        </div>

        <div class="row g-4">

            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Usuarios</h5>
                        <p class="card-text">
                            Administrar los usuarios registrados en el sistema.
                        </p>
                        <button class="btn btn-primary">
                            Gestionar usuarios
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Documentación</h5>
                        <p class="card-text">
                            Administrar y consultar la documentación del sistema.
                        </p>
                        <button class="btn btn-primary">
                            Ver documentación
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Encuestas</h5>
                        <p class="card-text">
                            Consultar las encuestas de satisfacción realizadas.
                        </p>
                        <button class="btn btn-primary">
                            Ver encuestas
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <h5 class="card-title">Información de la sesión</h5>

                <p class="mb-1">
                    <strong>Nombre:</strong>
                    <?php echo htmlspecialchars($_SESSION["nombre"]); ?>
                </p>

                <p class="mb-0">
                    <strong>Rol:</strong>
                    <?php echo htmlspecialchars($_SESSION["rol"]); ?>
                </p>
            </div>
        </div>

    </main>

    <footer class="text-center text-secondary py-4">
        Sistema Hospitalario
    </footer>

</body>
</html> 