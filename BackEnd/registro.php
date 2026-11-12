
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>FLEM | Registro</title>

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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--fondo);
            font-family: Arial, sans-serif;
            color: #26352f;
        }

        /* ENCABEZADO */

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
            font-weight: bold;
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

        /* CONTENIDO */

        main {
            flex: 1;
            padding-top: 45px;
            padding-bottom: 45px;
        }

        .register-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
        }

        .register-card .card-body {
            padding: 40px;
        }

        .medical-symbol {
            font-size: 48px;
            color: var(--dorado);
        }

        .flem-title {
            color: var(--verde);
            font-weight: 800;
            letter-spacing: 3px;
        }

        .form-label {
            color: #34483e;
        }

        .form-control {
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #dce5e0;
        }

        .form-control:focus {
            border-color: var(--verde);
            box-shadow: 0 0 0 .2rem rgba(7,92,69,.12);
        }

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

        .login-link {
            color: var(--verde);
        }

        .login-link:hover {
            color: var(--verde-oscuro);
            text-decoration: underline !important;
        }

        /* PIE DE PÁGINA */

        .footer-flem {
            background: white;
            border-top: 3px solid var(--dorado);
            padding: 27px 18px;
            text-align: center;
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

            .register-card .card-body {
                padding: 23px;
            }

        }

    </style>

</head>

<body>

    <!-- ENCABEZADO -->

    <nav class="navbar navbar-flem">

        <div class="container">

            <a href="login.html" class="marca">

                <span class="brand-name">
                    FLEM <span class="brand-x">×</span>
                </span>

                <img
                    src="img/logo-hospital-clinicas.png"
                    alt="Logo Hospital de Clínicas"
                    class="logo-hospital"
                >

            </a>

        </div>

    </nav>


    <!-- REGISTRO -->

    <main class="container">

        <div class="row justify-content-center">

            <div class="col-12 col-md-10 col-lg-8">

                <div class="card register-card shadow-lg">

                    <div class="card-body">

                        <div class="text-center mb-4">

                            <div class="medical-symbol">
                                ⚕
                            </div>

                            <h1 class="flem-title">
                                Crear cuenta
                            </h1>

                            <p class="text-secondary">
                                Registro de paciente
                            </p>

                        </div>


                        <!-- FORMULARIO ORIGINAL -->

                        <form action="../BackEnd/registro.php" method="POST">

                            <div class="row">

                                <div class="col-md-6 mb-3">

                                    <label for="nombre" class="form-label fw-semibold">
                                        Nombre
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        id="nombre"
                                        name="nombre"
                                        placeholder="Ingresá tu nombre"
                                        required
                                    >

                                </div>


                                <div class="col-md-6 mb-3">

                                    <label for="apellido" class="form-label fw-semibold">
                                        Apellido
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        id="apellido"
                                        name="apellido"
                                        placeholder="Ingresá tu apellido"
                                        required
                                    >

                                </div>


                                <div class="col-md-6 mb-3">

                                    <label for="cedula" class="form-label fw-semibold">
                                        Cédula
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        id="cedula"
                                        name="cedula"
                                        placeholder="Ingresá tu cédula"
                                        required
                                    >

                                </div>


                                <div class="col-md-6 mb-3">

                                    <label for="edad" class="form-label fw-semibold">
                                        Edad
                                    </label>

                                    <input
                                        type="number"
                                        class="form-control"
                                        id="edad"
                                        name="edad"
                                        placeholder="Ingresá tu edad"
                                        min="0"
                                        max="120"
                                        required
                                    >

                                </div>


                                <div class="col-md-6 mb-3">

                                    <label for="telefono" class="form-label fw-semibold">
                                        Teléfono
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        id="telefono"
                                        name="telefono"
                                        placeholder="Ingresá tu teléfono"
                                        required
                                    >

                                </div>


                                <div class="col-md-6 mb-3">

                                    <label for="correo" class="form-label fw-semibold">
                                        Correo electrónico
                                    </label>

                                    <input
                                        type="email"
                                        class="form-control"
                                        id="correo"
                                        name="correo"
                                        placeholder="ejemplo@correo.com"
                                        required
                                    >

                                </div>


                                <div class="col-12 mb-4">

                                    <label for="password" class="form-label fw-semibold">
                                        Contraseña
                                    </label>

                                    <input
                                        type="password"
                                        class="form-control"
                                        id="password"
                                        name="password"
                                        placeholder="Ingresá una contraseña"
                                        required
                                    >

                                </div>

                            </div>


                            <div class="d-grid">

                                <button type="submit" class="btn btn-flem btn-lg">
                                    Crear cuenta
                                </button>

                            </div>

                        </form>


                        <!-- INICIAR SESIÓN -->

                        <div class="text-center mt-4">

                            <span class="text-secondary">
                                ¿Ya tenés una cuenta?
                            </span>

                            <a
                                href="login.html"
                                class="text-decoration-none fw-semibold login-link"
                            >
                                Iniciá sesión
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </main>


    <!-- PIE DE PÁGINA -->

    <footer class="footer-flem">

        <div class="footer-title">
            FLEM × Hospital de Clínicas
        </div>

        <p class="footer-description">
            Somos una empresa especializada en la digitalización de servicios mediante el desarrollo de páginas web y soluciones tecnológicas. Nuestro objetivo es optimizar procesos, facilitar el acceso a la información y mejorar la gestión de las organizaciones mediante herramientas modernas, accesibles y eficientes.
        </p>

        <div class="footer-copy">
            © 2026 FLEM. Todos los derechos reservados.
        </div>

    </footer>

</body>

</html>
