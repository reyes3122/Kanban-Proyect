document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("taskForm");
  const btnSubmit = form.querySelector("button[type='submit']");

  // Columnas del tablero
  const columnas = {
    "Por hacer": document.getElementById("tareas-por-hacer"),
    "En progreso": document.getElementById("tareas-en-progreso"),
    "Finalizado": document.getElementById("tareas-finalizado"),
  };

  let editandoId = null; // para saber si estamos editando una tarea

  // 🔹 Cargar tareas al inicio
  cargarTareas();

  // ============================
  // 🔹 Función principal: cargar tareas
  // ============================
  function cargarTareas() {
    fetch("tarea.php?accion=listar")
      .then(res => res.text())
      .then(data => {
        console.log("Respuesta listar:", data);
        let json;
        try {
          json = JSON.parse(data);
        } catch (e) {
          alert("Error al interpretar respuesta del servidor.");
          console.error(e);
          return;
        }

        if (json.success) {
          Object.values(columnas).forEach(col => col.innerHTML = "");
          json.tareas.forEach(t => {
            agregarTarea(
              t.idTareas,
              t.titulo_tarea,
              t.descripcion_tarea,
              t.fecha_creacion_tarea,
              t.fecha_vencimiento_tarea,
              t.estado_tarea
            );
          });
        } else {
          console.error("Error al listar:", json.error);
        }
      })
      .catch(err => console.error("Error al conectar con tarea.php:", err));
  }

  // ============================
  // 🔹 Agregar o editar tarea
  // ============================
  form.addEventListener("submit", (e) => {
    e.preventDefault();

    const titulo = document.getElementById("Titulo").value.trim();
    const descripcion = document.getElementById("descripcion").value.trim();
    const fechaCreacion = form.querySelector('input[name="fecha_creacion"]').value;
    const fechaVencimiento = form.querySelector('input[name="fecha_vencimiento"]').value;
    const estado = document.getElementById("Estado").value;

    if (!titulo || !descripcion || !fechaCreacion || !fechaVencimiento) {
      alert("Completa todos los campos.");
      return;
    }

    const accion = editandoId ? "editar" : "agregar";

    const datos = new URLSearchParams({
      accion: accion,
      titulo_tarea: titulo,
      descripcion_tarea: descripcion,
      fecha_creacion_tarea: fechaCreacion,
      fecha_vencimiento_tarea: fechaVencimiento,
      estado_tarea: estado,
    });

    if (editandoId) datos.append("idTareas", editandoId);

    fetch("tarea.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: datos
    })
    .then(res => res.text())
    .then(data => {
      console.log("Respuesta guardar:", data);
      let json;
      try {
        json = JSON.parse(data);
      } catch (e) {
        alert("❌ Error al interpretar respuesta del servidor.");
        console.error(e);
        return;
      }

      if (json.success) {
        form.reset();
        btnSubmit.textContent = "Agregar tarea";
        editandoId = null;
        cargarTareas();
      } else {
        alert("Error al guardar: " + json.error);
      }
    })
    .catch(() => alert("Error de conexión con el servidor."));
  });

  // ============================
  // 🔹 Función para mostrar una tarea en la tabla
  // ============================
  function agregarTarea(id, titulo, descripcion, fechaCreacion, fechaVencimiento, estado) {
    const fila = document.createElement("tr");
    fila.dataset.id = id;
    fila.innerHTML = `
      <td>${titulo}</td>
      <td>${descripcion}</td>
      <td>${fechaCreacion}</td>
      <td>${fechaVencimiento}</td>
      <td>
        <button class="editar">✏️</button>
        <button class="eliminar">🗑️</button>
      </td>
    `;

    const columna = columnas[estado] || columnas["Por hacer"];
    columna.appendChild(fila);

    // Botón editar
    fila.querySelector(".editar").addEventListener("click", () => {
      document.getElementById("Titulo").value = titulo;
      document.getElementById("descripcion").value = descripcion;
      form.querySelector('input[name="fecha_creacion"]').value = fechaCreacion;
      form.querySelector('input[name="fecha_vencimiento"]').value = fechaVencimiento;
      document.getElementById("Estado").value = estado;

      editandoId = id;
      btnSubmit.textContent = "Guardar cambios";
    });

    // Botón eliminar
    fila.querySelector(".eliminar").addEventListener("click", () => eliminarTarea(id));
  }

  // ============================
  // 🔹 Eliminar tarea
  // ============================
  function eliminarTarea(id) {
    if (!confirm("¿Eliminar esta tarea?")) return;

    fetch("tarea.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({ accion: "eliminar", idTareas: id })
    })
    .then(res => res.json())
    .then(json => {
      if (json.success) cargarTareas();
      else alert("Error al eliminar: " + json.error);
    })
    .catch(() => alert("Error al conectar con el servidor."));
  }
});
