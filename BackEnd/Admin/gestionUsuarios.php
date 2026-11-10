```php
<?php

// Verificar permisos
if (
    !isset($_SESSION["id"]) ||
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "Administrador"
) {
    die("Acceso denegado.");
}

// Conexión a MySQL
require_once __DIR__ . "/../conexion.php";

// Token de seguridad
if (empty($_SESSION["csrf"])) {
    $_SESSION["csrf"] = bin2hex(random_bytes(32));
}

function limpiarDato($dato) {
    return htmlspecialchars((string)$dato, ENT_QUOTES, "UTF-8");
}

$mensaje = "";
$error = "";


// ===================================
// PROCESAR FORMULARIOS
// ===================================

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["accion"])) {

    if (
        !isset($_POST["csrf"]) ||
        !hash_equals($_SESSION["csrf"], $_POST["csrf"])
    ) {
        die("Solicitud no válida.");
    }

    $accion = $_POST["accion"];


    // ===================================
    // AGREGAR USUARIO
    // ===================================

    if ($accion === "agregar") {

        $nombre = trim($_POST["nombre"] ?? "");
        $apellido = trim($_POST["apellido"] ?? "");
        $cedula = trim($_POST["cedula"] ?? "");
        $edad = filter_var($_POST["edad"] ?? "", FILTER_VALIDATE_INT);
        $telefono = trim($_POST["telefono"] ?? "");
        $correo = trim($_POST["correo"] ?? "");
        $password = $_POST["password"] ?? "";
        $rolUsuario = $_POST["rol"] ?? "";

        if (
            $nombre === "" ||
            $apellido === "" ||
            $cedula === "" ||
            $edad === false ||
            $edad < 1 || $edad > 120 ||
            $telefono === "" ||
            !filter_var($correo, FILTER_VALIDATE_EMAIL) ||
            $password === "" ||
            !in_array($rolUsuario, ["Administrador", "Paciente"], true)
        ) {

            $error = "Completá correctamente todos los campos.";

        } else {

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
                    $rolUsuario
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


    // ===================================
    // MODIFICAR USUARIO
    // ===================================

    elseif ($accion === "modificar") {

        $id = filter_var($_POST["id"] ?? "", FILTER_VALIDATE_INT);

        $nombre = trim($_POST["nombre"] ?? "");
        $apellido = trim($_POST["apellido"] ?? "");
        $cedula = trim($_POST["cedula"] ?? "");
        $edad = filter_var($_POST["edad"] ?? "", FILTER_VALIDATE_INT);
        $telefono = trim($_POST["telefono"] ?? "");
        $correo = trim($_POST["correo"] ?? "");
        $password = $_POST["password"] ?? "";
        $rolUsuario = $_POST["rol"] ?? "";

        if (
            $id === false || $id <= 0 ||
            $nombre === "" ||
            $apellido === "" ||
            $cedula === "" ||
            $edad === false ||
            $edad < 1 || $edad > 120 ||
            $telefono === "" ||
            !filter_var($correo, FILTER_VALIDATE_EMAIL) ||
            !in_array($rolUsuario, ["Administrador", "Paciente"], true)
        ) {

            $error = "Verificá los datos ingresados.";

        } elseif (
            $id === (int)$_SESSION["id"] &&
            $rolUsuario !== "Administrador"
        ) {

            $error = "No podés quitarte tu propio rol de administrador.";

        } else {

            $buscar = $conexion->prepare(
                "SELECT id, rol FROM usuarios
                 WHERE id = ?"
            );

            $buscar->bind_param("i", $id);
            $buscar->execute();

            $usuarioActual = $buscar->get_result()->fetch_assoc();

            $buscar->close();

            if (!$usuarioActual) {

                $error = "El usuario no existe.";

            } elseif (
                $usuarioActual["rol"] === "Administrador" &&
                $rolUsuario !== "Administrador"
            ) {

                $cantidad = $conexion->query(
                    "SELECT COUNT(*) AS total
                     FROM usuarios WHERE rol = 'Administrador'"
                )->fetch_assoc()["total"];

                if ((int)$cantidad <= 1) {
                    $error = "No podés quitar el rol al último administrador.";
                }
            }

            if ($error === "") {

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
                            $rolUsuario,
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
                            $rolUsuario,
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
    }


    // ===================================
    // ELIMINAR USUARIO
    // ===================================

    elseif ($accion === "eliminar") {

        $id = filter_var($_POST["id"] ?? "", FILTER_VALIDATE_INT);

        if ($id === false || $id <= 0) {

            $error = "Usuario no válido.";

        } elseif ($id === (int)$_SESSION["id"]) {

            $error = "No podés eliminar tu propia cuenta.";

        } else {

            $buscar = $conexion->prepare(
                "SELECT rol FROM usuarios WHERE id = ?"
            );

            $buscar->bind_param("i", $id);
            $buscar->execute();

            $usuarioEliminar = $buscar->get_result()->fetch_assoc();

            $buscar->close();

            if (!$usuarioEliminar) {

                $error = "El usuario no existe.";

            } else {

                if ($usuarioEliminar["rol"] === "Administrador") {

                    $cantidad = $conexion->query(
                        "SELECT COUNT(*) AS total
                         FROM usuarios WHERE rol = 'Administrador'"
                    )->fetch_assoc()["total"];

                    if ((int)$cantidad <= 1) {
                        $error = "No podés eliminar al último administrador.";
                    }
                }

                if ($error === "") {

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
    }
}


// ===================================
// CARGAR USUARIO PARA EDITAR
// ===================================

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


// ===================================
// LISTAR USUARIOS
// ===================================

$resultado = $conexion->query(
    "SELECT id, nombre, apellido, cedula, edad,
    telefono, correo, rol
    FROM usuarios ORDER BY id DESC"
);

$totalUsuarios = $resultado->num_rows;

?>

<!-- ESTILOS -->

<style>

    .usuarios-card {
        border: none;
        border-radius: 18px;
    }

    .usuarios-titulo {
        color: #075c45;
    }

    .usuarios-tabla thead {
        background: #075c45;
        color: white;
    }

    .usuarios-tabla th {
        white-space: nowrap;
    }

    .usuarios-tabla td {
        vertical-align: middle;
    }

</style>


<!-- CONTENIDO DE USUARIOS -->

<div class="mb-4">

    <a href="panel.php" class="btn btn-secondary mb-3">
        ← Volver a administración
    </a>

    <h2 class="fw-bold usuarios-titulo">
        Gestión de usuarios
    </h2>

    <p class="text-secondary">
        Administrá los usuarios registrados en FLEM.
    </p>

</div>


<?php if ($mensaje !== "") { ?>

    <div class="alert alert-success">
        <?= limpiarDato($mensaje) ?>
    </div>

<?php } ?>


<?php if ($error !== "") { ?>

    <div class="alert alert-danger">
        <?= limpiarDato($error) ?>
    </div>

<?php } ?>


<!-- FORMULARIO -->

<div class="card usuarios-card shadow-sm mb-4">

    <div class="card-body p-4">

        <h4 class="fw-bold mb-3">

            <?= $editar ? "Modificar usuario" : "Agregar usuario" ?>

        </h4>

        <form method="POST" action="panel.php?vista=usuarios">

            <input type="hidden" name="csrf"
                   value="<?= limpiarDato($_SESSION["csrf"]) ?>">

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
                           value="<?= limpiarDato($editar["nombre"] ?? "") ?>"
                           required>

                </div>


                <div class="col-md-6">

                    <label class="form-label">Apellido</label>

                    <input type="text" name="apellido"
                           class="form-control"
                           value="<?= limpiarDato($editar["apellido"] ?? "") ?>"
                           required>

                </div>


                <div class="col-md-6">

                    <label class="form-label">Cédula</label>

                    <input type="text" name="cedula"
                           class="form-control"
                           value="<?= limpiarDato($editar["cedula"] ?? "") ?>"
                           required>

                </div>


                <div class="col-md-6">

                    <label class="form-label">Edad</label>

                    <input type="number" name="edad"
                           class="form-control"
                           min="1" max="120"
                           value="<?= limpiarDato($editar["edad"] ?? "") ?>"
                           required>

                </div>


                <div class="col-md-6">

                    <label class="form-label">Teléfono</label>

                    <input type="text" name="telefono"
                           class="form-control"
                           value="<?= limpiarDato($editar["telefono"] ?? "") ?>"
                           required>

                </div>


                <div class="col-md-6">

                    <label class="form-label">Correo electrónico</label>

                    <input type="email" name="correo"
                           class="form-control"
                           value="<?= limpiarDato($editar["correo"] ?? "") ?>"
                           required>

                </div>


                <div class="col-md-6">

                    <label class="form-label">Contraseña</label>

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

                <button type="submit" class="btn btn-flem">

                    <?= $editar ? "Guardar cambios" : "Agregar usuario" ?>

                </button>

                <?php if ($editar) { ?>

                    <a href="panel.php?vista=usuarios"
                       class="btn btn-secondary">
                        Cancelar
                    </a>

                <?php } ?>

            </div>

        </form>

    </div>

</div>


<!-- TABLA DE USUARIOS -->

<div class="card usuarios-card shadow-sm">

    <div class="card-body p-4">

        <div class="d-flex justify-content-between align-items-center mb-3">

            <h4 class="fw-bold mb-0">
                Usuarios registrados
            </h4>

            <span class="badge text-bg-success">
                Total: <?= $totalUsuarios ?>
            </span>

        </div>


        <div class="table-responsive">

            <table class="table table-bordered table-hover usuarios-tabla">

                <thead>

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

                            <td><?= limpiarDato($usuario["nombre"]) ?></td>

                            <td><?= limpiarDato($usuario["apellido"]) ?></td>

                            <td><?= limpiarDato($usuario["cedula"]) ?></td>

                            <td><?= (int)$usuario["edad"] ?></td>

                            <td><?= limpiarDato($usuario["telefono"]) ?></td>

                            <td><?= limpiarDato($usuario["correo"]) ?></td>

                            <td>

                                <span class="badge <?= $usuario["rol"] === "Administrador" ? "text-bg-primary" : "text-bg-secondary" ?>">

                                    <?= limpiarDato($usuario["rol"]) ?>

                                </span>

                            </td>

                            <td>

                                <a href="panel.php?vista=usuarios&editar=<?= (int)$usuario["id"] ?>"
                                   class="btn btn-warning btn-sm mb-1">
                                    Editar
                                </a>


                                <form method="POST"
                                      action="panel.php?vista=usuarios"
                                      class="d-inline"
                                      onsubmit="return confirm('¿Seguro que querés eliminar este usuario?')">

                                    <input type="hidden" name="csrf"
                                           value="<?= limpiarDato($_SESSION["csrf"]) ?>">

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
```