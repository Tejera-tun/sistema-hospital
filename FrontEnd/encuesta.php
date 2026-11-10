
<?php

session_start();

if (!isset($_SESSION["id"]) || ($_SESSION["rol"] ?? "") !== "Paciente") {
    header("Location: login.html");
    exit;
}

require_once __DIR__ . "/../BackEnd/conexion_encuestas.php";

$id_encuesta = 1;

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

    <title>Encuesta de satisfacción | FLEM</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f7f5;
            color: #26352f;
        }

        .navbar-flem {
            background: #075b46;
            padding: 17px 0;
        }

        .logo-flem {
            color: white;
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 2px;
            text-decoration: none;
        }

        .logo-flem:hover {
            color: #e8b832;
        }

        .btn-volver {
            background: white;
            color: #075b46;
            padding: 9px 16px;
            border-radius: 7px;
            text-decoration: none;
            font-weight: bold;
        }

        .btn-volver:hover {
            background: #e8f1ed;
            color: #075b46;
        }

        .contenedor {
            max-width: 850px;
            margin: 45px auto;
            padding: 0 18px;
        }

        .encabezado {
            background: linear-gradient(120deg, #075b46, #087b5b);
            color: white;
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 25px;
            box-shadow: 0 5px 14px rgba(0,0,0,0.10);
        }

        .encabezado small {
            color: #e8b832;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .encabezado h1 {
            font-weight: bold;
            margin-top: 10px;
        }

        .encabezado p {
            margin-bottom: 0;
            color: #edf6f2;
        }

        .tarjeta {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.06);
        }

        .pregunta {
            padding: 22px;
            margin-bottom: 20px;
            background: #f8faf9;
            border: 1px solid #e5ece8;
            border-left: 5px solid #075b46;
            border-radius: 10px;
        }

        .pregunta-titulo {
            display: block;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 16px;
            color: #075b46;
        }

        .opcion {
            display: flex;
            align-items: center;
            gap: 10px;
            background: white;
            border: 1px solid #e2e9e5;
            padding: 13px 15px;
            margin-bottom: 9px;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.2s;
        }

        .opcion:hover {
            background: #edf6f1;
            border-color: #075b46;
        }

        .opcion input {
            accent-color: #075b46;
            width: 17px;
            height: 17px;
            cursor: pointer;
        }

        textarea {
            width: 100%;
            min-height: 120px;
            padding: 14px;
            box-sizing: border-box;
            border: 1px solid #d5dfd9;
            border-radius: 9px;
            resize: vertical;
            font-family: Arial, sans-serif;
        }

        textarea:focus {
            outline: none;
            border-color: #075b46;
            box-shadow: 0 0 0 3px rgba(7,91,70,0.10);
        }

        .boton {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 9px;
            background: #075b46;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s;
        }

        .boton:hover {
            background: #064735;
            transform: translateY(-1px);
        }

        .pie {
            text-align: center;
            color: #82918a;
            font-size: 13px;
            margin-top: 25px;
        }

        @media (max-width: 600px) {
            .contenedor {
                margin-top: 25px;
            }

            .encabezado {
                padding: 23px;
            }

            .tarjeta {
                padding: 18px;
            }

            .pregunta {
                padding: 17px;
            }
        }

    </style>

</head>

<body>

<nav class="navbar-flem">
    <div class="container d-flex justify-content-between align-items-center">

        <a href="../BackEnd/panel.php" class="logo-flem">
            ⚕ FLEM
        </a>

        <a href="../BackEnd/panel.php" class="btn-volver">
            Volver al panel
        </a>

    </div>
</nav>

<div class="contenedor">

    <div class="encabezado">

        <small>SISTEMA HOSPITALARIO</small>

        <h1>
            <?= htmlspecialchars($encuesta["titulo"]) ?>
        </h1>

        <p>
            <?= htmlspecialchars($encuesta["descripcion"]) ?>
        </p>

    </div>

    <div class="tarjeta">

        <h4 class="fw-bold mb-4" style="color: #075b46;">
            Tu opinión es importante
        </h4>

        <form action="../BackEnd/respuestas.php" method="POST">

            <input
                type="hidden"
                name="id_encuesta"
                value="<?= $id_encuesta ?>"
            >

            <?php while ($pregunta = $preguntas->fetch_assoc()): ?>

                <div class="pregunta">

                    <span class="pregunta-titulo">
                        <?= htmlspecialchars($pregunta["pregunta"]) ?>
                    </span>

                    <?php

                    $sql = "SELECT *
                            FROM opciones
                            WHERE id_pregunta = ?
                            ORDER BY id_opcion";

                    $stmt_opciones = $conexion->prepare($sql);

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
                                    name="pregunta_<?= (int) $pregunta["id_pregunta"] ?>"
                                    value="<?= (int) $opcion["id_opcion"] ?>"
                                    required
                                >

                                <span>
                                    <?= htmlspecialchars($opcion["texto"]) ?>
                                </span>

                            </label>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <textarea
                            name="pregunta_<?= (int) $pregunta["id_pregunta"] ?>"
                            placeholder="Escribí tu respuesta..."
                            required
                        ></textarea>

                    <?php endif; ?>

                    <?php $stmt_opciones->close(); ?>

                </div>

            <?php endwhile; ?>

            <button class="boton" type="submit">
                Enviar encuesta
            </button>

        </form>

    </div>

    <div class="pie">
        FLEM - Sistema Hospitalario
    </div>

</div>

</body>
</html>

<?php
$stmt->close();
$conexion->close();
?>

