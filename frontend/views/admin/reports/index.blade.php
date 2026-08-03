@extends('layouts.admin')

@section('title', 'Reportes')
@section('heading', 'Reportes del sistema')
@section('subheading', 'Genera e imprime reportes de mantenimientos, gastos, vehículos y pendientes.')

@section('content')
  <div class="header-page">
    <h2>📊 Reportes del sistema</h2>
    <p>Genera e imprime reportes de mantenimientos, gastos, vehículos y pendientes.</p>
  </div>

  <!-- TARJETAS DE REPORTES -->
  <div class="reports-grid">
    
    <div class="report-card" onclick="abrirReporte('mantenimientos')">
      <div class="icon-box">🛠️</div>
      <h3>Reporte de mantenimientos</h3>
      <p>Historial completo de servicios realizados a la flota de vehículos.</p>
    </div>

    <div class="report-card" onclick="abrirReporte('gastos')">
      <div class="icon-box">💰</div>
      <h3>Reporte de gastos</h3>
      <p>Resumen detallado de costos operativos y servicios completados.</p>
    </div>

    <div class="report-card" onclick="abrirReporte('vehiculos')">
      <div class="icon-box">🚗</div>
      <h3>Reporte de vehículos</h3>
      <p>Estado general de la flota de vehículos registrada en el sistema.</p>
    </div>

    <div class="report-card" onclick="abrirReporte('pendientes')">
      <div class="icon-box">⏳</div>
      <h3>Reporte de pendientes</h3>
      <p>Mantenimientos programados y órdenes de servicio aún abiertas.</p>
    </div>

  </div>

  <!-- MODAL DE PREVISUALIZACIÓN / IMPRESIÓN -->
  <div class="modal-overlay" id="modalReporte">
    <div class="modal-box" id="modalPrintArea">
      
      <div class="modal-header">
        <h3 id="reportTitle">Vista previa del Reporte</h3>
        <button onclick="cerrarModal()" style="border:none; background:none; font-size:1.2rem; cursor:pointer;">✕</button>
      </div>

      <div class="modal-body">
        <div id="reportContent">
          <!-- La tabla se inyecta dinámicamente -->
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
        <button class="btn btn-pdf" onclick="exportarPDF()">📄 Descargar PDF</button>
        <button class="btn btn-print" onclick="imprimirReporte()">🖨️ Imprimir</button>
      </div>

    </div>
  </div>

  <!-- Librería para exportar a PDF -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

  <style>
    .header-page { margin-bottom: 24px; }
    .header-page h2 { font-size: 1.5rem; font-weight: 700; color: #0f172a; }
    .header-page p { font-size: 0.875rem; color: #64748b; margin-top: 4px; }

    .reports-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
      max-width: 1000px;
    }

    .report-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 24px;
      cursor: pointer;
      transition: all 0.2s ease-in-out;
      display: flex;
      flex-direction: column;
      gap: 10px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }

    .report-card:hover {
      border-color: #0d9488;
      box-shadow: 0 10px 15px -3px rgba(13, 148, 136, 0.1);
      transform: translateY(-2px);
    }

    .report-card .icon-box {
      font-size: 1.75rem;
      background: #f1f5f9;
      width: 48px;
      height: 48px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 10px;
    }

    .report-card h3 { font-size: 1.05rem; color: #0f172a; font-weight: 600; }
    .report-card p { font-size: 0.85rem; color: #64748b; line-height: 1.4; }

    .modal-overlay {
      position: fixed; top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(15, 23, 42, 0.6);
      backdrop-filter: blur(4px);
      display: none; align-items: center; justify-content: center;
      z-index: 999;
    }
    .modal-overlay.active { display: flex; }

    .modal-box {
      background: #ffffff; border-radius: 12px;
      width: 100%; max-width: 850px;
      overflow: hidden;
      box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);
    }

    .modal-header {
      padding: 20px 24px; border-bottom: 1px solid #e2e8f0;
      display: flex; justify-content: space-between; align-items: center;
    }
    .modal-header h3 { font-size: 1.15rem; color: #0f172a; }

    .modal-body { padding: 24px; max-height: 60vh; overflow-y: auto; }

    .modal-footer {
      padding: 16px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0;
      display: flex; justify-content: flex-end; gap: 10px;
    }

    .btn { padding: 9px 16px; border-radius: 6px; font-weight: 600; font-size: 0.875rem; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 6px; }
    .btn-secondary { background: #ffffff; border: 1px solid #cbd5e1; color: #475569; }
    .btn-print { background: #0f172a; color: #ffffff; }
    .btn-pdf { background: #ef4444; color: #ffffff; }
    .btn:hover { opacity: 0.9; }

    table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem; }
    th { background: #f1f5f9; color: #475569; padding: 10px 12px; border-bottom: 1px solid #cbd5e1; }
    td { padding: 10px 12px; border-bottom: 1px solid #e2e8f0; }

    @media print {
      body * { visibility: hidden; }
      #modalPrintArea, #modalPrintArea * { visibility: visible; }
      #modalPrintArea { position: absolute; left: 0; top: 0; width: 100%; }
      .modal-footer, .modal-header button { display: none !important; }
    }
  </style>

  <script>
    const datosReportes = {
        'mantenimientos': {
            'titulo': 'Reporte General de Mantenimientos',
            'columnas': ['ID Orden', 'Vehículo', 'Servicio', 'Mecánico', 'Fecha'],
            'filas': @json($reportData['mantenimientos'])
        },
        'gastos': {
            'titulo': 'Reporte Resumen de Gastos Operativos',
            'columnas': ['Categoría', 'Detalle / Concepto', 'Vehículo', 'Costo ($)'],
            'filas': @json($reportData['gastos'])
        },
        'vehiculos': {
            'titulo': 'Reporte de Estado de Flota Vehicular',
            'columnas': ['Placa', 'Modelo / Marca', 'Año', 'Kilometraje', 'Estado'],
            'filas': @json($reportData['vehiculos'])
        },
        'pendientes': {
            'titulo': 'Reporte de Mantenimientos Pendientes y Abiertos',
            'columnas': ['N° Cita', 'Vehículo', 'Trabajo Requerido', 'Prioridad', 'Fecha Programada'],
            'filas': @json($reportData['pendientes'])
        }
    };

    let reporteActual = null;

    function abrirReporte(tipo) {
      reporteActual = datosReportes[tipo];
      document.getElementById('reportTitle').innerText = reporteActual.titulo;

      let html = `<table id="tablaReporte"><thead><tr>`;
      reporteActual.columnas.forEach(col => html += `<th>${col}</th>`);
      html += `</tr></thead><tbody>`;

      reporteActual.filas.forEach(fila => {
        html += `<tr>`;
        fila.forEach(celda => html += `<td>${celda}</td>`);
        html += `</tr>`;
      });
      html += `</tbody></table>`;

      document.getElementById('reportContent').innerHTML = html;
      document.getElementById('modalReporte').classList.add('active');
    }

    function cerrarModal() {
      document.getElementById('modalReporte').classList.remove('active');
    }

    function imprimirReporte() {
      window.print();
    }

    function exportarPDF() {
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF();

      doc.setFontSize(16);
      doc.text(reporteActual.titulo, 14, 20);

      doc.autoTable({
        head: [reporteActual.columnas],
        body: reporteActual.filas,
        startY: 28,
        theme: 'striped',
        headStyles: { fillColor: [13, 148, 136] }
      });

      doc.save(`${reporteActual.titulo}.pdf`);
    }
  </script>
@endsection
