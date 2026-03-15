(function polyfill() {
  const relList = document.createElement("link").relList;
  if (relList && relList.supports && relList.supports("modulepreload")) {
    return;
  }
  for (const link of document.querySelectorAll('link[rel="modulepreload"]')) {
    processPreload(link);
  }
  new MutationObserver((mutations) => {
    for (const mutation of mutations) {
      if (mutation.type !== "childList") {
        continue;
      }
      for (const node of mutation.addedNodes) {
        if (node.tagName === "LINK" && node.rel === "modulepreload")
          processPreload(node);
      }
    }
  }).observe(document, { childList: true, subtree: true });
  function getFetchOpts(link) {
    const fetchOpts = {};
    if (link.integrity) fetchOpts.integrity = link.integrity;
    if (link.referrerPolicy) fetchOpts.referrerPolicy = link.referrerPolicy;
    if (link.crossOrigin === "use-credentials")
      fetchOpts.credentials = "include";
    else if (link.crossOrigin === "anonymous") fetchOpts.credentials = "omit";
    else fetchOpts.credentials = "same-origin";
    return fetchOpts;
  }
  function processPreload(link) {
    if (link.ep)
      return;
    link.ep = true;
    const fetchOpts = getFetchOpts(link);
    fetch(link.href, fetchOpts);
  }
})();
const app = {
  db: null,
  isOnline: navigator.onLine,
  init() {
    this.bindEvents();
    this.updateOnlineStatus();
    this.initSQLiteMock();
  },
  bindEvents() {
    const menuBtn = document.getElementById("mobileMenuBtn");
    const menu = document.getElementById("mobileMenu");
    menuBtn.addEventListener("click", () => {
      menu.classList.toggle("active");
    });
    const navLinks = document.querySelectorAll(".mobile-nav-link");
    navLinks.forEach((link) => {
      link.addEventListener("click", (e) => {
        e.preventDefault();
        navLinks.forEach((l) => l.classList.remove("active"));
        e.currentTarget.classList.add("active");
        const targetViewId = e.currentTarget.getAttribute("data-target");
        document.querySelectorAll(".view-container").forEach((view) => {
          view.classList.remove("active");
        });
        document.getElementById(targetViewId).classList.add("active");
        menu.classList.remove("active");
        if (targetViewId === "view-equipos") {
          this.renderEquiposFromDB();
        }
      });
    });
    window.addEventListener("online", () => this.updateOnlineStatus());
    window.addEventListener("offline", () => this.updateOnlineStatus());
    window.simularDescarga = this.simularDescarga.bind(this);
  },
  updateOnlineStatus() {
    this.isOnline = navigator.onLine;
    const ind = document.getElementById("offlineIndicator");
    const dlabel = document.getElementById("conexionLabel");
    if (this.isOnline) {
      ind.classList.remove("disconnect");
      ind.classList.add("active");
      ind.innerHTML = '<i class="material-icons" style="font-size:14px;">wifi</i> SISTEMA CONECTADO. LISTO PARA SYNC.';
      if (dlabel) dlabel.innerHTML = "Servidor Maestro (127.0.0.1)";
      setTimeout(() => {
        ind.style.display = "none";
      }, 2500);
    } else {
      ind.style.display = "flex";
      ind.classList.remove("active");
      ind.classList.add("disconnect");
      ind.innerHTML = '<i class="material-icons" style="font-size:14px;">signal_wifi_off</i> MODO SIN CONEXIÓN (SQLite Local)';
      if (dlabel) dlabel.innerHTML = "Offline (Esperando Red)";
    }
  },
  initSQLiteMock() {
    document.getElementById("dbStatus").innerHTML = '<i class="material-icons" style="font-size:14px; vertical-align:middle;">check_circle</i> Conectado (Motor SQLite Activo)';
    const stored = localStorage.getItem("vidalsa_offline_equipos");
    const count = stored ? JSON.parse(stored).length : 0;
    document.getElementById("dbCount").textContent = count + " Equipos cacheados";
  },
  async simularDescarga() {
    const btn = document.getElementById("btnDownload");
    const originalText = btn.innerHTML;
    if (!document.getElementById("spinStyle")) {
      const style = document.createElement("style");
      style.id = "spinStyle";
      style.innerHTML = `@keyframes spinIcon { from { transform: rotate(0deg); } to { transform: rotate(360deg); } } .rotating { animation: spinIcon 1s linear infinite; }`;
      document.head.appendChild(style);
    }
    btn.innerHTML = '<i class="material-icons rotating">sync</i> Descargando al Teléfono...';
    btn.style.opacity = "0.8";
    try {
      const data = await new Promise((resolve) => {
        setTimeout(() => {
          resolve([
            { id: 1, codigo: "AMB-001", modelo: "TOYOTA LAND CRUISER", anio: 2019, estado: "OPERATIVO", tipo: "AMBULANCIA" },
            { id: 2, codigo: "RET-045", modelo: "CAT 320", anio: 2018, estado: "OPERATIVO", tipo: "RETROEXCAVADORA" },
            { id: 3, codigo: "VOL-012", modelo: "MACK GRANITE", anio: 2012, estado: "EN MANTENIMIENTO", tipo: "VOLQUETA" },
            { id: 4, codigo: "SOL-009", modelo: "LINCOLN VANTAGE", anio: 2015, estado: "INOPERATIVO", tipo: "SOLDADORA" },
            { id: 5, codigo: "CAM-110", modelo: "CHEVROLET NPR", anio: 2011, estado: "OPERATIVO", tipo: "CAMION" }
          ]);
        }, 1500);
      });
      localStorage.setItem("vidalsa_offline_equipos", JSON.stringify(data));
      this.initSQLiteMock();
      btn.innerHTML = '<i class="material-icons">check_circle</i> Descarga Completada';
      btn.style.background = "#10b981";
      setTimeout(() => {
        btn.innerHTML = originalText;
        btn.style.background = "linear-gradient(135deg, #0284c7, #0369a1)";
        btn.style.opacity = "1";
        document.querySelector('[data-target="view-equipos"]').click();
      }, 1800);
    } catch (e) {
      btn.innerHTML = '<i class="material-icons">error</i> Falla de Red';
      btn.style.background = "#ef4444";
    }
  },
  renderEquiposFromDB() {
    const container = document.getElementById("equiposOfflineContainer");
    const stored = localStorage.getItem("vidalsa_offline_equipos");
    if (!stored) return;
    const equipos = JSON.parse(stored);
    if (equipos.length === 0) return;
    let html = "";
    equipos.forEach((item) => {
      const isOp = item.estado === "OPERATIVO";
      const badgeClass = isOp ? "badge-operativo" : item.estado === "INOPERATIVO" ? "badge-inoperativo" : "badge-status";
      const statusLabel = isOp ? "OPERATIVO" : item.estado;
      html += `
                <div class="mobile-card">
                    <div class="mobile-card-title">
                        ${item.codigo}
                        <span class="badge-status ${badgeClass}">${statusLabel}</span>
                    </div>
                    <div class="mobile-card-subtitle" style="display: flex; align-items: center; gap: 4px; color: #0284c7; font-weight: bold;">
                        <i class="material-icons" style="font-size:16px;">airport_shuttle</i> ${item.tipo}
                    </div>
                    <div class="mobile-card-subtitle" style="margin-top: 5px;">
                        <strong>Modelo:</strong> ${item.modelo} <br>
                        <strong>Año Fabricación:</strong> ${item.anio}
                    </div>
                    
                    <div style="margin-top: 10px; border-top: 1px dashed #e2e8f0; padding-top: 10px; display: flex; justify-content: space-between;">
                        <button onclick="alert('Guardado en SQLite Local. Se sincronizará en cuanto exista conexión a Internet.')" style="background: white; border: 1px solid #cbd5e1; color: #475569; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: bold;"><i class="material-icons" style="font-size:12px; vertical-align:middle;">event_note</i> VER INFO</button>
                        
                        <button onclick="alert('Se ha encolado el evento Reportar Falla. Se enviará en cuanto se sincronice la BBDD')" style="background: #ef4444; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: bold;"><i class="material-icons" style="font-size:12px; vertical-align:middle;">warning</i> REPORTAR FALLA</button>
                    </div>
                </div>
            `;
    });
    container.innerHTML = html;
  }
};
document.addEventListener("DOMContentLoaded", () => app.init());
