
<?php
session_start();

if (!isset($_SESSION["id"]) || ($_SESSION["rol"] ?? "") !== "Administrador") {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . "/../conexion_encuestas.php";

$id_encuesta = 1;

if (empty($_SESSION["token_encuestas"])) {
    $_SESSION["token_encuestas"] = bin2hex(random_bytes(32));
}

$mensaje = "";
$tipo_mensaje = "success";

// Procesar acciones
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        !isset($_POST["token"]) ||
        !hash_equals($_SESSION["token_encuestas"], $_POST["token"])
    ) {
        die("Solicitud no válida.");
    }

    $accion = $_POST["accion"] ?? "";

    try {

        // AGREGAR PREGUNTA
        if ($accion === "crear_pregunta") {

            $pregunta = trim($_POST["pregunta"] ?? "");

            if ($pregunta === "") {
                throw new Exception("Escribí el texto de la pregunta.");
            }

            $stmt = $conexion->prepare(
                "INSERT INTO preguntas (id_encuesta, pregunta)
                 VALUES (?, ?)"
            );

            $stmt->bind_param("is", $id_encuesta, $pregunta);
            $stmt->execute();
            $id_nueva = $stmt->insert_id;
            $stmt->close();

            $opciones = $_POST["opciones"] ?? [];

            foreach ($opciones as $texto) {
                $texto = trim($texto);

                if ($texto !== "") {
                    $insert = $conexion->prepare(
                        "INSERT INTO opciones (id_pregunta, texto)
                         VALUES (?, ?)"
                    );

                    $insert->bind_param("is", $id_nueva, $texto);
                    $insert->execute();
                    $insert->close();
                }
            }

            $mensaje = "Pregunta agregada correctamente.";
        }

        // EDITAR PREGUNTA
        elseif ($accion === "editar_pregunta") {

            $id = (int) ($_POST["id_pregunta"] ?? 0);
            $pregunta = trim($_POST["pregunta"] ?? "");

            if ($id <= 0 || $pregunta === "") {
                throw new Exception("Los datos de la pregunta no son válidos.");
            }

            $stmt = $conexion->prepare(
                "UPDATE preguntas
                 SET pregunta = ?
                 WHERE id_pregunta = ? AND id_encuesta = ?"
            );

            $stmt->bind_param("sii", $pregunta, $id, $id_encuesta);
            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                $stmt->close();

                $check = $conexion->prepare(
                    "SELECT id_pregunta FROM preguntas
                     WHERE id_pregunta = ? AND id_encuesta = ?"
                );

                $check->bind_param("ii", $id, $id_encuesta);
                $check->execute();
                $existe = $check->get_result()->num_rows > 0;
                $check->close();

                if (!$existe) {
                    throw new Exception("La pregunta no existe.");
                }
            } else {
                $stmt->close();
            }

            $mensaje = "Pregunta actualizada correctamente.";
        }

        // ELIMINAR PREGUNTA
        elseif ($accion === "eliminar_pregunta") {

            $id = (int) ($_POST["id_pregunta"] ?? 0);

            $check = $conexion->prepare(
                "SELECT id_pregunta FROM preguntas
                 WHERE id_pregunta = ? AND id_encuesta = ?"
            );

            $check->bind_param("ii", $id, $id_encuesta);
            $check->execute();
            $existe = $check->get_result()->num_rows > 0;
            $check->close();

            if (!$existe) {
                throw new Exception("La pregunta no existe.");
            }

            $check = $conexion->prepare(
                "SELECT COUNT(*) AS total FROM respuestas
                 WHERE id_pregunta = ?"
            );

            $check->bind_param("i", $id);
            $check->execute();
            $total = $check->get_result()->fetch_assoc()["total"];
            $check->close();

            if ($total > 0) {
                throw new Exception(
                    "No se puede eliminar esta pregunta porque tiene respuestas registradas."
                );
            }

            $conexion->begin_transaction();

            $delete = $conexion->prepare(
                "DELETE FROM opciones WHERE id_pregunta = ?"
            );

            $delete->bind_param("i", $id);
            $delete->execute();
            $delete->close();

            $delete = $conexion->prepare(
                "DELETE FROM preguntas
                 WHERE id_pregunta = ? AND id_encuesta = ?"
            );

            $delete->bind_param("ii", $id, $id_encuesta);
            $delete->execute();
            $delete->close();

            $conexion->commit();

            $mensaje = "Pregunta eliminada correctamente.";
        }

        // AGREGAR OPCIÓN
        elseif ($accion === "crear_opcion") {

            $id = (int) ($_POST["id_pregunta"] ?? 0);
            $texto = trim($_POST["texto"] ?? "");

            if ($texto === "") {
                throw new Exception("Escribí el texto de la opción.");
            }

            $check = $conexion->prepare(
                "SELECT id_pregunta FROM preguntas
                 WHERE id_pregunta = ? AND id_encuesta = ?"
            );

            $check->bind_param("ii", $id, $id_encuesta);
            $check->execute();
            $existe = $check->get_result()->num_rows > 0;
            $check->close();

            if (!$existe) {
                throw new Exception("La pregunta no existe.");
            }

            $stmt = $conexion->prepare(
                "INSERT INTO opciones (id_pregunta, texto)
                 VALUES (?, ?)"
            );

            $stmt->bind_param("is", $id, $texto);
            $stmt->execute();
            $stmt->close();

            $mensaje = "Opción agregada correctamente.";
        }

        // EDITAR OPCIÓN
        elseif ($accion === "editar_opcion") {

            $id_opcion = (int) ($_POST["id_opcion"] ?? 0);
            $id_pregunta = (int) ($_POST["id_pregunta"] ?? 0);
            $texto = trim($_POST["texto"] ?? "");

            if ($texto === "") {
                throw new Exception("Escribí el texto de la opción.");
            }

            $stmt = $conexion->prepare(
                "UPDATE opciones o
                 INNER JOIN preguntas p
                 ON p.id_pregunta = o.id_pregunta
                 SET o.texto = ?
                 WHERE o.id_opcion = ?
                 AND p.id_pregunta = ?
                 AND p.id_encuesta = ?"
            );

            $stmt->bind_param(
                "siii",
                $texto,
                $id_opcion,
                $id_pregunta,
                $id_encuesta
            );

            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                $stmt->close();

                $check = $conexion->prepare(
                    "SELECT o.id_opcion
                     FROM opciones o
                     INNER JOIN preguntas p ON p.id_pregunta = o.id_pregunta
                     WHERE o.id_opcion = ?
                     AND o.id_pregunta = ?
                     AND p.id_encuesta = ?"
                );

                $check->bind_param("iii", $id_opcion, $id_pregunta, $id_encuesta);
                $check->execute();
                $existe = $check->get_result()->num_rows > 0;
                $check->close();

                if (!$existe) {
                    throw new Exception("La opción no existe.");
                }
            } else {
                $stmt->close();
            }

            $mensaje = "Opción actualizada correctamente.";
        }

        // ELIMINAR OPCIÓN
        elseif ($accion === "eliminar_opcion") {

            $id_opcion = (int) ($_POST["id_opcion"] ?? 0);
            $id_pregunta = (int) ($_POST["id_pregunta"] ?? 0);

            $check = $conexion->prepare(
                "SELECT COUNT(*) AS total
                 FROM respuestas
                 WHERE id_opcion = ?"
            );

            $check->bind_param("i", $id_opcion);
            $check->execute();
            $total = $check->get_result()->fetch_assoc()["total"];
            $check->close();

            if ($total > 0) {
                throw new Exception(
                    "No se puede eliminar esta opción porque tiene respuestas registradas."
                );
            }

            $stmt = $conexion->prepare(
                "DELETE o FROM opciones o
                 INNER JOIN preguntas p ON p.id_pregunta = o.id_pregunta
                 WHERE o.id_opcion = ?
                 AND o.id_pregunta = ?
                 AND p.id_encuesta = ?"
            );

            $stmt->bind_param(
                "iii",
                $id_opcion,
                $id_pregunta,
                $id_encuesta
            );

            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                throw new Exception("No se encontró la opción.");
            }

            $stmt->close();

            $mensaje = "Opción eliminada correctamente.";
        }

        else {
            throw new Exception("Acción no reconocida.");
        }

    } catch (Throwable $e) {

        try {
            $conexion->rollback();
        } catch (Throwable $ignorado) {
        }

        $mensaje = $e instanceof mysqli_sql_exception
            ? "No se pudo completar la operación. Revisá las relaciones de la base de datos."
            : $e->getMessage();

        $tipo_mensaje = "danger";
    }

    $_SESSION["mensaje_encuestas"] = $mensaje;
    $_SESSION["tipo_mensaje_encuestas"] = $tipo_mensaje;

    header("Location: gestionarEncuestas.php");
    exit;
}

