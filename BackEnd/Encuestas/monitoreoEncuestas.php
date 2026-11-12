
<?php

session_start();

if (
    !isset($_SESSION["id"]) ||
    ($_SESSION["rol"] ?? "") !== "Administrador"
) {
    header("Location: ../../FrontEnd/login.html");
    exit;
}

require_once __DIR__ . "/conexion_encuestas.php";

function h($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

$encuestas = [];
$preguntasPorEncuesta = [];
$respuestasIndividuales = [];

$totalRespuestas = 0;
$totalParticipantes = 0;
$error = "";

try {

    // RESUMEN GENERAL

    $sqlResumen = "
        SELECT
            COUNT(*) AS total_respuestas,
            COUNT(DISTINCT r.id_usuario) AS total_participantes
        FROM respuestas r
        INNER JOIN preguntas p
            ON p.id_pregunta = r.id_pregunta
        INNER JOIN encuestas e
            ON e.id_encuesta = p.id_encuesta
    ";

    $resumen = $conexion->query($sqlResumen)->fetch_assoc();

    $totalRespuestas = (int)($resumen["total_respuestas"] ?? 0);
    $totalParticipantes = (int)($resumen["total_participantes"] ?? 0);

    // OBTENER ENCUESTAS

    $resultadoEncuestas = $conexion->query(
        "SELECT
            id_encuesta,
            `Pregunta` AS titulo,
            descripcion,
            estado
         FROM encuestas
         ORDER BY id_encuesta"
    );

    while ($fila = $resultadoEncuestas->fetch_assoc()) {
        $encuestas[] = $fila;
    }

    // OBTENER PREGUNTAS

    $sqlPreguntas = "
        SELECT
            p.id_pregunta,
            p.pregunta,
            p.id_encuesta
        FROM preguntas p
        ORDER BY p.id_encuesta, p.id_pregunta
    ";

    $resultadoPreguntas = $conexion->query($sqlPreguntas);

    while ($pregunta = $resultadoPreguntas->fetch_assoc()) {

        $idPregunta = (int)$pregunta["id_pregunta"];

        // OBTENER OPCIONES Y CANTIDAD DE RESPUESTAS

        $stmtOpciones = $conexion->prepare("
            SELECT
                o.id_opcion,
                o.texto,
                COUNT(r.id_usuario) AS cantidad
            FROM opciones o
            LEFT JOIN respuestas r
                ON r.id_opcion = o.id_opcion
            WHERE o.id_pregunta = ?
            GROUP BY o.id_opcion, o.texto
            ORDER BY o.id_opcion
        ");

        $stmtOpciones->bind_param("i", $idPregunta);
        $stmtOpciones->execute();

        $resultadoOpciones = $stmtOpciones->get_result();

        $opciones = [];
        $totalPregunta = 0;

        while ($opcion = $resultadoOpciones->fetch_assoc()) {

            $opcion["cantidad"] = (int)$opcion["cantidad"];

            $totalPregunta += $opcion["cantidad"];

            $opciones[] = $opcion;
        }

        $stmtOpciones->close();

        // RESPUESTAS ABIERTAS

        $textos = [];

        if (count($opciones) === 0) {

            $stmtTextos = $conexion->prepare("
                SELECT respuesta_texto
                FROM respuestas
                WHERE id_pregunta = ?
                  AND respuesta_texto IS NOT NULL
                  AND respuesta_texto <> ''
                ORDER BY id_usuario
            ");

            $stmtTextos->bind_param("i", $idPregunta);
            $stmtTextos->execute();

            $resultadoTextos = $stmtTextos->get_result();

            while ($texto = $resultadoTextos->fetch_assoc()) {
                $textos[] = $texto["respuesta_texto"];
            }

            $stmtTextos->close();

            $totalPregunta = count($textos);
        }

        $idEncuesta = (int)$pregunta["id_encuesta"];

        $preguntasPorEncuesta[$idEncuesta][] = [
            "id" => $idPregunta,
            "pregunta" => $pregunta["pregunta"],
            "opciones" => $opciones,
            "textos" => $textos,
            "total" => $totalPregunta
        ];
    }

    // RESPUESTAS INDIVIDUALES

    $sqlIndividuales = "
        SELECT
            r.id_usuario,
            p.pregunta,
            COALESCE(o.texto, r.respuesta_texto) AS respuesta
        FROM respuestas r
        INNER JOIN preguntas p
            ON p.id_pregunta = r.id_pregunta
        LEFT JOIN opciones o
            ON o.id_opcion = r.id_opcion
        ORDER BY r.id_usuario DESC, p.id_pregunta
        LIMIT 200
    ";

    $resultadoIndividuales = $conexion->query($sqlIndividuales);

    while ($fila = $resultadoIndividuales->fetch_assoc()) {
        $respuestasIndividuales[] = $fila;
    }

    $conexion->close();

} catch (Throwable $e) {

    $error = "No se pudieron cargar los resultados. Verificá la estructura de la base de datos y la conexión de encuestas.";

}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>FLEM | Monitoreo de encuestas</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>

        :root {
            --verde: #075c45;
            --verde-oscuro: #064735;
            --dorado: #d4af37;
            --fondo: #f4f8f6;
        }

        body {
            background: var(--fondo);
            color: #26352f;
            font-family: Arial, sans-serif;
        }

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

        .marca strong {
            color: var(--dorado);
            letter-spacing: 2px;
            font-size: 24px;
        }

        .logo {
            width: 105px;
            height: 52px;
            object-fit: contain;
        }

        .hero {
            background: linear-gradient(135deg, var(--verde), #0a7658);
            color: white;
            border-radius: 18px;
        }

        .hero small {
            color: var(--dorado);
            font-weight: bold;
            letter-spacing: 1px;
        }

        .stat-card,
        .panel-card {
            border: 0;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0,0,0,.06);
        }

        .stat-number {
            color: var(--verde);
            font-size: 2rem;
            font-weight: 800;
        }

        .section-title {
            color: var(--verde);
            font-weight: 800;
        }

        .btn-flem {
            background: var(--verde);
            color: white;
            border: 0;
        }

        .btn-flem:hover {
            background: var(--verde-oscuro);
            color: white;
        }

        .chart-wrap {
            position: relative;
            min-height: 260px;
        }

        .footer {
            background: white;
            border-top: 3px solid var(--dorado);
            color: #66736c;
        }

    </style>

</head>

<body>

<!-- ENCABEZADO -->

<nav class="navbar navbar-flem">

    <div class="container d-flex justify-content-between align-items-center">

        <a href="../panel.php" class="marca">

            <strong>FLEM ×</strong>

            <img
                src="../../FrontEnd/img/logo-hospital-clinicas.png"
                alt="Hospital de Clínicas"
                class="logo"
            >

        </a>

        <a href="../panel.php" class="btn btn-light">
            Volver al panel
        </a>

    </div>

</nav>

<main class="container py-5">

    <!-- PRESENTACIÓN -->

    <div class="hero p-4 p-md-5 mb-4">

        <small>SISTEMA HOSPITALARIO</small>

        <h1 class="fw-bold mt-2">
            Monitoreo de encuestas
        </h1>

        <p class="mb-0">
            Consultá las respuestas recibidas y analizá los resultados de satisfacción.
        </p>

    </div>

    <?php if ($error !== ""): ?>

        <div class="alert alert-danger">
            <?= h($error) ?>
        </div>

    <?php else: ?>

        <!-- ESTADÍSTICAS GENERALES -->

        <div class="row g-3 mb-5">

            <div class="col-md-6">

                <div class="card stat-card h-100">

                    <div class="card-body p-4">

                        <div class="text-secondary">
                            Respuestas registradas
                        </div>

                        <div class="stat-number">
                            <?= $totalRespuestas ?>
                        </div>

                    </div>

                </div>

            </div>

            <div class="col-md-6">

                <div class="card stat-card h-100">

                    <div class="card-body p-4">

                        <div class="text-secondary">
                            Pacientes participantes
                        </div>

                        <div class="stat-number">
                            <?= $totalParticipantes ?>
                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!-- RESULTADOS -->

        <h2 class="section-title mb-3">
            Resultados por encuesta
        </h2>

        <?php if (!$encuestas): ?>

            <div class="alert alert-info">
                Todavía no hay encuestas registradas.
            </div>

        <?php endif; ?>

        <?php foreach ($encuestas as $encuesta): ?>

            <?php
                $idEncuesta = (int)$encuesta["id_encuesta"];
                $preguntas = $preguntasPorEncuesta[$idEncuesta] ?? [];
            ?>

            <section class="card panel-card mb-4">

                <div class="card-body p-4">

                    <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">

                        <div>

                            <h3 class="h4 fw-bold mb-1">
                                <?= h($encuesta["titulo"]) ?>
                            </h3>

                            <p class="text-secondary mb-0">
                                <?= h($encuesta["descripcion"] ?? "") ?>
                            </p>

                        </div>

                        <span class="badge text-bg-<?= strtolower($encuesta["estado"]) === "activa" ? "success" : "secondary" ?> align-self-start">

                            <?= h($encuesta["estado"]) ?>

                        </span>

                    </div>

                    <?php if (!$preguntas): ?>

                        <p class="text-secondary mb-0">
                            Esta encuesta todavía no tiene preguntas.
                        </p>

                    <?php else: ?>

                        <?php foreach ($preguntas as $pregunta): ?>

                            <div class="border rounded-3 p-3 mb-3">

                                <div class="d-flex justify-content-between flex-wrap gap-2">

                                    <h4 class="h6 fw-bold">
                                        <?= h($pregunta["pregunta"]) ?>
                                    </h4>

                                    <span class="badge text-bg-light">
                                        <?= $pregunta["total"] ?> respuestas
                                    </span>

                                </div>

                                <?php if ($pregunta["opciones"]): ?>

                                    <!-- GRÁFICO -->

                                    <div class="chart-wrap mt-3">

                                        <canvas id="grafico-<?= $pregunta["id"] ?>"></canvas>

                                    </div>

                                    <!-- TABLA DE RESULTADOS -->

                                    <div class="table-responsive mt-3">

                                        <table class="table table-sm align-middle">

                                            <thead>
                                                <tr>
                                                    <th>Opción</th>
                                                    <th class="text-end">Respuestas</th>
                                                </tr>
                                            </thead>

                                            <tbody>

                                                <?php foreach ($pregunta["opciones"] as $opcion): ?>

                                                    <tr>

                                                        <td>
                                                            <?= h($opcion["texto"]) ?>
                                                        </td>

                                                        <td class="text-end fw-semibold">
                                                            <?= $opcion["cantidad"] ?>
                                                        </td>

                                                    </tr>

                                                <?php endforeach; ?>

                                            </tbody>

                                        </table>

                                    </div>

                                    <!-- CONFIGURACIÓN DEL GRÁFICO -->

                                    <script>

                                    new Chart(
                                        document.getElementById('grafico-<?= $pregunta["id"] ?>'),
                                        {
                                            type: 'bar',

                                            data: {

                                                labels: <?= json_encode(
                                                    array_column($pregunta["opciones"], "texto"),
                                                    JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
                                                ) ?>,

                                                datasets: [{

                                                    label: 'Cantidad de respuestas',

                                                    data: <?= json_encode(
                                                        array_column($pregunta["opciones"], "cantidad")
                                                    ) ?>,

                                                    backgroundColor: '#0a7658',

                                                    borderColor: '#075c45',

                                                    borderWidth: 1,

                                                    borderRadius: 6

                                                }]

                                            },

                                            options: {

                                                responsive: true,

                                                maintainAspectRatio: false,

                                                indexAxis: 'y',

                                                plugins: {
                                                    legend: {
                                                        display: false
                                                    }
                                                },

                                                scales: {

                                                    x: {
                                                        beginAtZero: true,

                                                        ticks: {
                                                            precision: 0
                                                        }
                                                    }

                                                }

                                            }

                                        }
                                    );

                                    </script>

                                <?php else: ?>

                                    <!-- RESPUESTAS ABIERTAS -->

                                    <?php if ($pregunta["textos"]): ?>

                                        <div class="list-group mt-2">

                                            <?php foreach ($pregunta["textos"] as $texto): ?>

                                                <div class="list-group-item">

                                                    <?= nl2br(h($texto)) ?>

                                                </div>

                                            <?php endforeach; ?>

                                        </div>

                                    <?php else: ?>

                                        <p class="text-secondary mb-0">
                                            Todavía no hay respuestas para esta pregunta.
                                        </p>

                                    <?php endif; ?>

                                <?php endif; ?>

                            </div>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>

            </section>

        <?php endforeach; ?>

        <!-- RESPUESTAS INDIVIDUALES -->

        <section class="card panel-card mt-5">

            <div class="card-body p-4">

                <h2 class="section-title h4 mb-3">
                    Respuestas individuales
                </h2>

                <p class="text-secondary">
                    Se muestran hasta 200 respuestas. Para proteger la privacidad,
                    se identifica al participante por su número interno y no se muestran sus datos personales.
                </p>

                <?php if (!$respuestasIndividuales): ?>

                    <div class="alert alert-info mb-0">
                        Todavía no hay respuestas registradas.
                    </div>

                <?php else: ?>

                    <div class="table-responsive">

                        <table class="table table-striped table-hover align-middle">

                            <thead>
                                <tr>
                                    <th>Participante (ID)</th>
                                    <th>Pregunta</th>
                                    <th>Respuesta</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php foreach ($respuestasIndividuales as $respuesta): ?>

                                    <tr>

                                        <td>
                                            <?= (int)$respuesta["id_usuario"] ?>
                                        </td>

                                        <td>
                                            <?= h($respuesta["pregunta"]) ?>
                                        </td>

                                        <td>
                                            <?= nl2br(h($respuesta["respuesta"] ?? "")) ?>
                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php endif; ?>

            </div>

        </section>

    <?php endif; ?>

</main>

<!-- PIE DE PÁGINA -->

<footer class="footer text-center py-4 mt-5">

    <strong style="color:#075c45;">
        FLEM × Hospital de Clínicas
    </strong>

    <p class="mb-0 mt-2 small">
        Monitoreo de encuestas | © <?= date("Y") ?> FLEM
    </p>

</footer>

</body>
</html>
