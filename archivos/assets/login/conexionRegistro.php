<?php
session_start();

// Datos de conexión
$servidor = "localhost";
$usuario = "root";
$password = "";
$db = "kanban"; 

$conexion = new mysqli($servidor, $usuario, $password, $db);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error al conectar: " . $conexion->connect_error);
}

// Verificar que los datos lleguen correctamente
if (isset($_POST['id_registro'], $_POST['nombre_registro'], $_POST['Apellido_registro'], $_POST['Telefono_registro'])) {

    $id_unico = $_POST['id_registro'];
    $nombre = $_POST['nombre_registro'];
    $apellido = $_POST['Apellido_registro'];
    $telefono = $_POST['Telefono_registro'];

    // Evitar inyección SQL
    $id_unico = $conexion->real_escape_string($id_unico);
    $nombre = $conexion->real_escape_string($nombre);
    $apellido = $conexion->real_escape_string($apellido);
    $telefono = $conexion->real_escape_string($telefono);

    // Verificar si el ID único ya existe
    $sql_verificar = "SELECT * FROM usuario WHERE id_unico = '$id_unico'";
    $resultado = $conexion->query($sql_verificar);

    if ($resultado->num_rows > 0) {
        // Ya existe un usuario con ese ID
        echo "<script>
                alert('Ya existe un usuario con este ID único.');
                window.location.href = 'login.html';
              </script>";
    } else {
        // Insertar nuevo usuario
        $insertar = "INSERT INTO usuario (id_unico, nombre_usuario, apellido_usuario, telefono_usuario)
                     VALUES ('$id_unico', '$nombre', '$apellido', '$telefono')";

        if ($conexion->query($insertar) === TRUE) {
            $_SESSION['usuario'] = $nombre;
            echo "<script>
                    alert('Registro exitoso. Bienvenido, $nombre');
                    window.location.href = 'ingresar_tarea/tarea.html';
                  </script>";
        } else {
            echo "<script>
                    alert('Error al registrar usuario: " . $conexion->error . "');
                    window.history.back();
                  </script>";
        }
    }
} else {
    echo "<script>
            alert('Por favor complete todos los campos del formulario.');
            window.history.back();
          </script>";
}

$conexion->close();
?>
