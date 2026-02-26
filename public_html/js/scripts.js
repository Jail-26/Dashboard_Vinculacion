document.addEventListener("DOMContentLoaded", () => {
  const programasList = document.getElementById("programas-list");
  const proyectosList = document.getElementById("proyectos-list");
  const proyectoDetalles = document.getElementById("proyecto-detalles");
  const totalEstudiantes = document.getElementById("total-estudiantes");
  const totalProfesores = document.getElementById("total-profesores");
  const totalBeneficiarios = document.getElementById("total-beneficiarios");
  const totalProyectos = document.getElementById("total-proyectos");
  let map;
  let proyectoSeleccionado = null;
  let programaSeleccionado = null;
  let datosIniciales = null;

  // Cargar programas y proyectos
  fetch("api/get_data.php")
    .then((response) => response.json())
    .then((data) => {
      datosIniciales = data.data;
      cargarDatosIniciales(data.data);
    })
    .catch((error) => console.error("Error al cargar los datos:", error));

  // Cargar los datos iniciales
  function cargarDatosIniciales(data) {
    let sumaEstudiantes = 0;
    let sumaBeneficiarios = 0;
    let sumaProfesores = 0;

    programasList.innerHTML = "";
    data.forEach((programa) => {
      const li = document.createElement("li");
      li.className = "list-group-item";
      li.textContent = programa.nombre;
      li.dataset.programaId = programa.id_programa;
      li.addEventListener("click", () => toggleSeleccionPrograma(programa, li));
      programasList.appendChild(li);
    });

    proyectosList.innerHTML = "";
    const todosProyectos = data.flatMap(programa => programa.proyectos);
    todosProyectos.forEach((proyecto) => {
      sumaEstudiantes += parseInt(proyecto.total_estudiantes) || 0;
      sumaBeneficiarios += parseInt(proyecto.total_beneficiarios) || 0;
      sumaProfesores += parseInt(proyecto.total_docentes) || 0;

      const li = document.createElement("li");
      li.className = "list-group-item";
      li.textContent = proyecto.nombre;
      li.addEventListener("click", () => toggleSeleccionProyecto(proyecto, li));
      proyectosList.appendChild(li);
    });

    totalEstudiantes.textContent = sumaEstudiantes;
    totalProfesores.textContent = sumaProfesores;
    totalBeneficiarios.textContent = sumaBeneficiarios;
    totalProyectos.textContent = todosProyectos.length;

    actualizarBarrasProgreso(
      {
        estudiantes: sumaEstudiantes,
        profesores: sumaProfesores,
        beneficiarios: sumaBeneficiarios,
        proyectos: todosProyectos.length,
      },
      {
        estudiantes: Math.max(...todosProyectos.map((p) => parseInt(p.total_estudiantes) || 0), 1),
        profesores: Math.max(...todosProyectos.map((p) => parseInt(p.total_docentes) || 0), 1),
        beneficiarios: Math.max(...todosProyectos.map((p) => parseInt(p.total_beneficiarios) || 0), 1),
        proyectos: todosProyectos.length || 1,
      }
    );

    proyectoDetalles.innerHTML = `
      <img src="images/comunidad.webp" alt="comunidad nelson torres">
      <h5>Instituto Superior Nelson Torres</h5>
      <p>
        El Instituto Superior Nelson Torres, a través de su unidad de vinculación con la sociedad, 
        ha contribuido significativamente al desarrollo social mediante proyectos que impactan 
        positivamente en las comunidades. Su compromiso con la educación y el bienestar social 
        lo posiciona como un referente en la región.
      </p>
    `;

    cargarMapaEcuador();
  }

  // Alternar selección de programa
  function toggleSeleccionPrograma(programa, elemento) {
    if (programaSeleccionado === programa) {
      programaSeleccionado = null;
      elemento.classList.remove("selected");
      cargarDatosIniciales(datosIniciales);
    } else {
      if (programaSeleccionado) {
        const elementos = programasList.querySelectorAll(".list-group-item");
        elementos.forEach((el) => el.classList.remove("selected"));
      }
      programaSeleccionado = programa;
      elemento.classList.add("selected");
      filtrarProyectosPorPrograma(programa);
    }
  }

  // Filtrar proyectos por programa
  function filtrarProyectosPorPrograma(programa) {
    const proyectosFiltrados = programa.proyectos || [];

    if (proyectosFiltrados.length === 0) {
      proyectosList.innerHTML = "<p>No hay proyectos asociados a este programa.</p>";
      return;
    }

    proyectosList.innerHTML = "";
    let sumaEstudiantes = 0;
    let sumaProfesores = 0;
    let sumaBeneficiarios = 0;

    proyectosFiltrados.forEach((proyecto) => {
      sumaEstudiantes += parseInt(proyecto.total_estudiantes) || 0;
      sumaProfesores += parseInt(proyecto.total_docentes) || 0;
      sumaBeneficiarios += parseInt(proyecto.total_beneficiarios) || 0;

      const li = document.createElement("li");
      li.className = "list-group-item";
      li.textContent = proyecto.nombre;
      li.addEventListener("click", () => toggleSeleccionProyecto(proyecto, li));
      proyectosList.appendChild(li);
    });

    totalEstudiantes.textContent = sumaEstudiantes;
    totalProfesores.textContent = sumaProfesores;
    totalBeneficiarios.textContent = sumaBeneficiarios;
    totalProyectos.textContent = proyectosFiltrados.length;

    const todosProyectos = datosIniciales.flatMap(p => p.proyectos);
    const maximos = {
      estudiantes: Math.max(...todosProyectos.map((p) => parseInt(p.total_estudiantes) || 0), 1),
      profesores: Math.max(...todosProyectos.map((p) => parseInt(p.total_docentes) || 0), 1),
      beneficiarios: Math.max(...todosProyectos.map((p) => parseInt(p.total_beneficiarios) || 0), 1),
      proyectos: todosProyectos.length || 1,
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

    proyectoDetalles.innerHTML = "<p>Selecciona un proyecto para ver los detalles.</p>";
    cargarMapaEcuador();
  }

  // Alternar selección de proyecto
  function toggleSeleccionProyecto(proyecto, elemento) {
    if (proyectoSeleccionado) {
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
    }, 3000);
  }

  // Mostrar detalles del proyecto
  function mostrarDetallesProyecto(proyecto) {
    proyectoDetalles.innerHTML = `
      <div style="display: flex; justify-content:space-between; align-items: center; gap: 8px;">
        <button id="btn-expandir-detalles" class="btn-expandir" title="Ver detalles ampliados">
          <img style="margin:0;" class="img-expandir" src="images/expandir_gris.png" alt="" width="24px" height="24px">
        </button>
        <button style="display:none;" id="btn-compartir" class="btn-expandir" title="Compartir enlace del proyecto">
          <img style="margin:0;" class="img-expandir" src="images/compartir_gris.png" alt="Compartir" width="24px" height="24px">
        </button>
      </div>

      <h5>${proyecto.nombre}</h5>

      <div id="contenedor-fases" class="contenedor-fases">
        ${proyecto.fases && proyecto.fases.length > 0 ? '<p>Cargando fases...</p>' : '<p>Este proyecto aún no tiene fases registradas.</p>'}
      </div>

      <hr>
      <p>Si deseas más información sobre el proyecto, puedes escribirnos a:
      <a href="mailto:vinculacion@intsuperior.edu.ec?subject=Quiero más información sobre el proyecto de ${proyecto.nombre}">
        vinculacion@intsuperior.edu.ec
      </a></p>
    `;

    // Actualizar estadísticas DEL PROYECTO (totales)
    totalEstudiantes.textContent = proyecto.total_estudiantes || 0;
    totalProfesores.textContent = proyecto.total_docentes || 0;
    totalBeneficiarios.textContent = proyecto.total_beneficiarios || 0;

    cargarMapa(proyecto);

    // Botón expandir detalles
    document.getElementById("btn-expandir-detalles").onclick = function () {
      mostrarModalDetalles(proyecto);
    };

    // Botón compartir
    document.getElementById("btn-compartir").onclick = function () {
      const url = `${window.location.origin}/proyecto.php?id=${proyecto.id_proyecto}`;
      navigator.clipboard
        .writeText(url)
        .then(() => mostrarToast("✅ Enlace copiado al portapapeles"));
    };

    // Cargar fases del proyecto (ya vienen en el objeto proyecto.fases)
    const contenedor = document.getElementById("contenedor-fases");
    const descripcionContenedor = document.getElementById("ctn-fase-descripcion");
    if (!proyecto.fases || proyecto.fases.length === 0) {
      contenedor.innerHTML = "<p>Este proyecto aún no tiene fases registradas.</p>";
      return;
    }

    const fases = proyecto.fases;

    let tabs = `<div class="tabs">`;
    let contenido = `<div class="tab-contenido">`;

    fases.forEach((fase, index) => {
      const label = `Fase ${index + 1}`;
      tabs += `<button class="tab-btn ${index === 0 ? "active" : ""}" data-index="${index}">${label}</button>`;

      contenido += `
        <div class="tab-panel" style="${index === 0 ? "" : "display:none"}">
          <div style="display:flex; justify-content:space-between"><h3 style="margin-bottom: 6px;">${fase.nombre}</h3> ${fase.documento_url ? `<a class="login-btn" style="max-height:20px;" target="_blank" href="${fase.documento_url}">ACTA ENTREGA</a>`:``} </div>
          ${fase.banner ? `<img src="uploads/fases/${fase.banner}" alt="imagen fase" style="width:100%; border-radius:6px; margin-top:10px;">` : ""}
          <div style="margin-bottom:8px;">
              <span class="lbl-estado" style="margin-right:10px;">${escapeHtml(fase.periodo_academico || "")}</span>
                <span class="lbl-estado" style="margin-right:10px;">${escapeHtml(fase.estado || "")}</span>
              </div>
          <div class="descripcion-fase">
    ${ fase.descripcion ? unescapeHtml(fase.descripcion) : "Sin descripción detallada" }
</div>
        </div>
      `;
    });
    tabs += `</div>`;
    contenido += `</div>`;

    contenedor.innerHTML = tabs + contenido;

    const botones = contenedor.querySelectorAll(".tab-btn");
    const panels = contenedor.querySelectorAll(".tab-panel");

    // Actualizar estadísticas cuando cambia la pestaña de fase
    botones.forEach((btn) => {
      btn.addEventListener("click", () => {
        const index = parseInt(btn.dataset.index);
        const faseSeleccionada = fases[index];

        // Cambiar visual de pestañas
        botones.forEach((b) => b.classList.remove("active"));
        btn.classList.add("active");

        panels.forEach((p) => (p.style.display = "none"));
        panels[index].style.display = "block";

        // ACTUALIZAR ESTADÍSTICAS GLOBALES CON LOS DATOS DE LA FASE
        totalEstudiantes.textContent = faseSeleccionada.estudiantes_fase || 0;
        totalProfesores.textContent = faseSeleccionada.docentes_fase || 0;
        totalBeneficiarios.textContent = faseSeleccionada.cantidad_beneficiados || 0;
      });
    });

    // Actualizar estadísticas con la primera fase por defecto
    if (fases.length > 0) {
      totalEstudiantes.textContent = fases[0].estudiantes_fase || 0;
      totalProfesores.textContent = fases[0].docentes_fase || 0;
      totalBeneficiarios.textContent = fases[0].cantidad_beneficiados || 0;
    }
  }

  // Mostrar modal con detalles ampliados
  function mostrarModalDetalles(proyecto) {
    const modal = document.getElementById("modal-detalles-proyecto");
    const contenido = document.getElementById("modal-detalles-contenido");
    const fases = proyecto.fases || [];

    if (!fases || fases.length === 0) {
      contenido.innerHTML = `
        <h2>${escapeHtml(proyecto.nombre)}</h2>
        ${proyecto.banner ? `<img src="uploads/proyectos/${proyecto.banner}" alt="${escapeHtml(proyecto.nombre)}" class="img-fluid mb-3" style="width: 100%;">` : ""}
        <div style="display:flex;gap:8px;align-items:center;margin-bottom:6px;">
          <span class="lbl-estado">${escapeHtml(proyecto.estado || "")}</span>
        </div>
        <div>${proyecto.descripcion_corta || ""}</div>
        <hr>
        <p><strong>Estudiantes:</strong> ${proyecto.total_estudiantes || 0}</p>
        <p><strong>Profesores:</strong> ${proyecto.total_docentes || 0}</p>
        <p><strong>Beneficiarios:</strong> ${proyecto.total_beneficiarios || 0}</p>
        <hr>
        <p>Si deseas más información sobre el proyecto, puedes escribirnos a:
          <a href="mailto:vinculacion@intsuperior.edu.ec">vinculacion@intsuperior.edu.ec</a>
        </p>
      `;
      modal.style.display = "flex";
      return;
    }

    let modalTabs = `<div class="modal-phases-tabs">`;
    let modalPanels = `<div class="modal-phases-content">`;

    fases.forEach((fase, idx) => {
      const label =  `Fase ${idx + 1}`;
      modalTabs += `<button class="modal-tab-btn ${idx === 0 ? "active" : ""}" data-index="${idx}">${label}</button>`;

      modalPanels += `
        <div class="modal-tab-panel" style="${idx === 0 ? "" : "display:none"}">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
            <div style="flex:1;">
              <br><div style="display:flex; justify-content:space-between"><h3 style="margin-bottom: 6px;">${fase.nombre}</h3> ${fase.documento_url ? `<a class="login-btn" style="max-height:20px;" target="_blank" href="${fase.documento_url}">ACTA ENTREGA</a>`:``} </div><br>
              <div style="margin-bottom:8px;">
              <span class="lbl-estado" style="margin-right:10px;">${escapeHtml(fase.periodo_academico || "")}</span>
                <span class="lbl-estado" style="margin-right:10px;">${escapeHtml(fase.estado || "")}</span>
              </div>
              ${fase.banner ? `<img src="uploads/fases/${fase.banner}" alt="imagen fase" style="width:100%; border-radius:6px; margin-top:6px;">` : ""}
              <p class="descripcion-fase" style="margin-top:12px;">${ fase.descripcion ? unescapeHtml(fase.descripcion) : "Sin descripción detallada"}</p>
            </div>

            <div class="stats-grid">
  <div class="stat-card" style="margin-bottom:10px;">
    <h4>Estudiantes</h4>
    <p id="total-estudiantes">${fase.estudiantes_fase || 0}</p>
  </div>

  <div class="stat-card" style="margin-bottom:10px;">
    <h4>Profesores</h4>
    <p id="total-profesores">${fase.docentes_fase || 0}</p>
  </div>

  <div class="stat-card">
    <h4>Beneficiarios</h4>
    <p id="total-beneficiarios">${fase.cantidad_beneficiados || 0}</p>
  </div>
</div>
          </div>
        </div>
      `;
    });

    modalTabs += `</div>`;
    modalPanels += `</div>`;

    contenido.innerHTML = `
      <div style="display:flex;justify-content:space-between;align-items:center;">
        <h2 style="margin:0;">${escapeHtml(proyecto.nombre)}</h2>
      </div>
      <div style="margin-top:12px;">${proyecto.banner ? `<img src="uploads/proyectos/${proyecto.banner}" alt="${escapeHtml(proyecto.nombre)}" style="width:100%; border-radius:6px; margin-bottom:12px;">` : ""}</div>
      ${modalTabs}
      ${modalPanels}
      <hr>
      <p style="margin-top:10px;">Para más información: <a href="mailto:vinculacion@intsuperior.edu.ec">vinculacion@intsuperior.edu.ec</a></p>
    `;

    modal.style.display = "flex";

    const modalTabBtns = contenido.querySelectorAll(".modal-tab-btn");
    const modalPanelsNodes = contenido.querySelectorAll(".modal-tab-panel");

    function activarModalTab(index) {
      modalTabBtns.forEach((b) => b.classList.remove("active"));
      modalTabBtns[index].classList.add("active");

      modalPanelsNodes.forEach((p) => (p.style.display = "none"));
      modalPanelsNodes[index].style.display = "block";

      const faseSel = fases[index];
      if (faseSel) {
        totalEstudiantes.textContent = faseSel.estudiantes_fase || 0;
        totalProfesores.textContent = faseSel.docentes_fase || 0;
        totalBeneficiarios.textContent = faseSel.cantidad_beneficiados || 0;
      }
    }

    modalTabBtns.forEach((btn) => {
      btn.addEventListener("click", () => {
        const idx = parseInt(btn.dataset.index, 10);
        activarModalTab(idx);
      });
    });

    activarModalTab(0);

    const cerrarBtn = document.getElementById("modal-cerrar-btn");
    cerrarBtn.addEventListener("click", () => {
      totalEstudiantes.textContent = proyecto.total_estudiantes || 0;
      totalProfesores.textContent = proyecto.total_docentes || 0;
      totalBeneficiarios.textContent = proyecto.total_beneficiarios || 0;
      modal.style.display = "none";
    });

    modal.onclick = function (e) {
      if (e.target === modal) {
        totalEstudiantes.textContent = proyecto.total_estudiantes || 0;
        totalProfesores.textContent = proyecto.total_docentes || 0;
        totalBeneficiarios.textContent = proyecto.total_beneficiarios || 0;
        modal.style.display = "none";
      }
    };
  }

  const cerrarModalBtn = document.getElementById("cerrar-modal");
  if (cerrarModalBtn) {
    cerrarModalBtn.onclick = function () {
      const modal = document.getElementById("modal-detalles-proyecto");
      if (modal) modal.style.display = "none";
    };
  }

  function cargarMapaEcuador() {
    if (map) {
      map.remove();
    }

    map = L.map("map").setView([-1.831239, -78.183406], 6);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      maxZoom: 19,
    }).addTo(map);

    L.marker([-1.831239, -78.183406])
      .addTo(map)
      .bindPopup(`<b>Ecuador</b><br>Estadísticas globales.`)
      .openPopup();
  }

  function cargarMapa(proyecto) {
    if (map) {
      map.remove();
    }

    const lat = parseFloat(proyecto.entidad_latitud) || -1.831239;
    const lng = parseFloat(proyecto.entidad_longitud) || -78.183406;

    map = L.map("map").setView([lat, lng], 17);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      maxZoom: 19,
    }).addTo(map);

    L.marker([lat, lng])
      .addTo(map)
      .bindPopup(
        `<b>${escapeHtml(proyecto.nombre)}</b><br>${proyecto.total_beneficiarios || 0} beneficiarios.`
      )
      .openPopup();
  }

  function actualizarBarrasProgreso(valores, maximos) {
    document.getElementById("progress-estudiantes").max = maximos.estudiantes;
    document.getElementById("progress-estudiantes").value = valores.estudiantes;

    document.getElementById("progress-profesores").max = maximos.profesores;
    document.getElementById("progress-profesores").value = valores.profesores;

    document.getElementById("progress-beneficiarios").max = maximos.beneficiarios;
    document.getElementById("progress-beneficiarios").value = valores.beneficiarios;

    document.getElementById("progress-proyectos").max = maximos.proyectos;
    document.getElementById("progress-proyectos").value = valores.proyectos;
  }

  function escapeHtml(str) {
    if (str === null || str === undefined) return "";
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }
});

function unescapeHtml(html) {
  const textarea = document.createElement("textarea");
  textarea.innerHTML = html;
  return textarea.value;
}