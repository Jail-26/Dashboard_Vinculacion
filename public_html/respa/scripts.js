document.addEventListener("DOMContentLoaded", () => {
  const programasList = document.getElementById("programas-list");
  const proyectosList = document.getElementById("proyectos-list");
  const proyectoDetalles = document.getElementById("proyecto-detalles");
  const totalEstudiantes = document.getElementById("total-estudiantes");
  const totalProfesores = document.getElementById("total-profesores");
  const totalBeneficiarios = document.getElementById("total-beneficiarios");
  const totalProyectos = document.getElementById("total-proyectos");
  let map; // Variable global para el mapa
  let proyectoSeleccionado = null; // Variable para rastrear el proyecto seleccionado
  let programaSeleccionado = null; // Variable para rastrear el programa seleccionado
  let datosIniciales = null; // Variable para almacenar los datos iniciales

  // Cargar programas y proyectos
  fetch("api/datos.php")
    .then((response) => response.json())
    .then((data) => {
      datosIniciales = data; // Guardar los datos iniciales
      cargarDatosIniciales(data);
    })
    .catch((error) => console.error("Error al cargar los datos:", error));

  // Cargar los datos iniciales
  function cargarDatosIniciales(data) {
  let sumaEstudiantes = 0;
  let sumaBeneficiarios = 0;
  let totalProfesoresReales = data.total_profesores !== undefined && !isNaN(data.total_profesores)
    ? data.total_profesores
    : 46;

  programasList.innerHTML = "";
  data.programas.forEach((programa) => {
    const li = document.createElement("li");
    li.className = "list-group-item";
    li.textContent = programa.nombre;
    li.dataset.programaId = programa.id_programa;
    li.addEventListener("click", () => toggleSeleccionPrograma(programa, li));
    programasList.appendChild(li);
  });

  proyectosList.innerHTML = "";
  data.proyectos.forEach((proyecto) => {
    sumaEstudiantes += proyecto.cantidad_estudiantes || 0;
    sumaBeneficiarios += proyecto.cantidad_beneficiados || 0;

    const li = document.createElement("li");
    li.className = "list-group-item";
    li.textContent = proyecto.nombre;
    li.addEventListener("click", () => toggleSeleccionProyecto(proyecto, li));
    proyectosList.appendChild(li);
  });

  totalEstudiantes.textContent = sumaEstudiantes;
  totalProfesores.textContent = totalProfesoresReales;
  totalBeneficiarios.textContent = sumaBeneficiarios;
  totalProyectos.textContent = data.proyectos.length;

  actualizarBarrasProgreso(
    {
      estudiantes: sumaEstudiantes,
      profesores: totalProfesoresReales,
      beneficiarios: sumaBeneficiarios,
      proyectos: data.proyectos.length,
    },
    {
      estudiantes: Math.max(...data.proyectos.map((p) => p.cantidad_estudiantes || 0), 1),
      profesores: totalProfesoresReales,
      beneficiarios: Math.max(...data.proyectos.map((p) => p.cantidad_beneficiados || 0), 1),
      proyectos: data.proyectos.length || 1,
    }
  );
    // Mostrar detalles iniciales
    proyectoDetalles.innerHTML = `
            <img src="images/comunidad.webp" alt="comnidad nelson torres">

                <h5>Instituto Superior Nelson Torres</h5>
                <p>
                    El Instituto Superior Nelson Torres, a través de su unidad de vinculación con la sociedad, 
                    ha contribuido significativamente al desarrollo social mediante proyectos que impactan 
                    positivamente en las comunidades. Su compromiso con la educación y el bienestar social 
                    lo posiciona como un referente en la región.
                </p>
            `;

    // Cargar mapa por defecto (Ecuador)
    cargarMapaEcuador();
  }

  // Alternar selección de programa
  function toggleSeleccionPrograma(programa, elemento) {
    if (programaSeleccionado === programa) {
      // Deseleccionar el programa
      programaSeleccionado = null;
      elemento.classList.remove("selected");
      cargarDatosIniciales(datosIniciales); // Restaurar los datos iniciales
    } else {
      // Seleccionar un nuevo programa
      if (programaSeleccionado) {
        // Deseleccionar el programa anterior
        const elementos = programasList.querySelectorAll(".list-group-item");
        elementos.forEach((el) => el.classList.remove("selected"));
      }
      programaSeleccionado = programa;
      elemento.classList.add("selected");
      filtrarProyectosPorPrograma(programa.id_programa);
    }
  }

  // Filtrar proyectos por programa
  function filtrarProyectosPorPrograma(idPrograma) {
    console.log("ID del programa seleccionado:", idPrograma);
    console.log("Proyectos disponibles:", datosIniciales.proyectos);

    // Filtrar los proyectos que pertenecen al programa seleccionado
    const proyectosFiltrados = datosIniciales.proyectos.filter((proyecto) => {
      console.log(
        `Comparando proyecto ${proyecto.nombre} con id_programa ${proyecto.id_programa}`
      );
      return proyecto.id_programa == idPrograma; // Usar comparación flexible para evitar problemas de tipo
    });

    // Verificar si hay proyectos filtrados
    if (proyectosFiltrados.length === 0) {
      proyectosList.innerHTML =
        "<p>No hay proyectos asociados a este programa.</p>";
      return;
    }

    // Mostrar proyectos filtrados
    proyectosList.innerHTML = "";
    let sumaEstudiantes = 0;
    let sumaProfesores = 0;
    let sumaBeneficiarios = 0;

    proyectosFiltrados.forEach((proyecto) => {
      sumaEstudiantes += proyecto.cantidad_estudiantes || 0;
      sumaProfesores += proyecto.cantidad_profesores || 0;
      sumaBeneficiarios += proyecto.cantidad_beneficiados || 0;

      const li = document.createElement("li");
      li.className = "list-group-item";
      li.textContent = proyecto.nombre;
      li.addEventListener("click", () => toggleSeleccionProyecto(proyecto, li));
      proyectosList.appendChild(li);
    });

    // Actualizar estadísticas
    totalEstudiantes.textContent = sumaEstudiantes;
    totalProfesores.textContent = sumaProfesores;
    totalBeneficiarios.textContent = sumaBeneficiarios;
    totalProyectos.textContent = proyectosFiltrados.length; // Total de proyectos filtrados

    const maximos = {
      estudiantes: Math.max(
        ...datosIniciales.proyectos.map((p) => p.cantidad_estudiantes || 0),
        1
      ),
      profesores: Math.max(
        ...datosIniciales.proyectos.map((p) => p.cantidad_profesores || 0),
        1
      ),
      beneficiarios: Math.max(
        ...datosIniciales.proyectos.map((p) => p.cantidad_beneficiados || 0),
        1
      ),
      proyectos: datosIniciales.proyectos.length || 1,
    };
    actualizarBarrasProgreso(
      {
        estudiantes: sumaEstudiantes,
        profesores: sumaProfesores,
        beneficiarios: sumaBeneficiarios,
        proyectos: proyectosFiltrados.length,
      },
      maximos
    );

    // Limpiar detalles y mapa
    proyectoDetalles.innerHTML =
      "<p>Selecciona un proyecto para ver los detalles.</p>";
    cargarMapaEcuador();
  }

  // Alternar selección de proyecto
  function toggleSeleccionProyecto(proyecto, elemento) {
    // Siempre mostrar detalles del proyecto seleccionado
    if (proyectoSeleccionado) {
      // Deseleccionar el proyecto anterior
      const elementos = proyectosList.querySelectorAll(".list-group-item");
      elementos.forEach((el) => el.classList.remove("selected"));
    }
    proyectoSeleccionado = proyecto;
    elemento.classList.add("selected");
    mostrarDetallesProyecto(proyecto);
  }

  
  function mostrarToast(mensaje) {
  const toast = document.getElementById("toast-mensaje");
  toast.textContent = mensaje;
  toast.style.opacity = "1";

  setTimeout(() => {
    toast.style.opacity = "0";
  }, 3000); // Ocultar después de 3 segundos
}
  // Mostrar detalles del proyecto y actualizar estadísticas
  function mostrarDetallesProyecto(proyecto) {
    proyectoDetalles.innerHTML = `
        <div style="display: flex; justify-content:space-between; align-items: center; gap: 8px;">
            <button id="btn-expandir-detalles" class="btn-expandir">
                <img style="margin:0;" class="img-expandir" src="images/expandir_gris.png" alt="" width="24px" height="24px">
            </button>
            <button id="btn-compartir" class="btn-expandir" title="Compartir enlace del proyecto">
              <img style="margin:0;" class="img-expandir" src="images/compartir_gris.png" alt="Compartir" width="24px" height="24px">
          </button>
            ${
              proyecto.pdf_url
                ? `<a href="${proyecto.pdf_url}" target="_blank" class="login-btn" ;">Ver Acta PDF</a>`
                : ""
            }
        </div>
        <h5>${proyecto.nombre}</h5>
        ${
          proyecto.imagen_url
            ? `<img src="${proyecto.imagen_url}" alt="${proyecto.nombre}" class="img-fluid mb-3" style="width: 100%;">`
            : ""
        }
        <span class="lbl-estado">${proyecto.estado}</span>
        <span class="lbl-estado">${proyecto.fase}</span>
        ${proyecto.descripcion_extendida}
        <hr>
        <p>Sí deseas más información sobre el proyecto, puedes escribirnos a:
        <a href="mailto:vinculacion@intsuperior.edu.ec?subject=Quiero más información sobre el proyecto de ${
          proyecto.nombre
        }">vinculacion@intsuperior.edu.ec</a></p>
    `;

    // Actualizar estadísticas del proyecto seleccionado
    totalEstudiantes.textContent = proyecto.cantidad_estudiantes || 0;
    totalProfesores.textContent = proyecto.cantidad_profesores || 0;
    totalBeneficiarios.textContent = proyecto.cantidad_beneficiados || 0;

    const maximos = {
      estudiantes: Math.max(
        ...datosIniciales.proyectos.map((p) => p.cantidad_estudiantes || 0),
        1
      ),
      profesores: Math.max(
        ...datosIniciales.proyectos.map((p) => p.cantidad_profesores || 0),
        1
      ),
      beneficiarios: Math.max(
        ...datosIniciales.proyectos.map((p) => p.cantidad_beneficiados || 0),
        1
      ),
      proyectos: datosIniciales.proyectos.length || 1,
    };
    actualizarBarrasProgreso(
      {
        estudiantes: proyecto.cantidad_estudiantes || 0,
        profesores: proyecto.cantidad_profesores || 0,
        beneficiarios: proyecto.cantidad_beneficiados || 0,
        proyectos: 1,
      },
      maximos
    );

    // Cargar mapa del proyecto
    cargarMapa(proyecto);

    // Evento para expandir detalles
    document.getElementById("btn-expandir-detalles").onclick = function () {
      mostrarModalDetalles(proyecto);
    };
    document.getElementById("btn-compartir").onclick = function () {
  const url = `${window.location.origin}/proyecto.php?id=${proyecto.id_proyecto}`;
 navigator.clipboard.writeText(url).then(() => {
  mostrarToast("✅ Enlace copiado al portapapeles");
}).catch(() => {
  mostrarToast("❌ No se pudo copiar el enlace");
});
};
  }

  // Mostrar modal con detalles ampliados
  function mostrarModalDetalles(proyecto) {
    const modal = document.getElementById("modal-detalles-proyecto");
    const contenido = document.getElementById("modal-detalles-contenido");
    contenido.innerHTML = `
                <h2>${proyecto.nombre}</h2>
                ${
                  proyecto.imagen_url
                    ? `<img src="${proyecto.imagen_url}" alt="${proyecto.nombre}" class="img-fluid mb-3" style="width: 100%;">`
                    : ""
                }
          <div style="display: flex; justify-content:space-between; align-items: center; gap: 8px;">
          <div style="display: flex; align-items: center; gap: 8px;"  >
            <span class="lbl-estado">${proyecto.estado}</span>
            <span class="lbl-estado">${proyecto.fase}</span>
          </div>
            ${
              proyecto.pdf_url
                ? `<a href="${proyecto.pdf_url}" target="_blank" class="lbl-estado login-btn pdf-btn">Ver Acta PDF</a>`
                : ""
            }
          </div>
                <div>${proyecto.descripcion_extendida}</div>
                <hr>
                <p><strong>Estudiantes:</strong> ${
                  proyecto.cantidad_estudiantes || 0
                }</p>
                <p><strong>Profesores:</strong> ${
                  proyecto.cantidad_profesores || 0
                }</p>
                <p><strong>Beneficiarios:</strong> ${
                  proyecto.cantidad_beneficiados || 0
                }</p>
                <hr>
        <p>Sí deseas más información sobre el proyecto, puedes escribirnos a:
        <a href="mailto:vinculacion@intsuperior.edu.ec?subject=Quiero más información sobre el proyecto de ${
          proyecto.nombre
        }">vinculacion@intsuperior.edu.ec</a></p>
            `;
    modal.style.display = "flex";
  }

  // Cerrar modal al hacer clic en la X o fuera del contenido
  document.getElementById("cerrar-modal").onclick = function () {
    document.getElementById("modal-detalles-proyecto").style.display = "none";
  };
  document.getElementById("modal-detalles-proyecto").onclick = function (e) {
    if (e.target === this) this.style.display = "none";
  };

  // Cargar mapa con estadísticas globales (Ecuador)
  function cargarMapaEcuador() {
    if (map) {
      map.remove(); // Destruir el mapa existente si ya está inicializado
    }

    map = L.map("map").setView([-1.831239, -78.183406], 6); // Coordenadas de Ecuador

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      maxZoom: 19,
    }).addTo(map);

    L.marker([-1.831239, -78.183406])
      .addTo(map)
      .bindPopup(`<b>Ecuador</b><br>Estadísticas globales.`)
      .openPopup();
  }

  // Cargar mapa con estadísticas del proyecto
  function cargarMapa(proyecto) {
    if (map) {
      map.remove(); // Destruir el mapa existente si ya está inicializado
    }

    map = L.map("map").setView(
      [proyecto.entidad_latitud, proyecto.entidad_longitud],
      17
    );

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      maxZoom: 19,
    }).addTo(map);

    L.marker([proyecto.entidad_latitud, proyecto.entidad_longitud])
      .addTo(map)
      .bindPopup(
        `<b>${proyecto.nombre}</b><br>${proyecto.cantidad_beneficiados} beneficiarios.`
      )
      .openPopup();
  }

  function actualizarBarrasProgreso(valores, maximos) {
    document.getElementById("progress-estudiantes").max = maximos.estudiantes;
    document.getElementById("progress-estudiantes").value = valores.estudiantes;

    document.getElementById("progress-profesores").max = maximos.profesores;
    document.getElementById("progress-profesores").value = valores.profesores;

    document.getElementById("progress-beneficiarios").max =
      maximos.beneficiarios;
    document.getElementById("progress-beneficiarios").value =
      valores.beneficiarios;

    document.getElementById("progress-proyectos").max = maximos.proyectos;
    document.getElementById("progress-proyectos").value = valores.proyectos;
  }
});