// Recuperar mensaje
if (isset($_SESSION["mensaje_encuestas"])) {

    $mensaje = $_SESSION["mensaje_encuestas"];
    $tipo_mensaje = $_SESSION["tipo_mensaje_encuestas"];

    unset($_SESSION["mensaje_encuestas"]);
    unset($_SESSION["tipo_mensaje_encuestas"]);
}

// Obtener encuesta
$stmt = $conexion->prepare(
    "SELECT * FROM encuestas WHERE id_encuesta = ?"
);

$stmt->bind_param("i", $id_encuesta);
$stmt->execute();

$encuesta = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$encuesta) {
    die("No se encontró la encuesta.");
}

// Obtener preguntas
$stmt = $conexion->prepare(
    "SELECT * FROM preguntas
     WHERE id_encuesta = ?
     ORDER BY id_pregunta"
);

$stmt->bind_param("i", $id_encuesta);
$stmt->execute();

$preguntas = $stmt->get_result();

$lista_preguntas = [];

while ($pregunta = $preguntas->fetch_assoc()) {

    $id = (int) $pregunta["id_pregunta"];

    $opciones = $conexion->prepare(
        "SELECT * FROM opciones
         WHERE id_pregunta = ?
         ORDER BY id_opcion"
    );

    $opciones->bind_param("i", $id);
    $opciones->execute();

    $pregunta["opciones"] = $opciones->get_result()->fetch_all(MYSQLI_ASSOC);

    $opciones->close();

    $lista_preguntas[] = $pregunta;
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Administrar encuestas | FLEM</title>

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
            background: var(--fondo);
            font-family: Arial, sans-serif;
            color: #26352f;
        }

        .navbar-flem {
            background: var(--verde);
            padding: 12px 0;
        }

        .marca {
            display: flex;
            align-items: center;
            gap: 13px;
            text-decoration: none;
        }

        .logo-hospital {
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

        .marca-texto .flem,
        .marca-texto .cruz {
            color: var(--dorado);
        }

        .marca-texto small {
            display: block;
            font-size: 11px;
            font-weight: normal;
            letter-spacing: 1px;
        }

        .contenedor {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 18px;
        }

        .encabezado {
            background: linear-gradient(120deg, var(--verde), #087b5b);
            color: white;
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 25px;
            box-shadow: 0 5px 14px rgba(0,0,0,.08);
        }

        .encabezado small {
            color: var(--dorado);
            font-weight: bold;
            letter-spacing: 1px;
        }

        .encabezado h1 {
            font-weight: bold;
            margin-top: 10px;
        }

        .tarjeta {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 22px;
            box-shadow: 0 3px 12px rgba(0,0,0,.06);
        }

        .pregunta {
            border-left: 5px solid var(--verde);
        }

        .titulo-seccion {
            color: var(--verde);
            font-weight: bold;
        }

        .btn-flem {
            background: var(--verde);
            color: white;
            border: none;
        }

        .btn-flem:hover {
            background: var(--verde-oscuro);
            color: white;
        }

        .btn-dorado {
            background: var(--dorado);
            color: #26352f;
            border: none;
            font-weight: bold;
        }

        .btn-dorado:hover {
            background: #c29d27;
            color: #26352f;
        }

        .opcion {
            background: var(--fondo);
            border: 1px solid #e4ebe7;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
        }

        .form-control {
            border-radius: 8px;
            padding: 10px 12px;
            border-color: #d8e1dc;
        }

        .form-control:focus {
            border-color: var(--verde);
            box-shadow: 0 0 0 3px rgba(7,92,69,.12);
        }

        .btn-volver {
            background: white;
            color: var(--verde);
            border: none;
            border-radius: 8px;
            padding: 9px 15px;
            text-decoration: none;
            font-weight: bold;
        }

        .btn-volver:hover {
            background: #e8f1ed;
            color: var(--verde-oscuro);
        }

        .estado {
            background: #e4f2e9;
            color: #17633e;
            padding: 7px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .estado-inactivo {
            background: #e9ecef;
            color: #495057;
        }

        .pie {
            text-align: center;
            color: #7a8981;
            padding: 25px;
            font-size: 13px;
        }

        .pie strong {
            color: var(--verde);
        }

        .pie span {
            color: var(--dorado);
        }

        @media (max-width: 600px) {

            .logo-hospital {
                width: 38px;
                height: 38px;
            }

            .marca-texto {
                font-size: 19px;
            }

            .marca-texto small {
                font-size: 9px;
            }

            .btn-volver {
                padding: 8px 10px;
                font-size: 13px;
            }

            .contenedor {
                margin: 25px auto;
            }

            .encabezado {
                padding: 22px;
            }

            .encabezado h1 {
                font-size: 24px;
            }

            .tarjeta {
                padding: 18px;
            }
        }

    </style>

</head>

<body>

<nav class="navbar-flem">

    <div class="container d-flex justify-content-between align-items-center">

        <a href="../panel.php" class="marca">

            <img
                src="../../FrontEnd/img/logo-hospital-clinicas.png"
                alt="Logo Hospital de Clínicas"
                class="logo-hospital"
            >

            <span class="marca-texto">
                <span class="flem">FLEM</span><span class="cruz"> ×</span>
                <small>HOSPITAL DE CLÍNICAS</small>
            </span>

        </a>

        <a href="../panel.php" class="btn-volver">
            Volver al panel
        </a>

    </div>

</nav>

<main class="contenedor">

    <div class="encabezado">

        <small>SISTEMA HOSPITALARIO</small>

        <h1 class="mt-2">Administración de encuestas</h1>

        <p class="mb-0">
            Gestioná las preguntas y opciones de respuesta de las encuestas.
        </p>

    </div>

    <?php if ($mensaje !== ""): ?>

        <div class="alert alert-<?= htmlspecialchars($tipo_mensaje) ?> alert-dismissible fade show">

            <?= htmlspecialchars($mensaje) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert">
            </button>

        </div>

    <?php endif; ?>

    <!-- INFORMACIÓN DE LA ENCUESTA -->

    <div class="tarjeta">

        <h4 class="titulo-seccion">
            <?= htmlspecialchars($encuesta["Pregunta"]) ?>
        </h4>

        <p class="text-secondary">
            <?= htmlspecialchars($encuesta["descripcion"] ?? "") ?>
        </p>

        <span class="<?= $encuesta["estado"] === "activa" ? "estado" : "estado estado-inactivo" ?>">
            <?= htmlspecialchars($encuesta["estado"]) ?>
        </span>

    </div>

    <!-- AGREGAR PREGUNTA -->

    <div class="tarjeta">

        <h4 class="titulo-seccion mb-3">
            Agregar una pregunta
        </h4>

        <form method="POST">

            <input
                type="hidden"
                name="token"
                value="<?= htmlspecialchars($_SESSION["token_encuestas"]) ?>"
            >

            <input
                type="hidden"
                name="accion"
                value="crear_pregunta"
            >

            <label class="form-label fw-semibold">
                Texto de la pregunta
            </label>

            <input
                type="text"
                name="pregunta"
                class="form-control mb-3"
                maxlength="500"
                placeholder="Escribí la nueva pregunta"
                required
            >

            <label class="form-label fw-semibold">
                Opciones de respuesta (si corresponde)
            </label>

            <input
                type="text"
                name="opciones[]"
                class="form-control mb-2"
                placeholder="Primera opción"
            >

            <input
                type="text"
                name="opciones[]"
                class="form-control mb-2"
                placeholder="Segunda opción"
            >

            <input
                type="text"
                name="opciones[]"
                class="form-control mb-3"
                placeholder="Tercera opción"
            >

            <button type="submit" class="btn btn-flem">
                + Agregar pregunta
            </button>

        </form>

        <p class="text-muted small mt-3 mb-0">
            Si dejás las opciones vacías, la pregunta será de respuesta escrita.
        </p>

    </div>

    <!-- PREGUNTAS EXISTENTES -->

    <h4 class="titulo-seccion mb-3">
        Preguntas existentes
    </h4>

    <?php if (count($lista_preguntas) > 0): ?>

        <?php foreach ($lista_preguntas as $pregunta): ?>

            <div class="tarjeta pregunta">

                <!-- EDITAR PREGUNTA -->

                <form method="POST" class="mb-3">

                    <input
                        type="hidden"
                        name="token"
                        value="<?= htmlspecialchars($_SESSION["token_encuestas"]) ?>"
                    >

                    <input
                        type="hidden"
                        name="accion"
                        value="editar_pregunta"
                    >

                    <input
                        type="hidden"
                        name="id_pregunta"
                        value="<?= (int)$pregunta["id_pregunta"] ?>"
                    >

                    <label class="form-label fw-bold">
                        Pregunta #<?= (int)$pregunta["id_pregunta"] ?>
                    </label>

                    <div class="d-flex gap-2 flex-wrap">

                        <input
                            type="text"
                            name="pregunta"
                            class="form-control flex-grow-1"
                            value="<?= htmlspecialchars($pregunta["pregunta"]) ?>"
                            required
                        >

                        <button class="btn btn-dorado" type="submit">
                            Guardar cambios
                        </button>

                    </div>

                </form>

                <h6 class="fw-bold mt-3 titulo-seccion">
                    Opciones de respuesta
                </h6>

                <?php if (count($pregunta["opciones"]) > 0): ?>

                    <?php foreach ($pregunta["opciones"] as $opcion): ?>

                        <div class="opcion">

                            <!-- EDITAR OPCIÓN -->

                            <form method="POST" class="d-flex gap-2 flex-wrap">

                                <input
                                    type="hidden"
                                    name="token"
                                    value="<?= htmlspecialchars($_SESSION["token_encuestas"]) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="accion"
                                    value="editar_opcion"
                                >

                                <input
                                    type="hidden"
                                    name="id_opcion"
                                    value="<?= (int)$opcion["id_opcion"] ?>"
                                >

                                <input
                                    type="hidden"
                                    name="id_pregunta"
                                    value="<?= (int)$pregunta["id_pregunta"] ?>"
                                >

                                <input
                                    type="text"
                                    name="texto"
                                    class="form-control flex-grow-1"
                                    value="<?= htmlspecialchars($opcion["texto"]) ?>"
                                    required
                                >

                                <button
                                    type="submit"
                                    class="btn btn-outline-success btn-sm">
                                    Guardar
                                </button>

                            </form>

                            <!-- ELIMINAR OPCIÓN -->

                            <form
                                method="POST"
                                class="mt-2"
                                onsubmit="return confirm('¿Querés eliminar esta opción?');">

                                <input
                                    type="hidden"
                                    name="token"
                                    value="<?= htmlspecialchars($_SESSION["token_encuestas"]) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="accion"
                                    value="eliminar_opcion"
                                >

                                <input
                                    type="hidden"
                                    name="id_opcion"
                                    value="<?= (int)$opcion["id_opcion"] ?>"
                                >

                                <input
                                    type="hidden"
                                    name="id_pregunta"
                                    value="<?= (int)$pregunta["id_pregunta"] ?>"
                                >

                                <button
                                    type="submit"
                                    class="btn btn-outline-danger btn-sm">
                                    Eliminar opción
                                </button>

                            </form>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <p class="text-muted">
                        Esta pregunta permite una respuesta escrita.
                    </p>

                <?php endif; ?>

                <!-- AGREGAR OPCIÓN -->

                <form method="POST" class="mt-3">

                    <input
                        type="hidden"
                        name="token"
                        value="<?= htmlspecialchars($_SESSION["token_encuestas"]) ?>"
                    >

                    <input
                        type="hidden"
                        name="accion"
                        value="crear_opcion"
                    >

                    <input
                        type="hidden"
                        name="id_pregunta"
                        value="<?= (int)$pregunta["id_pregunta"] ?>"
                    >

                    <div class="input-group">

                        <input
                            type="text"
                            name="texto"
                            class="form-control"
                            placeholder="Nueva opción de respuesta"
                            required
                        >

                        <button class="btn btn-flem" type="submit">
                            + Agregar opción
                        </button>

                    </div>

                </form>

                <hr>

                <!-- ELIMINAR PREGUNTA -->

                <form
                    method="POST"
                    onsubmit="return confirm('¿Eliminar esta pregunta? Solo se podrá eliminar si no tiene respuestas registradas.');">

                    <input
                        type="hidden"
                        name="token"
                        value="<?= htmlspecialchars($_SESSION["token_encuestas"]) ?>"
                    >

                    <input
                        type="hidden"
                        name="accion"
                        value="eliminar_pregunta"
                    >

                    <input
                        type="hidden"
                        name="id_pregunta"
                        value="<?= (int)$pregunta["id_pregunta"] ?>"
                    >

                    <button
                        type="submit"
                        class="btn btn-outline-danger btn-sm">
                        Eliminar pregunta
                    </button>

                </form>

            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <div class="tarjeta text-center">

            <p class="text-secondary mb-0">
                Todavía no hay preguntas registradas en esta encuesta.
            </p>

        </div>

    <?php endif; ?>

</main>

<footer class="pie">

    <strong><span>FLEM</span> × Hospital de Clínicas</strong>

    <div>Sistema de gestión hospitalaria</div>

</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php
$conexion->close();
?>
