@extends('layouts.admin')

@section('title', 'Reportes')
@section('heading', 'Expedientes Completos')
@section('subheading', 'Genera expedientes completos de vehículos e inventario con toda la información detallada.')

@section('content')
  <div class="header-page">
    <h2>📊 Generador de Expedientes Completos</h2>
    <p>Genera expedientes completos de vehículos e inventario con toda la información detallada en PDF.</p>
  </div>

  <!-- GENERADOR UNIFICADO -->
  <div class="panel">
    <h3>Configurar Expediente</h3>
    <form id="unifiedReportForm"
        data-vehicle-detail-pdf="{{ route('reports.vehicle.detail.pdf', ['vehicleId' => '__VEHICLE_ID__']) }}"
        data-vehicle-fleet-pdf="{{ route('reports.vehicle.fleet.pdf') }}"
        data-reports-pdf="{{ route('reports.pdf') }}">
      @csrf
      
      <div class="form-group">
        <label>Tipo de Expediente</label>
        <select name="type" id="reportType" style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px;" onchange="updateReportConfig()">
          <option value="">Seleccione un tipo de expediente...</option>
          
          <optgroup label="📋 EXPEDIENTES DE VEHÍCULOS">
            <option value="vehiculo_detalle">Expediente de Vehículo Específico</option>
            <option value="vehiculo_general">Expediente Completo de Flota</option>
          </optgroup>
          
          <optgroup label="📦 INVENTARIO Y PRODUCTOS">
            <option value="inventario">Reporte de Inventario General</option>
            <option value="productos">Reporte de Productos</option>
            <option value="movimientos">Reporte de Movimientos de Stock</option>
            <option value="categorias">Reporte de Categorías y Marcas</option>
          </optgroup>
        </select>
      </div>

      <!-- Filtros dinámicos según tipo de reporte -->
      <div id="dynamicFilters" style="margin-top: 16px;">
        <!-- Los filtros se cargarán dinámicamente -->
      </div>

      <div style="margin-top: 24px;">
        <button type="button" class="btn btn-primary btn-block" data-reports-generate>📥 Generar y Descargar PDF</button>
      </div>
    </form>
  </div>

  @php
      $reportsCatalog = [
          'vehicles' => $vehicles ?? [],
          'categories' => $categories ?? [],
          'brands' => $brands ?? [],
          'clients' => $clients ?? [],
      ];
  @endphp
  <script type="application/json" id="reportsCatalogData">{!! json_encode($reportsCatalog, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>

  <script>
    const reportsCatalog = JSON.parse(document.getElementById('reportsCatalogData').textContent);
    const vehicles = reportsCatalog.vehicles;
    const categories = reportsCatalog.categories;
    const brands = reportsCatalog.brands;
    const clients = reportsCatalog.clients;
    const reportForm = document.getElementById('unifiedReportForm');

    function updateReportConfig() {
      const reportType = document.getElementById('reportType').value;
      const filtersContainer = document.getElementById('dynamicFilters');
      
      if (!reportType) {
        filtersContainer.innerHTML = '';
        return;
      }

      let filtersHTML = '';

      // Filtros para expedientes de vehículos
      if (reportType === 'vehiculo_detalle') {
        filtersHTML += `
          <div class="form-group">
            <label>Seleccionar Cliente</label>
            <select name="client_id" id="clientSelect" style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 4px;" onchange="filterVehiclesByClient()">
              <option value="">Seleccione un cliente...</option>
              ${clients.map(c => `<option value="${c.id}">${c.name}</option>`).join('')}
            </select>
          </div>

          <div class="form-group" id="vehicleSelection" style="display: none;">
            <label>Seleccionar Vehículo</label>
            <select name="vehicle_id" id="vehicleSelect" style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 4px;">
              <option value="">Seleccione un vehículo...</option>
            </select>
          </div>

          <div class="form-group">
            <label>Filtros Adicionales</label>
            <div style="display: grid; grid-template-columns: 1fr; gap: 8px; margin-top: 8px;">
              <div>
                <label style="font-size: 0.85rem;">Estado:</label>
                <select name="status" style="width: 100%; padding: 6px; border: 1px solid #e2e8f0; border-radius: 4px;">
                  <option value="">Todos los estados</option>
                  <option value="activo">Activo</option>
                  <option value="en_taller">En Taller</option>
                  <option value="mantenimiento">En Mantenimiento</option>
                  <option value="inactivo">Inactivo</option>
                </select>
              </div>
            </div>
          </div>
        `;
      }

      if (reportType === 'vehiculo_general') {
        filtersHTML += `
          <div class="form-group">
            <label>Filtros de Flota</label>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 8px;">
              <div>
                <label style="font-size: 0.85rem;">Cliente:</label>
                <select name="client_id" style="width: 100%; padding: 6px; border: 1px solid #e2e8f0; border-radius: 4px;">
                  <option value="">Todos los clientes</option>
                  ${clients.map(c => `<option value="${c.id}">${c.name}</option>`).join('')}
                </select>
              </div>
              <div>
                <label style="font-size: 0.85rem;">Estado:</label>
                <select name="status" style="width: 100%; padding: 6px; border: 1px solid #e2e8f0; border-radius: 4px;">
                  <option value="">Todos los estados</option>
                  <option value="activo">Activo</option>
                  <option value="en_taller">En Taller</option>
                  <option value="mantenimiento">En Mantenimiento</option>
                  <option value="inactivo">Inactivo</option>
                </select>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label>Rango de Fechas</label>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 8px;">
              <div>
                <label style="font-size: 0.85rem;">Desde:</label>
                <input type="date" name="from" style="width: 100%; padding: 6px; border: 1px solid #e2e8f0; border-radius: 4px;">
              </div>
              <div>
                <label style="font-size: 0.85rem;">Hasta:</label>
                <input type="date" name="to" style="width: 100%; padding: 6px; border: 1px solid #e2e8f0; border-radius: 4px;">
              </div>
            </div>
          </div>
        `;
      }



      // Filtros para inventario y productos
      if (['inventario', 'productos', 'movimientos', 'categorias'].includes(reportType)) {
        filtersHTML += `
          <div class="form-group">
            <label>Filtros de Inventario</label>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 8px;">
              <div>
                <label style="font-size: 0.85rem;">Categoría:</label>
                <select name="category_id" style="width: 100%; padding: 6px; border: 1px solid #e2e8f0; border-radius: 4px;">
                  <option value="">Todas las categorías</option>
                  ${categories.map(c => `<option value="${c.id}">${c.name}</option>`).join('')}
                </select>
              </div>
              <div>
                <label style="font-size: 0.85rem;">Marca:</label>
                <select name="brand_id" style="width: 100%; padding: 6px; border: 1px solid #e2e8f0; border-radius: 4px;">
                  <option value="">Todas las marcas</option>
                  ${brands.map(b => `<option value="${b.id}">${b.name}</option>`).join('')}
                </select>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label>Estado de Stock</label>
            <div style="margin: 8px 0;">
              <label style="display: block; margin: 4px 0;">
                <input type="radio" name="stock_status" value="all" checked>
                Todo el inventario
              </label>
              <label style="display: block; margin: 4px 0;">
                <input type="radio" name="stock_status" value="low">
                Stock bajo (alerta)
              </label>
              <label style="display: block; margin: 4px 0;">
                <input type="radio" name="stock_status" value="out">
                Sin stock
              </label>
            </div>
          </div>
        `;
      }

      filtersContainer.innerHTML = filtersHTML;
    }

    function filterVehiclesByClient() {
      const clientId = document.getElementById('clientSelect').value;
      const vehicleSelect = document.getElementById('vehicleSelect');
      const vehicleSelection = document.getElementById('vehicleSelection');
      
      if (!clientId) {
        vehicleSelection.style.display = 'none';
        return;
      }

      const clientVehicles = vehicles.filter(v => v.client_id == clientId);
      
      vehicleSelect.innerHTML = '<option value="">Seleccione un vehículo...</option>' +
        clientVehicles.map(v => `<option value="${v.id}">${v.plate} - ${v.brand} ${v.model} (${v.year})</option>`).join('');
      
      vehicleSelection.style.display = 'block';
    }

    function generatePdf() {
      const form = document.getElementById('unifiedReportForm');
      const formData = new FormData(form);
      const reportType = formData.get('type');

      if (!reportType) {
        alert('Seleccione un tipo de expediente');
        return;
      }

      // Para expedientes individuales, redirigir a rutas específicas
      if (reportType === 'vehiculo_detalle') {
        const vehicleId = formData.get('vehicle_id');
        if (!vehicleId) {
          alert('Debe seleccionar un vehículo para el expediente individual');
          return;
        }
        window.location.href = reportForm.dataset.vehicleDetailPdf.replace('__VEHICLE_ID__', vehicleId);
        return;
      }

      if (reportType === 'vehiculo_general') {
        // Construir URL con filtros opcionales
        const params = new URLSearchParams();
        const clientId = formData.get('client_id');
        const status = formData.get('status');
        const from = formData.get('from');
        const to = formData.get('to');
        
        if (clientId) params.append('client_id', clientId);
        if (status) params.append('status', status);
        if (from) params.append('from', from);
        if (to) params.append('to', to);
        
        const queryString = params.toString();
        window.location.href = reportForm.dataset.vehicleFleetPdf + (queryString ? '?' + queryString : '');
        return;
      }

      // Para reportes de inventario, usar la ruta general
      const params = new URLSearchParams();
      for (const [key, value] of formData.entries()) {
        if (key !== '_token' && value !== '') {
          params.append(key, value);
        }
      }
      window.location.href = reportForm.dataset.reportsPdf + '?' + params.toString();
    }

    // Inicializar configuración al cargar
    document.addEventListener('DOMContentLoaded', function() {
      updateReportConfig();

      document.querySelector('[data-reports-generate]')?.addEventListener('click', generatePdf);
    });
  </script>
@endsection