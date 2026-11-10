
<?php

session_start();

if (
    !isset($_SESSION["id"]) ||
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "Administrador"
) {
    header("Location: ../login.php");
    exit;
}

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

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        !isset($_POST["csrf"]) ||
        !hash_equals($_SESSION["csrf"], $_POST["csrf"])
    ) {
        die("Solicitud no válida.");
    }

    $accion = $_POST["accion"] ?? "";

    try {

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
                throw new Exception("Completá correctamente todos los campos.");
            }

            $buscar = $conexion->prepare(
                "SELECT id FROM usuarios
                 WHERE cedula = ? OR correo = ?"
            );

            $buscar->bind_param("ss", $cedula, $correo);
            $buscar->execute();

            $existe = $buscar->get_result()->num_rows > 0;
            $buscar->close();

            if ($existe) {
                throw new Exception("La cédula o el correo ya están registrados.");
            }

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

            $sql->execute();
            $sql->close();

            $mensaje = "Usuario agregado correctamente.";
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
                throw new Exception("Verificá los datos ingresados.");
            }

            if (
                $id === (int)$_SESSION["id"] &&
                $rolUsuario !== "Administrador"
            ) {
                throw new Exception("No podés quitarte tu propio rol de administrador.");
            }

            $buscar = $conexion->prepare(
                "SELECT id, rol FROM usuarios WHERE id = ?"
            );

            $buscar->bind_param("i", $id);
            $buscar->execute();

            $usuarioActual = $buscar->get_result()->fetch_assoc();
            $buscar->close();

            if (!$usuarioActual) {
                throw new Exception("El usuario no existe.");
            }

            if (
                $usuarioActual["rol"] === "Administrador" &&
                $rolUsuario !== "Administrador"
            ) {

                $cantidad = $conexion->query(
                    "SELECT COUNT(*) AS total
                     FROM usuarios
                     WHERE rol = 'Administrador'"
                )->fetch_assoc()["total"];

                if ((int)$cantidad <= 1) {
                    throw new Exception("No podés quitar el rol al último administrador.");
                }
            }

            $buscar = $conexion->prepare(
                "SELECT id FROM usuarios
                 WHERE (cedula = ? OR correo = ?) AND id != ?"
            );

            $buscar->bind_param("ssi", $cedula, $correo, $id);
            $buscar->execute();

            $existe = $buscar->get_result()->num_rows > 0;
            $buscar->close();

            if ($existe) {
                throw new Exception("La cédula o el correo ya pertenecen a otro usuario.");
            }

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

            $sql->execute();
            $sql->close();

            $mensaje = "Usuario modificado correctamente.";
        }


        // ===================================
        // ELIMINAR USUARIO
        // ===================================

        elseif ($accion === "eliminar") {

            $id = filter_var($_POST["id"] ?? "", FILTER_VALIDATE_INT);

            if ($id === false || $id <= 0) {
                throw new Exception("Usuario no válido.");
            }

            if ($id === (int)$_SESSION["id"]) {
                throw new Exception("No podés eliminar tu propia cuenta.");
            }

            $buscar = $conexion->prepare(
                "SELECT rol FROM usuarios WHERE id = ?"
            );

            $buscar->bind_param("i", $id);
            $buscar->execute();

            $usuarioEliminar = $buscar->get_result()->fetch_assoc();
            $buscar->close();

            if (!$usuarioEliminar) {
                throw new Exception("El usuario no existe.");
            }

            if ($usuarioEliminar["rol"] === "Administrador") {

                $cantidad = $conexion->query(
                    "SELECT COUNT(*) AS total
                     FROM usuarios
                     WHERE rol = 'Administrador'"
                )->fetch_assoc()["total"];

                if ((int)$cantidad <= 1) {
                    throw new Exception("No podés eliminar al último administrador.");
                }
            }

            $sql = $conexion->prepare(
                "DELETE FROM usuarios WHERE id = ?"
            );

            $sql->bind_param("i", $id);
            $sql->execute();

            if ($sql->affected_rows > 0) {
                $mensaje = "Usuario eliminado correctamente.";
            } else {
                throw new Exception("No se pudo eliminar el usuario.");
            }

            $sql->close();
        }

        else {
            throw new Exception("Acción no válida.");
        }

    } catch (mysqli_sql_exception $e) {

        if ($e->getCode() == 1451) {
            $error = "No se puede eliminar este usuario porque tiene información relacionada en el sistema.";
        } else {
            $error = "Ocurrió un problema al realizar la operación.";
        }

    } catch (Exception $e) {

        $error = $e->getMessage();
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
        FROM usuarios
        WHERE id = ?"
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
    FROM usuarios
    ORDER BY id DESC"
);

