<?php

session_start();

require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (
        !isset($_POST["nombre"]) ||
        !isset($_POST["apellido"]) ||
        !isset($_POST["cedula"]) ||
        !isset($_POST["edad"]) ||
        !isset($_POST["telefono"]) ||
        !isset($_POST["correo"]) ||
        !isset($_POST["password"])
    ) {
        die("Error: faltan datos del formulario.");
    }

    $nombre = trim($_POST["nombre"]);
    $apellido = trim($_POST["apellido"]);
    $cedula = trim($_POST["cedula"]);
    $edad = $_POST["edad"];
    $telefono = trim($_POST["telefono"]);
    $correo = trim($_POST["correo"]);
    $password = $_POST["password"];

    if (
        empty($nombre) ||
        empty($apellido) ||
        empty($cedula) ||
        empty($edad) ||
        empty($telefono) ||
        empty($correo) ||
        empty($password)
    ) {
        die("Error: todos los campos son obligatorios.");
    }

    if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/", $nombre)) {
        die("Error: el nombre solo puede contener letras.");
    }

    if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/", $apellido)) {
        die("Error: el apellido solo puede contener letras.");
    }

    $edad_validada = filter_var($edad, FILTER_VALIDATE_INT);

    if ($edad_validada === false || $edad_validada < 0 || $edad_validada > 120) {
        die("Error: la edad no es válida.");
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        die("Error: el correo electrónico no es válido.");
    }

    $consulta = $conexion->prepare(
        "SELECT id FROM usuarios WHERE cedula = ? OR correo = ?"
    );

    $consulta->bind_param("ss", $cedula, $correo);
    $consulta->execute();

    $resultado = $consulta->get_result();

    if ($resultado->num_rows > 0) {
        die("Error: la cédula o el correo ya están registrados.");
    }

    $password_segura = password_hash($password, PASSWORD_DEFAULT);

    $rol = "Paciente";

    $insertar = $conexion->prepare(
        "INSERT INTO usuarios
        (nombre, apellido, cedula, correo, edad, password, rol, telefono)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $insertar->bind_param(
        "ssssisss",
        $nombre,
        $apellido,
        $cedula,
        $correo,
        $edad_validada,
        $password_segura,
        $rol,
        $telefono
    );

    if ($insertar->execute()) {

        $id_usuario = $conexion->insert_id;

        $_SESSION["id"] = $id_usuario;
        $_SESSION["nombre"] = $nombre;
        $_SESSION["apellido"] = $apellido;
        $_SESSION["correo"] = $correo;
        $_SESSION["rol"] = $rol;

        header("Location: panel.php");
        exit;

    } else {

        echo "Error al registrar el paciente: " . $conexion->error;
    }

} else {

    echo "No se han recibido datos del formulario.";
}

?>