<?php

session_start();

if (!isset($_SESSION["id"]) || ($_SESSION["rol"] ?? "") !== "Paciente") {
    header("Location: login.html");
    exit;
}

require_once "../BackEnd/conexion_encuestas.php";

$id_encuesta = 1;

$sql = "SELECT *
        FROM encuestas
        WHERE id_encuesta = ?
        AND estado = 'activa'";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_encuesta);
$stmt->execute();

$encuesta = $stmt->get_result()->fetch_assoc();

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

    <title><?php echo htmlspecialchars($encuesta["titulo"]); ?></title>

    <link rel="stylesheet" href="css/estilo.css">

    <style>

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
        }

        .contenedor {
            max-width: 750px;
            margin: 40px auto;
            background-color: white;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        h1 {
            color: #087f5b;
            text-align: center;
        }

        .descripcion {
            text-align: center;
            color: #555;
            margin-bottom: 35px;
        }

        .pregunta {
            margin-bottom: 30px;
        }

        .pregunta-titulo {
            display: block;
            font-weight: bold;
            margin-bottom: 12px;
            color: #333;
        }

        .opcion {
            display: block;
            background-color: #f3f3f3;
            padding: 12px;
            margin-bottom: 8px;
            border-radius: 8px;
            cursor: pointer;
        }

        .opcion:hover {
            background-color: #e5f4ef;
        }

        textarea {
            width: 100%;
            min-height: 120px;
            padding: 12px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 8px;
            resize: vertical;
        }

        .boton {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            background-color: #087f5b;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .boton:hover {
            background-color: #066b4d;
        }

    </style>

</head>

<body>

<div class="contenedor">

    <h1>
        <?php echo htmlspecialchars($encuesta["Pregunta"]); ?>
    </h1>

    <p class="descripcion">
        <?php echo htmlspecialchars($encuesta["descripcion"]); ?>
    </p>

    <form action="../BackEnd/respuestas.php" method="POST">

        <input
            type="hidden"
            name="id_encuesta"
            value="<?php echo $id_encuesta; ?>"
        >

        <?php while ($pregunta = $preguntas->fetch_assoc()): ?>

            <div class="pregunta">

                <span class="pregunta-titulo">

                    <?php echo htmlspecialchars($pregunta["pregunta"]); ?>

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
                                name="pregunta_<?php echo $pregunta["id_pregunta"]; ?>"
                                value="<?php echo $opcion["id_opcion"]; ?>"
                                required
                            >

                            <?php echo htmlspecialchars($opcion["texto"]); ?>

                        </label>

                    <?php endwhile; ?>

                <?php else: ?>

                    <textarea
                        name="pregunta_<?php echo $pregunta["id_pregunta"]; ?>"
                        placeholder="Escriba su respuesta..."
                        required
                    ></textarea>

                <?php endif; ?>

            </div>

        <?php endwhile; ?>

        <button class="boton" type="submit">
            Enviar encuesta
        </button>

    </form>

</div>

</body>

</html>

<?php

$conexion->close();

?>