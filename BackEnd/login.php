<?php

session_start();

require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_POST["correo"]) || !isset($_POST["password"])) {
        die("Error: faltan datos.");
    }

    $correo = trim($_POST["correo"]);
    $password = $_POST["password"];

    if (empty($correo) || empty($password)) {
        die("Error: el correo y la contraseña son obligatorios.");
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        die("Error: el correo electrónico no es válido.");
    }

    $consulta = $conexion->prepare(
        "SELECT id, nombre, apellido, correo, password, rol FROM usuarios WHERE correo = ?"
    );

    $consulta->bind_param("s", $correo);
    $consulta->execute();

    $resultado = $consulta->get_result();

    if ($resultado->num_rows == 0) {
        die("Error: el correo o la contraseña son incorrectos.");
    }

    $usuario = $resultado->fetch_assoc();

    if (!password_verify($password, $usuario["password"])) {
        die("Error: el correo o la contraseña son incorrectos.");
    }

    $_SESSION["id"] = $usuario["id"];
    $_SESSION["nombre"] = $usuario["nombre"];
    $_SESSION["apellido"] = $usuario["apellido"];
    $_SESSION["correo"] = $usuario["correo"];
    $_SESSION["rol"] = $usuario["rol"];

header("Location: panel.php");
exit;
}
 else {

    echo "No se han recibido datos del formulario.";
}

?>