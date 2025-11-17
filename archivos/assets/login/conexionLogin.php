<?php
session_start();

$servidor = "localhost";
$usuario = "root";
$password = "";
$db = "kanban";

$conexion = new mysqli($servidor, $usuario, $password, $db);

if ($conexion->connect_error) {
    die("Error al conectar: " . $conexion->connect_error);
}

// Validar que venga el dato del formulario
if (isset($_POST['usuario_login'])) {

    $id_unico = trim($_POST['usuario_login']);
    $id_unico = $conexion->real_escape_string($id_unico);

    // Buscar el ID único en la base de datos
    $sql = "SELECT * FROM usuario WHERE id_unico = '$id_unico'";
    $resultado = $conexion->query($sql);

    if ($resultado->num_rows > 0) {
        // Usuario encontrado
        $fila = $resultado->fetch_assoc();
        $_SESSION['usuario'] = $fila['nombre_usuario'];  // Nombre
        $_SESSION['id_unico'] = $fila['id_unico'];       // Guardamos el ID también
        $_SESSION['idUsuarios'] = $fila['idUsuarios']; //guardar el id del usuario


        echo "<script>
                alert('Bienvenido, " . $fila['nombre_usuario'] . "');
                window.location.href = 'ingresar_tarea/tarea.html';
              </script>";
    } else {
        // No se encontró usuario
        echo "<script>
                alert('No se encontró un usuario con ese ID. Por favor regístrese para continuar.');
                window.location.href = 'registrar.html';
              </script>";
    }

} else {
    // Si no se envió nada
    echo "<script>
            alert('Por favor ingrese su ID.');
            window.history.back();
          </script>";
}

$conexion->close();
?>
