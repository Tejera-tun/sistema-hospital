<?php

session_start();

if (!isset($_SESSION["id"]) || ($_SESSION["rol"] ?? "") !== "Paciente") {
    header("Location: ../FrontEnd/login.html");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../FrontEnd/encuesta.php");
    exit;
}

require_once "conexion_encuestas.php";

if (!isset($_POST["id_encuesta"]) || !filter_var($_POST["id_encuesta"], FILTER_VALIDATE_INT)) {
    header("Location: ../FrontEnd/encuesta.php");
    exit;
}

$id_encuesta = (int)$_POST["id_encuesta"];
$id_usuario = (int)$_SESSION["id"];

$sql = "SELECT id_pregunta
        FROM preguntas
        WHERE id_encuesta = ?
        ORDER BY id_pregunta";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_encuesta);
$stmt->execute();

$preguntas = $stmt->get_result();

while ($pregunta = $preguntas->fetch_assoc()) {

    $id_pregunta = $pregunta["id_pregunta"];

    $nombre_campo = "pregunta_" . $id_pregunta;

    if (!isset($_POST[$nombre_campo])) {
        continue;
    }

    $respuesta = $_POST[$nombre_campo];

    if (is_numeric($respuesta)) {

        $id_opcion = (int)$respuesta;

        $respuesta_texto = null;

    } else {

        $id_opcion = null;

        $respuesta_texto = trim($respuesta);
    }

    $sql_insert = "INSERT INTO respuestas
                   (id_usuario, id_pregunta, id_opcion, respuesta_texto)
                   VALUES (?, ?, ?, ?)";

    $insert = $conexion->prepare($sql_insert);

    $insert->bind_param(
        "iiis",
        $id_usuario,
        $id_pregunta,
        $id_opcion,
        $respuesta_texto
    );

    $insert->execute();

    $insert->close();
}

$conexion->close();

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Encuesta enviada</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .mensaje {
            background-color: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        h1 {
            color: #087f5b;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 20px;
            background-color: #087f5b;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }

    </style>

</head>

<body>

<div class="mensaje">

    <h1>¡Gracias por responder!</h1>

    <p>
        Su respuesta fue registrada correctamente.
    </p>

    <a href="panel.php">
        Volver al panel
    </a>

</div>

</body>

</html>