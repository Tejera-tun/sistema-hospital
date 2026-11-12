
<?php

session_start();

if (!isset($_SESSION["id"]) || ($_SESSION["rol"] ?? "") !== "Paciente") {
    header("Location: login.html");
    exit;
}

require_once __DIR__ . "/../BackEnd/Encuestas/conexion_encuestas.php";

$id_encuesta = 1;

// Obtener encuesta activa
$sql = "SELECT *
        FROM encuestas
        WHERE id_encuesta = ?
        AND estado = 'activa'";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_encuesta);
$stmt->execute();

$encuesta = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$encuesta) {
    die("La encuesta no está disponible.");
}

// Obtener preguntas
$sql = "SELECT *
        FROM preguntas
        WHERE id_encuesta = ?
        ORDER BY id_pregunta";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_encuesta);
$stmt->execute();

$preguntas = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($encuesta["Pregunta"], ENT_QUOTES, "UTF-8"); ?>
    </title>

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
            margin: 0;
            font-family: Arial, sans-serif;
            background: var(--fondo);
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
            font-size: 27px;
            font-weight: bold;
            letter-spacing: 2px;
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

        .btn-volver {
            color: white;
            background: transparent;
            border: 1px solid rgba(255,255,255,.6);
            border-radius: 8px;
            padding: 9px 15px;
            text-decoration: none;
            transition: .2s;
        }

        .btn-volver:hover {
            background: white;
            color: var(--verde);
        }

        /* CONTENIDO */

        .contenedor {
            max-width: 850px;
            margin: 40px auto;
            padding: 0 18px;
        }

        .encabezado {
            background: linear-gradient(135deg, var(--verde), #0a7658);
            color: white;
            padding: 35px;
            border-radius: 17px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,.10);
        }

        .encabezado small {
            color: var(--dorado);
            font-weight: bold;
            letter-spacing: 1px;
        }

        .encabezado h1 {
            font-size: 30px;
            font-weight: bold;
            margin-top: 12px;
        }

        .encabezado p {
            color: #e7f1ed;
            margin-bottom: 0;
        }

        /* FORMULARIO */

        .formulario {
            background: white;
            padding: 30px;
            border-radius: 17px;
            box-shadow: 0 4px 14px rgba(0,0,0,.06);
        }

        .pregunta {
            background: white;
            border: 1px solid #e4ebe7;
            border-left: 4px solid #08775a;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .pregunta-titulo {
            display: block;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 15px;
            color: var(--verde);
        }

        .opcion {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--fondo);
            padding: 13px 15px;
            margin-bottom: 9px;
            border-radius: 9px;
            cursor: pointer;
            transition: .2s;
        }

        .opcion:hover {
            background: #e5f2ec;
        }

        .opcion input {
            accent-color: #08775a;
            width: 17px;
            height: 17px;
        }

        textarea {
            width: 100%;
            min-height: 120px;
            padding: 13px;
            border: 1px solid #ced9d3;
            border-radius: 9px;
            resize: vertical;
        }

        textarea:focus {
            outline: none;
            border-color: #08775a;
            box-shadow: 0 0 0 3px rgba(8,119,90,.12);
        }

        .btn-flem {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 9px;
            background: var(--verde);
            color: white;
            font-size: 16px;
            font-weight: bold;
            transition: .2s;
        }

        .btn-flem:hover {
            background: var(--verde-oscuro);
            color: white;
        }

        /* PIE DE PÁGINA */

        .footer-flem {
            text-align: center;
            background: white;
            border-top: 3px solid var(--dorado);
            margin-top: 55px;
            padding: 30px 18px;
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

        /* RESPONSIVE */

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

            .encabezado {
                padding: 23px;
            }

            .encabezado h1 {
                font-size: 24px;
            }

            .formulario {
                padding: 20px;
            }

        }

    </style>

</head>

