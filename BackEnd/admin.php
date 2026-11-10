<?php

session_start();

if (
    !isset($_SESSION["id"]) ||
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "Administrador"
) {
    die("Acceso denegado.");
}

require_once "../BackEnd/conexion.php";

// Protección CSRF
if (empty($_SESSION["csrf"])) {
    $_SESSION["csrf"] = bin2hex(random_bytes(32));
}

function limpiar($dato) {
    return htmlspecialchars((string)$dato, ENT_QUOTES, "UTF-8");
}

$mensaje = "";
$error = "";

// PROCESAR FORMULARIOS
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        !isset($_POST["csrf"]) ||
        !hash_equals($_SESSION["csrf"], $_POST["csrf"])
    ) {
        die("Solicitud no válida.");
    }

    $accion = $_POST["accion"] ?? "";

    // AGREGAR USUARIO
    if ($accion === "agregar") {

        $nombre = trim($_POST["nombre"]);
        $apellido = trim($_POST["apellido"]);
        $cedula = trim($_POST["cedula"]);
        $edad = (int)$_POST["edad"];
        $telefono = trim($_POST["telefono"]);
        $correo = trim($_POST["correo"]);
        $password = $_POST["password"];
        $rol = $_POST["rol"];

        if (
            $nombre === "" ||
            $apellido === "" ||
            $cedula === "" ||
            $telefono === "" ||
            !filter_var($correo, FILTER_VALIDATE_EMAIL) ||
            $edad < 1 ||
            $edad > 120 ||
            $password === "" ||
            !in_array($rol, ["Administrador", "Paciente"], true)
        ) {
            $error = "Verificá los datos ingresados.";

        } else {

            // Comprobar cédula y correo
            $buscar = $conexion->prepare(
                "SELECT id FROM usuarios
                 WHERE cedula = ? OR correo = ?"
            );

            $buscar->bind_param("ss", $cedula, $correo);
            $buscar->execute();

            $existe = $buscar->get_result()->num_rows > 0;
            $buscar->close();

            if ($existe) {

                $error = "La cédula o el correo ya están registrados.";

            } else {

                $hash = password_hash($password, PASSWORD_DEFAULT);

                $sql = $conexion->prepare(
                    "INSERT INTO usuarios
                    (nombre, apellido, cedula, edad, telefono, correo, password, rol)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
                );

                $sql->bind_param(
                    "sssissss",
                    $nombre,
                    $apellido,
                    $cedula,
                    $edad,
                    $telefono,
                    $correo,
                    $hash,
                    $rol
                );

                if ($sql->execute()) {
                    $mensaje = "Usuario agregado correctamente.";
                } else {
                    $error = "No se pudo agregar el usuario.";
                }

                $sql->close();
            }
        }
    }

    // MODIFICAR USUARIO
    if ($accion === "modificar") {

        $id = (int)$_POST["id"];
        $nombre = trim($_POST["nombre"]);
        $apellido = trim($_POST["apellido"]);
        $cedula = trim($_POST["cedula"]);
        $edad = (int)$_POST["edad"];
        $telefono = trim($_POST["telefono"]);
        $correo = trim($_POST["correo"]);
        $password = $_POST["password"];
        $rol = $_POST["rol"];

        if (
            $id <= 0 ||
            $nombre === "" ||
            $apellido === "" ||
            $cedula === "" ||
            $telefono === "" ||
            !filter_var($correo, FILTER_VALIDATE_EMAIL) ||
            $edad < 1 ||
            $edad > 120 ||
            !in_array($rol, ["Administrador", "Paciente"], true)
        ) {
            $error = "Verificá los datos ingresados.";

        } elseif (
            $id === (int)$_SESSION["id"] &&
            $rol !== "Administrador"
        ) {
            $error = "No podés quitarte tu propio rol de administrador.";

        } else {

            $buscar = $conexion->prepare(
                "SELECT id FROM usuarios
                 WHERE (cedula = ? OR correo = ?) AND id != ?"
            );

            $buscar->bind_param("ssi", $cedula, $correo, $id);
            $buscar->execute();

            $existe = $buscar->get_result()->num_rows > 0;
            $buscar->close();

            if ($existe) {

                $error = "La cédula o el correo ya pertenecen a otro usuario.";

            } else {

                if ($password !== "") {

                    $hash = password_hash($password, PASSWORD_DEFAULT);

                    $sql = $conexion->prepare(
                        "UPDATE usuarios SET
                        nombre = ?, apellido = ?, cedula = ?,
                        edad = ?, telefono = ?, correo = ?,
                        password = ?, rol = ?
                        WHERE id = ?"
                    );

                    $sql->bind_param(
                        "sssissssi",
                        $nombre,
                        $apellido,
                        $cedula,
                        $edad,
                        $telefono,
                        $correo,
                        $hash,
                        $rol,
                        $id
                    );

                } else {

                    $sql = $conexion->prepare(
                        "UPDATE usuarios SET
                        nombre = ?, apellido = ?, cedula = ?,
                        edad = ?, telefono = ?, correo = ?, rol = ?
                        WHERE id = ?"
                    );

                    $sql->bind_param(
                        "sssisssi",
                        $nombre,
                        $apellido,
                        $cedula,
                        $edad,
                        $telefono,
                        $correo,
                        $rol,
                        $id
                    );
                }

                if ($sql->execute()) {
                    $mensaje = "Usuario modificado correctamente.";
                } else {
                    $error = "No se pudieron modificar los datos.";
                }

                $sql->close();
            }
        }
    }

    // ELIMINAR USUARIO
    if ($accion === "eliminar") {

        $id = (int)$_POST["id"];

        if ($id <= 0) {

            $error = "Usuario no válido.";

        } elseif ($id === (int)$_SESSION["id"]) {

            $error = "No podés eliminar tu propia cuenta.";

        } else {

            $sql = $conexion->prepare(
                "DELETE FROM usuarios WHERE id = ?"
            );

            $sql->bind_param("i", $id);

            if ($sql->execute() && $sql->affected_rows > 0) {
                $mensaje = "Usuario eliminado correctamente.";
            } else {
                $error = "No se pudo eliminar el usuario.";
            }

            $sql->close();
        }
    }
}

