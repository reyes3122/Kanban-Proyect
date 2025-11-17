<?php
$servidor = "localhost";
$usuario = "root";
$password = "";
$db = "kanbandb";

$conexion = new mysqli($servidor, $usuario, $password, $db);


// Capturar los datos del formulario
$nombre = $_POST['Nombre'];
$email = $_POST['Email'];
$mensaje = $_POST['Mensaje'];

// Insertar los datos en la base de datos
$sql = "INSERT INTO contacto (Nombre, Email, Mensaje) 
        VALUES ('$nombre', '$email', '$mensaje')";

if ($conexion->query($sql) === TRUE) {
    echo "<script>
            alert('✅ Tu mensaje fue enviado correctamente.');
            window.history.back();
          </script>";
} else {
    echo "<script>
            alert('❌ Error al enviar el mensaje: " . $conexion->error . "');
            window.history.back();
          </script>";
}

$conexion->close();

?>