<body>

    <!-- ENCABEZADO -->

    <nav class="navbar navbar-flem">

        <div class="container d-flex justify-content-between align-items-center">

            <a href="../BackEnd/panel.php" class="marca">

                <span class="brand-name">
                    FLEM <span class="brand-x">×</span>
                </span>

                <img
                    src="img/logo-hospital-clinicas.png"
                    alt="Logo Hospital de Clínicas"
                    class="logo-hospital"
                >

            </a>

            <a href="../BackEnd/panel.php" class="btn-volver">
                Volver al panel
            </a>

        </div>

    </nav>

    <main class="contenedor">

        <!-- INFORMACIÓN DE LA ENCUESTA -->

        <div class="encabezado">

            <small>SISTEMA HOSPITALARIO</small>

            <h1>
                <?= htmlspecialchars($encuesta["Pregunta"], ENT_QUOTES, "UTF-8"); ?>
            </h1>

            <p>
                <?= htmlspecialchars($encuesta["descripcion"] ?? "", ENT_QUOTES, "UTF-8"); ?>
            </p>

        </div>

        <!-- FORMULARIO DE ENCUESTA -->

        <div class="formulario">

            <div class="mb-4">

                <h4 class="fw-bold" style="color:#075c45;">
                    Encuesta de satisfacción
                </h4>

                <p class="text-secondary">
                    Tu opinión nos ayuda a mejorar la atención del hospital.
                </p>

            </div>

            <!-- RUTA CORREGIDA -->
            <form action="../BackEnd/Encuestas/respuestas.php" method="POST">

                <input
                    type="hidden"
                    name="id_encuesta"
                    value="<?= (int)$id_encuesta; ?>"
                >

                <?php if ($preguntas->num_rows > 0): ?>

                    <?php while ($pregunta = $preguntas->fetch_assoc()): ?>

                        <div class="pregunta">

                            <span class="pregunta-titulo">
                                <?= htmlspecialchars($pregunta["pregunta"], ENT_QUOTES, "UTF-8"); ?>
                            </span>

                            <?php

                            $sql_opciones = "SELECT *
                                             FROM opciones
                                             WHERE id_pregunta = ?
                                             ORDER BY id_opcion";

                            $stmt_opciones = $conexion->prepare($sql_opciones);

                            $stmt_opciones->bind_param(
                                "i",
                                $pregunta["id_pregunta"]
                            );

                            $stmt_opciones->execute();

                            $opciones = $stmt_opciones->get_result();

                            ?>

                            <?php if ($opciones->num_rows > 0): ?>

                                <?php while ($opcion = $opciones->fetch_assoc()): ?>

                                    <label class="opcion">

                                        <input
                                            type="radio"
                                            name="pregunta_<?= (int)$pregunta["id_pregunta"]; ?>"
                                            value="<?= (int)$opcion["id_opcion"]; ?>"
                                            required
                                        >

                                        <span>
                                            <?= htmlspecialchars($opcion["texto"], ENT_QUOTES, "UTF-8"); ?>
                                        </span>

                                    </label>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <textarea
                                    name="pregunta_<?= (int)$pregunta["id_pregunta"]; ?>"
                                    placeholder="Escribí tu respuesta..."
                                    required
                                ></textarea>

                            <?php endif; ?>

                            <?php $stmt_opciones->close(); ?>

                        </div>

                    <?php endwhile; ?>

                <?php else: ?>

                    <div class="alert alert-info">
                        Todavía no hay preguntas disponibles en esta encuesta.
                    </div>

                <?php endif; ?>

                <?php if ($preguntas->num_rows > 0): ?>

                    <button type="submit" class="btn btn-flem">
                        Enviar encuesta
                    </button>

                <?php endif; ?>

            </form>

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
            © <?= date("Y"); ?> FLEM. Todos los derechos reservados.
        </div>

    </footer>

</body>

</html>

<?php

$stmt->close();
$conexion->close();

?>
111