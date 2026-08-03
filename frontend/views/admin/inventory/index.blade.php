@extends('layouts.admin')

@section('title', 'Productos e Inventario')
@section('heading', 'Productos e Inventario')
@section('subheading', 'Control centralizado de stock, familias de repuestos y órdenes de compra.')

@section('content')
<div class="module-card">
  <div class="module-header">
    <div class="module-title">
      <h2>📁 Productos e Inventario</h2>
      <p>Control centralizado de stock, familias de repuestos y órdenes de compra.</p>
    </div>
    <button class="btn btn-primary" onclick="abrirModal('nuevo_producto')">+ Nuevo Producto / Repuesto</button>
  </div>

  <!-- Pestañas -->
  <div class="tabs-bar">
    <button class="tab-btn active" onclick="switchTab('productos')">📦 Productos y Stock</button>
    <button class="tab-btn" onclick="switchTab('categorias')">🏷️ Categorías y Marcas</button>
    <button class="tab-btn" onclick="switchTab('proveedores')">🚚 Compras y Proveedores</button>
  </div>

  <!-- TAB 1: PRODUCTOS -->
  <div id="tab-productos" class="tab-content active">
    <table>
      <thead>
        <tr>
          <th>SKU / Código</th>
          <th>Nombre del Repuesto</th>
          <th>P. Venta</th>
          <th>Stock Actual</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($products as $product)
        <tr>
          <td><strong>{{ $product->sku }}</strong></td>
          <td>{{ $product->name }}</td>
          <td>${{ number_format($product->price, 2) }}</td>
          <td>{{ $product->stock }} unid.</td>
          <td>
            <button class="actions-btn" onclick="abrirModal('editar_producto', '{{ $product->name }}', '{{ $product->sku }}', {{ $product->price }})">Editar</button>
            <button class="actions-btn" style="color: #2563eb;" onclick="abrirModal('ajustar_stock', '{{ $product->name }}', {{ $product->stock }}, {{ $product->id }})">+ Stock</button>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <!-- TAB 2: CATEGORÍAS -->
  <div id="tab-categorias" class="tab-content">
    <div style="margin-bottom: 16px; text-align: right;">
      <button class="btn btn-primary" style="background:#475569;" onclick="abrirModal('nueva_categoria')">+ Nueva Categoría / Marca</button>
    </div>
    <table>
      <thead>
        <tr>
          <th>Categoría</th>
          <th>Marcas Vinculadas</th>
          <th>Total Productos</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($categories as $category)
        <tr>
          <td><strong>{{ $category->name }}</strong></td>
          <td>{{ $category->brands->pluck('name')->join(', ') ?: 'Sin marcas' }}</td>
          <td>{{ $category->products()->count() }} repuestos</td>
          <td><button class="actions-btn" onclick="abrirModal('nueva_categoria', '{{ $category->name }}')">Editar</button></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <!-- TAB 3: PROVEEDORES Y COMPRAS -->
  <div id="tab-proveedores" class="tab-content">
    <table>
      <thead>
        <tr>
          <th>N° Orden</th>
          <th>Proveedor</th>
          <th>Monto Total</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($purchases as $purchase)
        <tr>
          <td><strong>{{ $purchase->reference }}</strong></td>
          <td>{{ $purchase->supplier->name }}</td>
          <td>${{ number_format($purchase->total, 2) }}</td>
          <td>{{ ucfirst($purchase->status) }}</td>
          <td><button class="actions-btn" onclick="abrirModal('ver_detalle_compra', '{{ $purchase->reference }}')">Ver Detalle</button></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<!-- VENTANA MODAL ÚNICA REUTILIZABLE -->
<div class="modal-overlay" id="modalOverlay">
  <div class="modal-box">
    <div class="modal-header">
      <h3 id="modalTitle">Título del Modal</h3>
      <button onclick="cerrarModal()" style="border:none; background:none; font-size:1.2rem; cursor:pointer;">✕</button>
    </div>
    
    <form id="formInventarioModal" onsubmit="enviarFormulario(event)">
      <input type="hidden" name="accion" id="modalAccion" value="">
      <input type="hidden" name="product_id" id="modalProductId" value="">

      <div class="modal-body" id="modalBody">
        <!-- El contenido se inyecta dinámicamente con JS -->
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
        <button type="submit" class="btn btn-primary" id="modalSubmitBtn">Guardar</button>
      </div>
    </form>
  </div>
</div>

