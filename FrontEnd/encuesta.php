
<?php

session_start();

if (!isset($_SESSION["id"]) || ($_SESSION["rol"] ?? "") !== "Paciente") {
    header("Location: login.html");
    exit;
}

require_once __DIR__ . "/../BackEnd/conexion_encuestas.php";

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

        .navbar-flem {
            background: var(--verde);
            padding: 12px 0;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .logo-hospital {
            width: 48px;
            height: 48px;
            object-fit: contain;
        }

        .brand-text {
            color: white;
            font-size: 23px;
            font-weight: bold;
            letter-spacing: 2px;
        }

        .brand-text .flem {
            color: var(--dorado);
        }

        .brand-text .cruz {
            color: var(--dorado);
            margin: 0 3px;
        }

        .brand-text small {
            display: block;
            font-size: 11px;
            font-weight: normal;
            letter-spacing: 1px;
        }

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

        .btn-volver {
            color: white;
            background: transparent;
            border: 1px solid rgba(255,255,255,.6);
            border-radius: 8px;
            padding: 9px 15px;
            text-decoration: none;
        }

        .btn-volver:hover {
            background: white;
            color: var(--verde);
        }

        footer {
            text-align: center;
            color: #78847e;
            padding: 25px;
        }

        footer strong {
            color: var(--verde);
        }

        footer span {
            color: var(--dorado);
        }

        @media (max-width: 600px) {
            .logo-hospital {
                width: 38px;
                height: 38px;
            }

            .brand-text {
                font-size: 19px;
            }

            .brand-text small {
                font-size: 9px;
            }

            .btn-volver {
                padding: 8px 10px;
                font-size: 13px;
            }

            .encabezado {
                padding: 23px;
            }

            .encabezado h1 {
                font-size: 24px;
            }

            .formulario {
                padding: 18px;
            }
        }

    </style>

</head>

<body>

    <nav class="navbar navbar-flem">

        <div class="container d-flex justify-content-between align-items-center">

            <a href="../BackEnd/panel.php" class="brand">

                <img
                    src="img/logo-hospital-clinicas.png"
                    alt="Logo Hospital de Clínicas"
                    class="logo-hospital"
                >

                <span class="brand-text">
                    <span class="flem">FLEM</span><span class="cruz">×</span>
                    <small>HOSPITAL DE CLÍNICAS</small>
                </span>

            </a>

            <a href="../BackEnd/panel.php" class="btn-volver">
                Volver al panel
            </a>

        </div>

    </nav>

    <main class="contenedor">

        <div class="encabezado">

            <small>SISTEMA HOSPITALARIO</small>

            <h1>
                <?= htmlspecialchars($encuesta["Pregunta"], ENT_QUOTES, "UTF-8"); ?>
            </h1>

            <p>
                <?= htmlspecialchars($encuesta["descripcion"] ?? "", ENT_QUOTES, "UTF-8"); ?>
            </p>

        </div>

        <div class="formulario">

            <div class="mb-4">

                <h4 class="fw-bold" style="color:#075c45;">
                    Encuesta de satisfacción
                </h4>

                <p class="text-secondary">
                    Tu opinión nos ayuda a mejorar la atención del hospital.
                </p>

            </div>

            <form action="../BackEnd/respuestas.php" method="POST">

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

    <footer>
        <strong><span>FLEM</span> × Hospital de Clínicas</strong>
        <div>Sistema Hospitalario</div>
    </footer>

</body>

</html>

<?php

$stmt->close();
$conexion->close();

?>
