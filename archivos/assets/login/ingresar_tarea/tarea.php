<?php
session_start();
header("Content-Type: application/json");

// Config DB
$conexion = new mysqli("localhost", "root", "", "kanban");
if ($conexion->connect_error) {
    echo json_encode(["success" => false, "error" => "Error de conexión a la base de datos"]);
    exit;
}

// Acción enviada
$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

// Verificar sesión
$idUnico = $_SESSION['id_unico'] ?? null;
if (!$idUnico) {
    echo json_encode(["success" => false, "error" => "Usuario no autenticado"]);
    exit;
}

// Obtener idUsuarios
$res = $conexion->query("SELECT idUsuarios FROM usuario WHERE id_unico = '$idUnico' LIMIT 1");
if (!$res || $res->num_rows == 0) {
    echo json_encode(["success" => false, "error" => "Usuario no encontrado en la base de datos"]);
    exit;
}
$row = $res->fetch_assoc();
$idUsuario = $row['idUsuarios'];

switch ($accion) {
    case 'agregar':
        $titulo = $conexion->real_escape_string($_POST['titulo_tarea'] ?? '');
        $descripcion = $conexion->real_escape_string($_POST['descripcion_tarea'] ?? '');
        $fecha_creacion = $conexion->real_escape_string($_POST['fecha_creacion_tarea'] ?? '');
        $fecha_vencimiento = $conexion->real_escape_string($_POST['fecha_vencimiento_tarea'] ?? '');
        $estado = $conexion->real_escape_string($_POST['estado_tarea'] ?? 'Por hacer');

        $sql = "INSERT INTO tarea (titulo_tarea, descripcion_tarea, fecha_creacion_tarea, fecha_vencimiento_tarea, USUARIO_idUsuarios, estado_tarea)
                VALUES ('$titulo', '$descripcion', '$fecha_creacion', '$fecha_vencimiento', '$idUsuario', '$estado')";

        if ($conexion->query($sql)) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => $conexion->error]);
        }
        break;

    case 'listar':
        $sql = "SELECT * FROM tarea WHERE USUARIO_idUsuarios = '$idUsuario'";
        $result = $conexion->query($sql);

        $tareas = [];
        while ($fila = $result->fetch_assoc()) {
            $tareas[] = $fila;
        }

        echo json_encode(["success" => true, "tareas" => $tareas]);
        break;

    case 'editar':
        $idTarea = intval($_POST['idTareas'] ?? 0);
        $titulo = $conexion->real_escape_string($_POST['titulo_tarea'] ?? '');
        $descripcion = $conexion->real_escape_string($_POST['descripcion_tarea'] ?? '');
        $fecha_vencimiento = $conexion->real_escape_string($_POST['fecha_vencimiento_tarea'] ?? '');
        $estado = $conexion->real_escape_string($_POST['estado_tarea'] ?? '');

        if ($idTarea <= 0) {
            echo json_encode(["success" => false, "error" => "ID de tarea no válido"]);
            exit;
        }

        $sql = "UPDATE tarea 
                SET titulo_tarea='$titulo', 
                    descripcion_tarea='$descripcion',
                    fecha_vencimiento_tarea='$fecha_vencimiento',
                    estado_tarea='$estado'
                WHERE idTareas='$idTarea' AND USUARIO_idUsuarios='$idUsuario'";

        if ($conexion->query($sql)) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => $conexion->error]);
        }
        break;

    // ← Añadimos este case para eliminar
    case 'eliminar':
        $idTarea = intval($_POST['idTareas'] ?? 0);
        if ($idTarea <= 0) {
            echo json_encode(["success" => false, "error" => "ID de tarea no válido"]);
            exit;
        }

        // Opcional: verificar que la tarea pertenece al usuario (ya lo hacemos en WHERE)
        $sql = "DELETE FROM tarea WHERE idTareas = '$idTarea' AND USUARIO_idUsuarios = '$idUsuario'";
        if ($conexion->query($sql)) {
            // affected_rows > 0 garantiza que sí borró algo del usuario correcto
            if ($conexion->affected_rows > 0) {
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "error" => "Tarea no encontrada o no te pertenece"]);
            }
        } else {
            echo json_encode(["success" => false, "error" => $conexion->error]);
        }
        break;

    default:
        echo json_encode(["success" => false, "error" => "Acción no válida"]);
        break;
}

$conexion->close();
?>
