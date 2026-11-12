
<?php

session_start();

if (
    !isset($_SESSION["id"]) ||
    ($_SESSION["rol"] ?? "") !== "Paciente"
) {
    header("Location: ../FrontEnd/login.html");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../FrontEnd/encuesta.php");
    exit;
}

require_once __DIR__ . "/conexion_encuestas.php";

function mostrarResultado($titulo, $mensaje, $exito = true)
{
    $color = $exito ? "#075c45" : "#b02a37";
    $fondo = $exito ? "#e4f2e9" : "#f8d7da";
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title><?= htmlspecialchars($titulo) ?> | FLEM</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
              rel="stylesheet">

        <style>
            :root {
                --verde: #075c45;
                --dorado: #d4af37;
                --fondo: #f4f8f6;
            }

            body {
                min-height: 100vh;
                margin: 0;
                background: var(--fondo);
                font-family: Arial, sans-serif;
                display: flex;
                flex-direction: column;
            }

            .navbar-flem {
                background: var(--verde);
                padding: 12px 0;
            }

            .marca {
                display: flex;
                align-items: center;
                gap: 12px;
                text-decoration: none;
            }

            .logo {
                width: 48px;
                height: 48px;
                object-fit: contain;
            }

            .marca-texto {
                color: white;
                font-size: 23px;
                font-weight: bold;
                letter-spacing: 2px;
            }

            .marca-texto span {
                color: var(--dorado);
            }

            .marca-texto small {
                display: block;
                font-size: 11px;
                font-weight: normal;
                letter-spacing: 1px;
            }

            .contenedor {
                flex: 1;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 25px 15px;
            }

            .tarjeta {
                width: 100%;
                max-width: 500px;
                padding: 40px 30px;
                background: white;
                border-radius: 17px;
                text-align: center;
                box-shadow: 0 5px 20px rgba(0,0,0,.07);
            }

            .icono {
                width: 75px;
                height: 75px;
                border-radius: 50%;
                display: flex;
                justify-content: center;
                align-items: center;
                margin: 0 auto 20px;
                background: <?= $fondo ?>;
                color: <?= $color ?>;
                font-size: 38px;
                font-weight: bold;
            }

            h1 {
                color: <?= $color ?>;
                font-size: 27px;
                font-weight: bold;
            }

            .mensaje {
                color: #68756e;
                line-height: 1.6;
                margin: 15px 0 25px;
            }

            .btn-flem {
                display: inline-block;
                background: var(--verde);
                color: white;
                text-decoration: none;
                padding: 12px 22px;
                border-radius: 8px;
                font-weight: bold;
            }

            .btn-flem:hover {
                background: #064735;
                color: white;
            }

            @media (max-width: 500px) {
                .logo {
                    width: 38px;
                    height: 38px;
                }

                .marca-texto {
                    font-size: 19px;
                }

                .marca-texto small {
                    font-size: 9px;
                }

                .tarjeta {
                    padding: 30px 20px;
                }
            }
        </style>
    </head>

    <body>

        <nav class="navbar-flem">
            <div class="container">

                <a href="../FrontEnd/login.html" class="marca">

                    <img
                        src="../FrontEnd/img/logo-hospital-clinicas.png"
                        alt="Hospital de Clínicas"
                        class="logo"
                    >

                    <span class="marca-texto">
                        <span>FLEM</span> ×
                        <small>HOSPITAL DE CLÍNICAS</small>
                    </span>

                </a>

            </div>
        </nav>

        <main class="contenedor">

            <div class="tarjeta">

                <div class="icono">
                    <?= $exito ? "✓" : "!" ?>
                </div>

                <h1><?= htmlspecialchars($titulo) ?></h1>

                <p class="mensaje">
                    <?= htmlspecialchars($mensaje) ?>
                </p>

                <a href="../BackEnd/panel.php" class="btn-flem">
                    Volver al panel
                </a>

            </div>

        </main>

    </body>
    </html>
    <?php
    exit;
}

// Validar identificador de encuesta
if (
    !isset($_POST["id_encuesta"]) ||
    !filter_var($_POST["id_encuesta"], FILTER_VALIDATE_INT)
) {
    mostrarResultado(
        "Encuesta no válida",
        "No se pudo identificar la encuesta.",
        false
    );
}

$id_encuesta = (int) $_POST["id_encuesta"];
$id_usuario = (int) $_SESSION["id"];