// OBTENER USUARIO PARA EDITAR
$editar = null;

if (isset($_GET["editar"]) && ctype_digit($_GET["editar"])) {

    $idEditar = (int)$_GET["editar"];

    $sql = $conexion->prepare(
        "SELECT id, nombre, apellido, cedula, edad,
        telefono, correo, rol
        FROM usuarios WHERE id = ?"
    );

    $sql->bind_param("i", $idEditar);
    $sql->execute();

    $editar = $sql->get_result()->fetch_assoc();

    $sql->close();
}

// LISTAR USUARIOS
$resultado = $conexion->query(
    "SELECT id, nombre, apellido, cedula, edad,
    telefono, correo, rol
    FROM usuarios ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Administrar usuarios</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet">

</head>

<body class="bg-light">

<nav class="navbar navbar-dark bg-primary">

    <div class="container">

        <span class="navbar-brand">
            Sistema Hospitalario
        </span>

        <a href="../BackEnd/logout.php" class="btn btn-light">
            Cerrar sesión
        </a>

    </div>

</nav>

<div class="container py-5">

    <h1 class="fw-bold mb-2">Administración de usuarios</h1>

    <p class="text-secondary">
        Desde aquí podés agregar, modificar y eliminar usuarios.
    </p>

    <a href="../BackEnd/panel.php" class="btn btn-secondary mb-4">
        Volver al panel
    </a>

    <?php if ($mensaje !== "") { ?>

        <div class="alert alert-success">
            <?= limpiar($mensaje) ?>
        </div>

    <?php } ?>

    <?php if ($error !== "") { ?>

        <div class="alert alert-danger">
            <?= limpiar($error) ?>
        </div>

    <?php } ?>


    <!-- FORMULARIO -->

    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <h4 class="mb-3">

                <?= $editar ? "Modificar usuario" : "Agregar usuario" ?>

            </h4>

            <form method="POST">

                <input type="hidden" name="csrf"
                value="<?= limpiar($_SESSION["csrf"]) ?>">

                <input type="hidden" name="accion"
                value="<?= $editar ? "modificar" : "agregar" ?>">

                <?php if ($editar) { ?>

                    <input type="hidden" name="id"
                    value="<?= (int)$editar["id"] ?>">

                <?php } ?>

                <div class="row g-3">

                    <div class="col-md-6">

                        <label class="form-label">Nombre</label>

                        <input type="text" name="nombre"
                        class="form-control"
                        value="<?= limpiar($editar["nombre"] ?? "") ?>"
                        required>

                    </div>

                    <div class="col-md-6">

                        <label class="form-label">Apellido</label>

                        <input type="text" name="apellido"
                        class="form-control"
                        value="<?= limpiar($editar["apellido"] ?? "") ?>"
                        required>

                    </div>

                    <div class="col-md-6">

                        <label class="form-label">Cédula</label>

                        <input type="text" name="cedula"
                        class="form-control"
                        value="<?= limpiar($editar["cedula"] ?? "") ?>"
                        required>

                    </div>

                    <div class="col-md-6">

                        <label class="form-label">Edad</label>

                        <input type="number" name="edad"
                        class="form-control"
                        min="1" max="120"
                        value="<?= limpiar($editar["edad"] ?? "") ?>"
                        required>

                    </div>

                    <div class="col-md-6">

                        <label class="form-label">Teléfono</label>

                        <input type="text" name="telefono"
                        class="form-control"
                        value="<?= limpiar($editar["telefono"] ?? "") ?>"
                        required>

                    </div>

                    <div class="col-md-6">

                        <label class="form-label">Correo</label>

                        <input type="email" name="correo"
                        class="form-control"
                        value="<?= limpiar($editar["correo"] ?? "") ?>"
                        required>

                    </div>

                    <div class="col-md-6">

                        <label class="form-label">
                            Contraseña
                        </label>

                        <input type="password" name="password"
                        class="form-control"
                        <?= $editar ? "" : "required" ?>
                        placeholder="<?= $editar ? "Dejar vacío para mantener la actual" : "Ingresar contraseña" ?>">

                    </div>

                    <div class="col-md-6">

                        <label class="form-label">Rol</label>

                        <select name="rol" class="form-select" required>

                            <option value="Paciente"
                            <?= ($editar["rol"] ?? "") === "Paciente" ? "selected" : "" ?>>
                                Paciente
                            </option>

                            <option value="Administrador"
                            <?= ($editar["rol"] ?? "") === "Administrador" ? "selected" : "" ?>>
                                Administrador
                            </option>

                        </select>

                    </div>

                </div>

                <div class="mt-4">

                    <button type="submit" class="btn btn-primary">

                        <?= $editar ? "Guardar cambios" : "Agregar usuario" ?>

                    </button>

                    <?php if ($editar) { ?>

                        <a href="usuarios.php" class="btn btn-secondary">
                            Cancelar
                        </a>

                    <?php } ?>

                </div>

            </form>

        </div>

    </div>


    <!-- TABLA DE USUARIOS -->

    <div class="card shadow-sm">

        <div class="card-body">

            <h4 class="mb-3">Usuarios registrados</h4>

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-primary">

                        <tr>

                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Cédula</th>
                            <th>Edad</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th>Rol</th>
                            <th>Acciones</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php while ($usuario = $resultado->fetch_assoc()) { ?>

                            <tr>

                                <td><?= (int)$usuario["id"] ?></td>

                                <td><?= limpiar($usuario["nombre"]) ?></td>

                                <td><?= limpiar($usuario["apellido"]) ?></td>

                                <td><?= limpiar($usuario["cedula"]) ?></td>

                                <td><?= (int)$usuario["edad"] ?></td>

                                <td><?= limpiar($usuario["telefono"]) ?></td>

                                <td><?= limpiar($usuario["correo"]) ?></td>

                                <td><?= limpiar($usuario["rol"]) ?></td>

                                <td>

                                    <a href="usuarios.php?editar=<?= (int)$usuario["id"] ?>"
                                    class="btn btn-warning btn-sm mb-1">
                                        Editar
                                    </a>

                                    <form method="POST" class="d-inline"
                                    onsubmit="return confirm('¿Seguro que querés eliminar este usuario?')">

                                        <input type="hidden" name="csrf"
                                        value="<?= limpiar($_SESSION["csrf"]) ?>">

                                        <input type="hidden" name="accion"
                                        value="eliminar">

                                        <input type="hidden" name="id"
                                        value="<?= (int)$usuario["id"] ?>">

                                        <button type="submit"
                                        class="btn btn-danger btn-sm mb-1">
                                            Eliminar
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</body>
</html>