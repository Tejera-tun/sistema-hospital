
<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = $_POST["nombre"];
    $apellido = $_POST["apellido"];
    $cedula = $_POST["cedula"];
    $edad = $_POST["edad"];
    $telefono = $_POST["telefono"];
    $direccion = $_POST["direccion"];
    $correo = $_POST["correo"];
    $fecha_nacimiento = $_POST["fecha_nacimiento"];
    $genero = $_POST["genero"];

    echo "<h1>Paciente registrado correctamente</h1>";

    echo "Nombre: " . $nombre . "<br>";
    echo "Apellido: " . $apellido . "<br>";
    echo "Cédula: " . $cedula . "<br>";
    echo "Edad: " . $edad . "<br>";
    echo "Teléfono: " . $telefono . "<br>";
    echo "Dirección: " . $direccion . "<br>";
    echo "Correo: " . $correo . "<br>";
    echo "Fecha de nacimiento: " . $fecha_nacimiento . "<br>";
    echo "Género: " . $genero;

} else {

    echo "No se han recibido datos del formulario.";

}

?>