$totalUsuarios = $resultado->num_rows;

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>FLEM | Gestión de usuarios</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>

        body {
            background: #f4f8f6;
            color: #26352f;
            font-family: Arial, sans-serif;
        }

        .navbar-flem {
            background: #075c45;
            padding: 14px 0;
        }

        .brand-symbol {
            color: #d4af37;
            font-size: 28px;
            margin-right: 8px;
        }

        .brand-name {
            color: white;
            font-weight: 700;
            letter-spacing: 2px;
            font-size: 22px;
        }

        .contenedor {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 18px;
        }

        .welcome-card {
            background: linear-gradient(135deg, #075c45, #0a7658);
            color: white;
            border: none;
            border-radius: 20px;
            margin-bottom: 30px;
        }

        .gold {
            color: #d4af37;
        }

        .usuarios-card {
            border: none;
            border-radius: 18px;
            overflow: hidden;
        }

        .usuarios-titulo {
            color: #075c45;
        }

        .btn-flem {
            background: #075c45;
            color: white;
            border: none;
        }

        .btn-flem:hover {
            background: #064936;
            color: white;
        }

        .usuarios-tabla thead th {
            background: #075c45;
            color: white;
            white-space: nowrap;
        }

        .usuarios-tabla td {
            vertical-align: middle;
        }

        .form-control,
        .form-select {
            border-radius: 9px;
            padding: 10px 12px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #08775a;
            box-shadow: 0 0 0 .2rem rgba(8,119,90,.12);
        }

        .role-admin {
            background: #d4af37;
            color: #26352f;
        }

        .role-paciente {
            background: #dcebe4;
            color: #075c45;
        }

        footer {
            color: #6c757d;
        }

    </style>

</head>

<body>

    <!-- NAVBAR -->

    <nav class="navbar navbar-flem">

        <div class="container">

            <a href="../panel.php" class="text-decoration-none">

                <span class="brand-symbol">⚕</span>
                <span class="brand-name">FLEM</span>

            </a>

            <a href="../logout.php" class="btn btn-light">
                Cerrar sesión
            </a>

        </div>

    </nav>


    <main class="contenedor">

        <!-- ENCABEZADO -->

        <div class="card welcome-card shadow">

            <div class="card-body p-4 p-md-5">

                <p class="gold fw-semibold mb-2">
                    SISTEMA HOSPITALARIO
                </p>

                <h1 class="fw-bold">
                    Gestión de usuarios
                </h1>

                <p class="mb-0">
                    Administrá los usuarios registrados en FLEM.
                </p>

            </div>

        </div>


        <!-- VOLVER -->

        <div class="mb-4">

            <a href="../panel.php" class="btn btn-secondary">
                ← Volver al panel
            </a>

        </div>


        <!-- MENSAJES -->

        <?php if ($mensaje !== ""): ?>

            <div class="alert alert-success alert-dismissible fade show">
                <?= limpiarDato($mensaje) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

        <?php endif; ?>


        <?php if ($error !== ""): ?>

            <div class="alert alert-danger alert-dismissible fade show">
                <?= limpiarDato($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

        <?php endif; ?>


        <!-- FORMULARIO -->

        <div class="card usuarios-card shadow-sm mb-4">

            <div class="card-body p-4 p-md-5">

                <h4 class="fw-bold usuarios-titulo mb-4">

                    <?= $editar ? "Modificar usuario" : "Agregar usuario" ?>

                </h4>

                <form method="POST" action="gestionUsuarios.php">

                    <input type="hidden" name="csrf"
                           value="<?= limpiarDato($_SESSION["csrf"]) ?>">

                    <input type="hidden" name="accion"
                           value="<?= $editar ? "modificar" : "agregar" ?>">

                    <?php if ($editar): ?>

                        <input type="hidden" name="id"
                               value="<?= (int)$editar["id"] ?>">

                    <?php endif; ?>


                    <div class="row g-3">

                        <div class="col-md-6">

                            <label class="form-label fw-semibold">Nombre</label>

                            <input type="text" name="nombre"
                                   class="form-control"
                                   value="<?= limpiarDato($editar["nombre"] ?? "") ?>"
                                   required>

                        </div>


                        <div class="col-md-6">

                            <label class="form-label fw-semibold">Apellido</label>

                            <input type="text" name="apellido"
                                   class="form-control"
                                   value="<?= limpiarDato($editar["apellido"] ?? "") ?>"
                                   required>

                        </div>


                        <div class="col-md-6">

                            <label class="form-label fw-semibold">Cédula</label>

                            <input type="text" name="cedula"
                                   class="form-control"
                                   value="<?= limpiarDato($editar["cedula"] ?? "") ?>"
                                   required>

                        </div>


                        <div class="col-md-6">

                            <label class="form-label fw-semibold">Edad</label>

                            <input type="number" name="edad"
                                   class="form-control"
                                   min="1" max="120"
                                   value="<?= limpiarDato($editar["edad"] ?? "") ?>"
                                   required>

                        </div>


                        <div class="col-md-6">

                            <label class="form-label fw-semibold">Teléfono</label>

                            <input type="text" name="telefono"
                                   class="form-control"
                                   value="<?= limpiarDato($editar["telefono"] ?? "") ?>"
                                   required>

                        </div>


                        <div class="col-md-6">

                            <label class="form-label fw-semibold">Correo electrónico</label>

                            <input type="email" name="correo"
                                   class="form-control"
                                   value="<?= limpiarDato($editar["correo"] ?? "") ?>"
                                   required>

                        </div>


                        <div class="col-md-6">

                            <label class="form-label fw-semibold">Contraseña</label>

                            <input type="password" name="password"
                                   class="form-control"
                                   <?= $editar ? "" : "required" ?>
                                   placeholder="<?= $editar ? "Dejar vacío para mantener la actual" : "Ingresar contraseña" ?>">

                        </div>


                        <div class="col-md-6">

                            <label class="form-label fw-semibold">Rol</label>

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


                    <div class="mt-4 d-flex gap-2 flex-wrap">

                        <button type="submit" class="btn btn-flem px-4">

                            <?= $editar ? "Guardar cambios" : "+ Agregar usuario" ?>

                        </button>

                        <?php if ($editar): ?>

                            <a href="gestionUsuarios.php" class="btn btn-secondary">
                                Cancelar
                            </a>

                        <?php endif; ?>

                    </div>

                </form>

            </div>

        </div>


        <!-- TABLA DE USUARIOS -->

        <div class="card usuarios-card shadow-sm">

            <div class="card-body p-4">

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">

                    <h4 class="fw-bold usuarios-titulo mb-0">
                        Usuarios registrados
                    </h4>

                    <span class="badge role-admin fs-6 px-3 py-2">
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

                            <?php while ($usuario = $resultado->fetch_assoc()): ?>

                                <tr>

                                    <td><?= (int)$usuario["id"] ?></td>

                                    <td><?= limpiarDato($usuario["nombre"]) ?></td>

                                    <td><?= limpiarDato($usuario["apellido"]) ?></td>

                                    <td><?= limpiarDato($usuario["cedula"]) ?></td>

                                    <td><?= (int)$usuario["edad"] ?></td>

                                    <td><?= limpiarDato($usuario["telefono"]) ?></td>

                                    <td><?= limpiarDato($usuario["correo"]) ?></td>

                                    <td>

                                        <span class="badge <?= $usuario["rol"] === "Administrador" ? "role-admin" : "role-paciente" ?>">

                                            <?= limpiarDato($usuario["rol"]) ?>

                                        </span>

                                    </td>

                                    <td>

                                        <a href="gestionUsuarios.php?editar=<?= (int)$usuario["id"] ?>"
                                           class="btn btn-warning btn-sm mb-1">

                                            Editar

                                        </a>


                                        <form method="POST"
                                              action="gestionUsuarios.php"
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

                            <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </main>


    <!-- FOOTER -->

    <footer class="text-center py-4">

        <div class="fw-bold">⚕ FLEM</div>

        <div>Sistema Hospitalario</div>

    </footer>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>

<?php

$conexion->close();

?>
