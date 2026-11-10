
<?php
session_start();

require_once __DIR__ . "/../conexion.php";

if (!isset($_SESSION["id"]) || $_SESSION["rol"] !== "Administrador") {
    header("Location: ../login.php");
    exit;
}

$mensaje = "";
$error = "";

// Obtener pacientes
$consultaPacientes = $conexion->query(
    "SELECT id, nombre, apellido, cedula
     FROM usuarios
     WHERE rol = 'Paciente'
     ORDER BY nombre, apellido"
);

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $usuario_id = filter_input(INPUT_POST, "usuario_id", FILTER_VALIDATE_INT);
    $titulo = trim($_POST["titulo"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");
    $fecha = $_POST["fecha"] ?? "";
    $estado = $_POST["estado"] ?? "Pendiente";

    $estadosPermitidos = ["Pendiente", "En proceso", "Finalizado"];

    if (!$usuario_id || $titulo === "" || $descripcion === "" || $fecha === "") {
        $error = "Completá todos los campos.";
    } elseif (!in_array($estado, $estadosPermitidos, true)) {
        $error = "El estado seleccionado no es válido.";
    } else {

        // Verificar que el usuario seleccionado sea paciente
        $verificar = $conexion->prepare(
            "SELECT id FROM usuarios WHERE id = ? AND rol = 'Paciente'"
        );

        $verificar->bind_param("i", $usuario_id);
        $verificar->execute();
        $resultado = $verificar->get_result();

        if ($resultado->num_rows === 0) {
            $error = "El paciente seleccionado no existe.";
        } else {

            $creado_por = $_SESSION["id"];

            $insertar = $conexion->prepare(
                "INSERT INTO seguimientos
                (usuario_id, titulo, descripcion, fecha, estado, creado_por)
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            $insertar->bind_param(
                "issssi",
                $usuario_id,
                $titulo,
                $descripcion,
                $fecha,
                $estado,
                $creado_por
            );

            if ($insertar->execute()) {
                $mensaje = "Seguimiento registrado correctamente.";
            } else {
                $error = "No se pudo registrar el seguimiento.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear seguimiento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow p-4">

        <h2 class="mb-4">Registrar seguimiento médico</h2>

        <?php if ($mensaje): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Seleccionar paciente</label>

                <select name="usuario_id" class="form-select" required>
                    <option value="">Elegir paciente</option>

                    <?php while ($paciente = $consultaPacientes->fetch_assoc()): ?>
                        <option value="<?= $paciente["id"] ?>"
                            <?= (($_POST["usuario_id"] ?? "") == $paciente["id"]) ? "selected" : "" ?>>
                            <?= htmlspecialchars(
                                $paciente["nombre"] . " " .
                                $paciente["apellido"] . " - CI: " .
                                $paciente["cedula"]
                            ) ?>
                        </option>
                    <?php endwhile; ?>

                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Título</label>
                <input type="text" name="titulo" class="form-control"
                       maxlength="150" required
                       value="<?= htmlspecialchars($_POST["titulo"] ?? "") ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control"
                          rows="4" required><?= htmlspecialchars($_POST["descripcion"] ?? "") ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Fecha</label>
                <input type="date" name="fecha" class="form-control"
                       required value="<?= htmlspecialchars($_POST["fecha"] ?? date("Y-m-d")) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Estado</label>

                <select name="estado" class="form-select" required>
                    <option value="Pendiente">Pendiente</option>
                    <option value="En proceso">En proceso</option>
                    <option value="Finalizado">Finalizado</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">
                Registrar seguimiento
            </button>

            <a href="listarSeguimiento.php" class="btn btn-secondary">
                Volver
            </a>

        </form>

    </div>

</div>

</body>
</html>