try {

    // Comprobar que la encuesta exista y esté activa
    $stmt = $conexion->prepare(
        "SELECT id_encuesta
         FROM encuestas
         WHERE id_encuesta = ?
         AND estado = 'activa'"
    );

    $stmt->bind_param("i", $id_encuesta);
    $stmt->execute();

    $encuesta = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$encuesta) {
        throw new Exception("La encuesta no está disponible.");
    }

    // Obtener las preguntas de la encuesta
    $stmt = $conexion->prepare(
        "SELECT id_pregunta
         FROM preguntas
         WHERE id_encuesta = ?
         ORDER BY id_pregunta"
    );

    $stmt->bind_param("i", $id_encuesta);
    $stmt->execute();

    $resultado = $stmt->get_result();

    $lista_preguntas = [];

    while ($pregunta = $resultado->fetch_assoc()) {
        $lista_preguntas[] = (int) $pregunta["id_pregunta"];
    }

    $stmt->close();

    if (count($lista_preguntas) === 0) {
        throw new Exception("La encuesta todavía no tiene preguntas.");
    }

    $respuestas = [];

    // Validar cada respuesta antes de guardarla
    foreach ($lista_preguntas as $id_pregunta) {

        $nombre_campo = "pregunta_" . $id_pregunta;

        if (
            !isset($_POST[$nombre_campo]) ||
            !is_string($_POST[$nombre_campo])
        ) {
            throw new Exception(
                "Debés responder todas las preguntas de la encuesta."
            );
        }

        $valor = trim($_POST[$nombre_campo]);

        if ($valor === "") {
            throw new Exception(
                "No podés enviar preguntas sin responder."
            );
        }

        $id_opcion = null;
        $respuesta_texto = null;

        // Comprobar si el valor corresponde a una opción existente
        if (ctype_digit($valor)) {

            $posible_opcion = (int) $valor;

            $check = $conexion->prepare(
                "SELECT id_opcion
                 FROM opciones
                 WHERE id_opcion = ?
                 AND id_pregunta = ?"
            );

            $check->bind_param(
                "ii",
                $posible_opcion,
                $id_pregunta
            );

            $check->execute();

            $opcion = $check->get_result()->fetch_assoc();

            $check->close();

            if ($opcion) {
                $id_opcion = $posible_opcion;
            } else {
                // Si no es una opción válida, se interpreta como texto
                $respuesta_texto = $valor;
            }

        } else {

            $respuesta_texto = $valor;

        }

        // Si la pregunta tiene opciones, debe seleccionarse una
        $check = $conexion->prepare(
            "SELECT COUNT(*) AS total
             FROM opciones
             WHERE id_pregunta = ?"
        );

        $check->bind_param("i", $id_pregunta);
        $check->execute();

        $total_opciones = (int) $check->get_result()->fetch_assoc()["total"];

        $check->close();

        if ($total_opciones > 0 && $id_opcion === null) {
            throw new Exception(
                "Una de las respuestas seleccionadas no es válida."
            );
        }

        if ($total_opciones === 0 && $respuesta_texto === null) {
            throw new Exception(
                "Debés escribir una respuesta para todas las preguntas."
            );
        }

        $respuestas[] = [
            "id_pregunta" => $id_pregunta,
            "id_opcion" => $id_opcion,
            "respuesta_texto" => $respuesta_texto
        ];
    }

    // Guardar todas las respuestas en una transacción
    $conexion->begin_transaction();

    $insert = $conexion->prepare(
        "INSERT INTO respuestas
         (id_usuario, id_pregunta, id_opcion, respuesta_texto)
         VALUES (?, ?, ?, ?)"
    );

    foreach ($respuestas as $respuesta) {

        $id_pregunta = $respuesta["id_pregunta"];
        $id_opcion = $respuesta["id_opcion"];
        $respuesta_texto = $respuesta["respuesta_texto"];

        $insert->bind_param(
            "iiis",
            $id_usuario,
            $id_pregunta,
            $id_opcion,
            $respuesta_texto
        );

        $insert->execute();
    }

    $insert->close();

    $conexion->commit();
    $conexion->close();

    mostrarResultado(
        "¡Gracias por responder!",
        "Tus respuestas fueron registradas correctamente. Tu opinión nos ayuda a mejorar la atención del hospital."
    );

} catch (Throwable $e) {

    try {
        $conexion->rollback();
    } catch (Throwable $ignorado) {
    }

    $conexion->close();

    mostrarResultado(
        "No se pudo enviar la encuesta",
        $e instanceof mysqli_sql_exception
            ? "Ocurrió un problema al guardar tus respuestas. Intentá nuevamente."
            : $e->getMessage(),
        false
    );
}

?>
