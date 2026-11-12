
<?php
session_start();

if (!isset($_SESSION["id"]) || ($_SESSION["rol"] ?? "") !== "Administrador") {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . "/conexion_encuestas.php";

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

            // Agregar opciones iniciales
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

        if ($conexion->errno || $conexion->connect_errno) {
            // No se muestran detalles internos de la base de datos.
        }

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
        "SELECT * FROM opciones WHERE id_pregunta = ? ORDER BY id_opcion"
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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

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
            box-shadow: 0 5px 14px rgba(0,0,0,.1);
        }

        .encabezado small {
            color: var(--dorado);
            font-weight: bold;
            letter-spacing: 1px;
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
        }

        .btn-dorado:hover {
            background: #c19d2e;
            color: #26352f;
        }

        .opcion {
            background: var(--fondo);
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 8px;
        }

        .form-control:focus {
            border-color: var(--verde);
            box-shadow: 0 0 0 .2rem rgba(7,92,69,.12);
        }

        .footer-flem {
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
                padding: 22px;
            }

            .tarjeta {
                padding: 18px;
            }

        }

    </style>

</head>

<body>

    <!-- ENCABEZADO -->

    <nav class="navbar navbar-flem">

        <div class="container d-flex justify-content-between align-items-center">

            <a href="../panel.php" class="marca">

                <span class="brand-name">
                    FLEM <span class="brand-x">×</span>
                </span>

                <img
                    src="../../FrontEnd/img/logo-hospital-clinicas.png"
                    alt="Logo Hospital de Clínicas"
                    class="logo-hospital"
                >

            </a>

            <a href="../panel.php" class="btn btn-light">
                Volver al panel
            </a>

        </div>

    </nav>

    <main class="contenedor">

        <div class="encabezado">

            <small>SISTEMA HOSPITALARIO</small>

            <h1 class="fw-bold mt-2">
                Administración de encuestas
            </h1>

            <p class="mb-0">
                Gestioná las preguntas y opciones de respuesta.
            </p>

        </div>

        <?php if ($mensaje !== ""): ?>

            <div class="alert alert-<?= htmlspecialchars($tipo_mensaje) ?> alert-dismissible fade show">

                <?= htmlspecialchars($mensaje) ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

            </div>

        <?php endif; ?>

        <!-- INFORMACIÓN DE LA ENCUESTA -->

        <div class="tarjeta">

            <h4 class="fw-bold" style="color:#075c45;">
                <?= htmlspecialchars($encuesta["Pregunta"]) ?>
            </h4>

            <p class="text-secondary">
                <?= htmlspecialchars($encuesta["descripcion"] ?? "") ?>
            </p>

            <span class="badge <?= $encuesta["estado"] === "activa" ? "bg-success" : "bg-secondary" ?>">
                <?= htmlspecialchars($encuesta["estado"]) ?>
            </span>

        </div>

        <!-- AGREGAR PREGUNTA -->

        <div class="tarjeta">

            <h4 class="fw-bold mb-3">
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

        <h4 class="fw-bold mb-3">
            Preguntas existentes
        </h4>

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

                <h6 class="fw-bold mt-3">
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

                                <button type="submit" class="btn btn-outline-success btn-sm">
                                    Guardar
                                </button>

                            </form>

                            <!-- ELIMINAR OPCIÓN -->

                            <form
                                method="POST"
                                class="mt-2"
                                onsubmit="return confirm('¿Querés eliminar esta opción?');"
                            >

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

                                <button type="submit" class="btn btn-outline-danger btn-sm">
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
                    onsubmit="return confirm('¿Eliminar esta pregunta? Solo se podrá eliminar si no tiene respuestas registradas.');"
                >

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

                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        Eliminar pregunta
                    </button>

                </form>

            </div>

        <?php endforeach; ?>

    </main>

    <!-- PIE DE PÁGINA -->

    <footer class="footer-flem text-center">

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php
$conexion->close();
?>
