let tareaActual = null;

// Drag & drop
function inicializarDragAndDrop() {
  const tareas = document.querySelectorAll(".tarea");
  const columnas = document.querySelectorAll(".contenedor-tareas");

  tareas.forEach(tarea => {
    tarea.removeEventListener("dragstart", dragStart);
    tarea.removeEventListener("dragend", dragEnd);
    tarea.addEventListener("dragstart", dragStart);
    tarea.addEventListener("dragend", dragEnd);
  });

  columnas.forEach(col => {
    col.removeEventListener("dragover", dragOver);
    col.removeEventListener("dragenter", dragEnter);
    col.removeEventListener("dragleave", dragLeave);
    col.removeEventListener("drop", drop);

    col.addEventListener("dragover", dragOver);
    col.addEventListener("dragenter", dragEnter);
    col.addEventListener("dragleave", dragLeave);
    col.addEventListener("drop", drop);
  });
}

function dragStart() { tareaActual = this; this.classList.add("dragging"); }
function dragEnd() { this.classList.remove("dragging"); tareaActual = null; guardarEstado(); }
function dragOver(e) { e.preventDefault(); }
function dragEnter(e) { e.preventDefault(); this.classList.add("drag-over"); }
function dragLeave() { this.classList.remove("drag-over"); }
function drop() {
  this.classList.remove("drag-over");
  if (tareaActual && this.children.length < 11) {
    this.appendChild(tareaActual);
    guardarEstado();
  } else if (tareaActual) {
    alert("No se pueden añadir más de 11 tareas en esta columna.");
  }
}

// Crear columna
function crearColumna(titulo = "Nueva columna") {
  const tablero = document.querySelector(".tablero");
  if (!tablero) return;

  const col = document.createElement("div");
  col.classList.add("columna");
  col.innerHTML = `
    <div class="header-columna">
      <h2>${titulo}</h2>
      <button class="editar-columna">Editar</button>
      <button class="borrar-columna">Borrar</button>
    </div>
    <div class="contenedor-tareas"></div>
  `;
  tablero.appendChild(col);

  const btnBorrar = col.querySelector(".borrar-columna");
  const btnEditar = col.querySelector(".editar-columna");

  btnBorrar.addEventListener("click", () => { col.remove(); guardarEstado(); });
  btnEditar.addEventListener("click", () => {
    const nuevoTitulo = prompt("Introduce el nuevo título:", col.querySelector("h2").textContent);
    if (nuevoTitulo && nuevoTitulo.trim() !== "") {
      col.querySelector("h2").textContent = nuevoTitulo;
      guardarEstado();
    }
  });

  inicializarDragAndDrop();
}

// Crear nueva tarea en cualquier columna
function crearTarea(columna) {
  if (!columna) return;
  if (columna.children.length >= 11) {
    alert("No se pueden añadir más de 11 tareas en esta columna.");
    return;
  }

  const tarea = document.createElement("div");
  tarea.classList.add("tarea");
  tarea.setAttribute("draggable", "true");
  tarea.textContent = `Incidencia ${Math.floor(Math.random() * 1000)}`;
  columna.appendChild(tarea);

  inicializarDragAndDrop();
  guardarEstado();
}

// Guardar estado en savestatus.php
function guardarEstado() {
  const columnas = document.querySelectorAll(".columna");
  const estado = [];
  columnas.forEach(col => {
    const titulo = col.querySelector("h2").textContent;
    const tareas = Array.from(col.querySelectorAll(".tarea")).map(t => t.textContent);
    estado.push({ titulo, tareas });
  });

  fetch('../template/savestatus.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(estado)
  });
}

function cargarEstado(estadoGuardado) {
  if (!Array.isArray(estadoGuardado)) return;
  estadoGuardado.forEach(col => {
    crearColumna(col.titulo);
    const contenedor = document.querySelectorAll(".contenedor-tareas")[document.querySelectorAll(".contenedor-tareas").length - 1];
    col.tareas.forEach(t => {
      if (contenedor.children.length < 11) {
        const tarea = document.createElement("div");
        tarea.classList.add("tarea");
        tarea.setAttribute("draggable", "true");
        tarea.textContent = t;
        contenedor.appendChild(tarea);
      }
    });
  });
}

// Esperar a DOM cargado
document.addEventListener("DOMContentLoaded", () => {
  // Botón nueva columna
  document.getElementById("nuevaColumna").addEventListener("click", () => crearColumna());

  // Botón nueva tarea añade a la primera columna
  document.getElementById("nuevaTarea").addEventListener("click", () => {
    const primera = document.querySelector(".contenedor-tareas");
    if (primera) crearTarea(primera);
  });

  // Cargar estado desde PHP
  cargarEstado(estadoGuardado);
});