<style>
  .module-card { background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow: hidden; }
  .module-header { padding: 24px 30px 10px; display: flex; justify-content: space-between; align-items: center; }
  .module-title h2 { font-size: 1.25rem; font-weight: 700; color: #0f172a; }
  .module-title p { font-size: 0.875rem; color: #64748b; margin-top: 4px; }

  .tabs-bar { display: flex; gap: 4px; padding: 0 30px; border-bottom: 2px solid #f1f5f9; background: #ffffff; }
  .tab-btn { padding: 12px 20px; font-weight: 600; font-size: 0.875rem; border: none; background: transparent; color: #64748b; cursor: pointer; border-bottom: 3px solid transparent; margin-bottom: -2px; }
  .tab-btn.active { color: #0d9488; border-bottom-color: #0d9488; }

  .tab-content { padding: 24px 30px; display: none; }
  .tab-content.active { display: block; }

  .actions-btn { background: transparent; border: none; color: #0d9488; font-weight: 600; cursor: pointer; margin-right: 8px; font-size: 0.825rem; }
  .actions-btn:hover { text-decoration: underline; }

  table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem; }
  th { background: #f1f5f9; color: #475569; padding: 12px 16px; border-bottom: 1px solid #e2e8f0; }
  td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; }

  .modal-overlay { position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); display: none; align-items: center; justify-content: center; z-index: 999; }
  .modal-overlay.active { display: flex; }
  .modal-box { background: #ffffff; border-radius: 12px; width: 100%; max-width: 650px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); }
  .modal-header { padding: 18px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
  .modal-header h3 { font-size: 1.1rem; color: #0f172a; }
  .modal-body { padding: 24px; max-height: 75vh; overflow-y: auto; }
  .modal-footer { padding: 16px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px; }

  .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }
  .form-group { display: flex; flex-direction: column; }
  .form-group.full { grid-column: span 2; }
  label { font-size: 0.75rem; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 4px; }
  .input-box { width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.875rem; outline: none; }
  .input-box:focus { border-color: #0d9488; }
</style>

<script>
  // Cambio de Pestañas
  function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));

    document.getElementById('tab-' + tabId).classList.add('active');
    event.currentTarget.classList.add('active');
  }

  // Modal Dinámico
  function abrirModal(tipo, dato1 = '', dato2 = '', dato3 = '') {
    const overlay = document.getElementById('modalOverlay');
    const title = document.getElementById('modalTitle');
    const body = document.getElementById('modalBody');
    const submitBtn = document.getElementById('modalSubmitBtn');
    const productIdInput = document.getElementById('modalProductId');

    document.getElementById('modalAccion').value = tipo;
    productIdInput.value = dato3 || '';
    overlay.classList.add('active');

    // ACCIÓN 1: NUEVO PRODUCTO
    if (tipo === 'nuevo_producto') {
      title.innerText = 'Registrar Nuevo Repuesto';
      submitBtn.innerText = 'Guardar Repuesto';
      submitBtn.style.display = 'inline-block';
      body.innerHTML = `
        <div class="form-grid">
          <div class="form-group"><label>SKU / Cod *</label><input type="text" name="sku" class="input-box" required placeholder="Ej: REP-1029"></div>
          <div class="form-group"><label>Nombre del Repuesto *</label><input type="text" name="name" class="input-box" required placeholder="Ej: Filtro de Aceite"></div>
          <div class="form-group"><label>Categoría</label>
            <select name="category_id" class="input-box">
              <option value="">Seleccionar categoría...</option>
              @foreach ($categories as $cat)
              <option value="{{ $cat->id }}">{{ $cat->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group"><label>Marca</label>
            <select name="brand_id" class="input-box">
              <option value="">Seleccionar marca...</option>
              @foreach ($brands as $brand)
              <option value="{{ $brand->id }}">{{ $brand->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group"><label>Precio Compra ($)</label><input type="number" step="0.01" name="purchase_price" class="input-box" placeholder="0.00"></div>
          <div class="form-group"><label>Precio Venta ($) *</label><input type="number" step="0.01" name="sale_price" class="input-box" required placeholder="0.00"></div>
          <div class="form-group"><label>Stock Inicial *</label><input type="number" name="stock_quantity" class="input-box" value="1"></div>
          <div class="form-group"><label>Stock Mínimo</label><input type="number" name="min_stock" class="input-box" value="2"></div>
        </div>
      `;
    }

    // ACCIÓN 2: EDITAR PRODUCTO
    else if (tipo === 'editar_producto') {
      title.innerText = 'Editar Repuesto: ' + dato1;
      submitBtn.innerText = 'Actualizar Cambios';
      submitBtn.style.display = 'inline-block';
      body.innerHTML = `
        <div class="form-grid">
          <div class="form-group"><label>SKU / Cod</label><input type="text" name="sku" class="input-box" value="${dato2}"></div>
          <div class="form-group"><label>Nombre del Repuesto</label><input type="text" name="name" class="input-box" value="${dato1}"></div>
          <div class="form-group full"><label>Precio Venta ($)</label><input type="number" step="0.01" name="sale_price" class="input-box" value="${dato3}"></div>
        </div>
      `;
    }

    // ACCIÓN 3: AJUSTAR STOCK RÁPIDO
    else if (tipo === 'ajustar_stock') {
      title.innerText = 'Movimiento de Inventario';
      submitBtn.innerText = 'Confirmar Ajuste';
      submitBtn.style.display = 'inline-block';
      body.innerHTML = `
        <p style="margin-bottom: 12px; font-size: 0.875rem; color:#64748b;">Producto: <strong>${dato1}</strong> | Stock Actual: <strong>${dato2} unid.</strong></p>
        <div class="form-grid">
          <div class="form-group">
            <label>Tipo de Movimiento</label>
            <select name="tipo_movimiento" class="input-box">
              <option value="ingreso">+ Entrada de Stock (Compra / Devolución)</option>
              <option value="egreso">- Salida de Stock (Ajuste / Merma)</option>
            </select>
          </div>
          <div class="form-group">
            <label>Cantidad a Agregar/Restar</label>
            <input type="number" name="quantity" class="input-box" value="1" min="1">
          </div>
          <div class="form-group full">
            <label>Motivo del Ajuste</label>
            <input type="text" name="motivo" class="input-box" placeholder="Ej: Compra directa a proveedor según Factura #804">
          </div>
        </div>
        <input type="hidden" name="product_id" value="${dato3}">
      `;
    }

    // ACCIÓN 4: NUEVA CATEGORÍA O MARCA
    else if (tipo === 'nueva_categoria') {
      title.innerText = dato1 ? 'Editar Categoría' : 'Nueva Categoría / Marca';
      submitBtn.innerText = 'Guardar Categoría';
      submitBtn.style.display = 'inline-block';
      body.innerHTML = `
        <div class="form-grid">
          <div class="form-group full"><label>Nombre de Categoría *</label><input type="text" name="nombre_categoria" class="input-box" value="${dato1}" placeholder="Ej: Sistema de Encendido" required></div>
        </div>
      `;
    }

    // ACCIÓN 5: VER DETALLE DE COMPRA
    else if (tipo === 'ver_detalle_compra') {
      title.innerText = 'Detalle de Orden de Compra ' + dato1;
      submitBtn.style.display = 'none';
      body.innerHTML = `
        <p style="font-size:0.875rem; margin-bottom:10px;"><strong>Proveedor:</strong> Importadora R&M Cía. Ltda.</p>
        <p style="font-size:0.875rem; margin-bottom:16px;"><strong>Fecha:</strong> 28/07/2026</p>
        <table style="width:100%; font-size:0.825rem; border: 1px solid #e2e8f0;">
          <thead><tr style="background:#f8fafc;"><th>Ítem</th><th>Cant.</th><th>P. Unit</th><th>Subtotal</th></tr></thead>
          <tbody>
            <tr><td>Pastillas Freno Bosch</td><td>20</td><td>$22.00</td><td>$440.00</td></tr>
            <tr><td>Disco de Freno Brembo</td><td>10</td><td>$45.00</td><td>$450.00</td></tr>
          </tbody>
        </table>
        <h4 style="text-align:right; margin-top:12px; color:#0d9488;">Total: $1,450.00</h4>
      `;
    }
  }

  function cerrarModal() {
    document.getElementById('modalOverlay').classList.remove('active');
  }

  // ENVÍO DE DATOS HACIA EL SERVIDOR LARAVEL
  async function enviarFormulario(e) {
    e.preventDefault();

    const form = document.getElementById('formInventarioModal');
    const formData = new FormData(form);
    const accion = formData.get('accion');

    let url = '';
    let method = 'POST';

    switch (accion) {
      case 'nuevo_producto':
        url = '{{ route("products.store") }}';
        break;
      case 'editar_producto':
        url = '{{ route("products.update", ":id") }}'.replace(':id', formData.get('product_id'));
        method = 'PUT';
        break;
      case 'ajustar_stock':
        url = '{{ route("stock.store") }}';
        break;
      case 'nueva_categoria':
        url = '{{ route("categories.store") }}';
        break;
      default:
        alert('Acción no válida');
        return;
    }

    if (method === 'PUT') {
      formData.append('_method', 'PUT');
    }
    formData.append('_token', '{{ csrf_token() }}');

    try {
      const response = await fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      const res = await response.json();

      if (res.success || response.ok) {
        alert('✔ Operación completada correctamente');
        cerrarModal();
        location.reload();
      } else {
        alert('❌ ' + (res.message || 'Error al procesar la solicitud'));
      }
    } catch (err) {
      console.error(err);
      alert('Ocurrió un error al procesar la solicitud.');
    }
  }
</script>
@endsection
